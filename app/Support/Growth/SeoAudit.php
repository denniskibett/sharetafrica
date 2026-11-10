<?php

namespace App\Support\Growth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * SEO audit of one site (php artisan growth:seo-audit): home page, /robots.txt, /sitemap.xml (URL count) and a sample
 * of sitemap URLs. Each page records status, response time, title, description length, canonical, og/twitter tags,
 * JSON-LD types, h1 count and noindex, plus red / amber / green findings. Replaces the site's rows in growth_seo_checks.
 */
class SeoAudit
{
    public const UA = 'SharetGrowthAudit/1.0 (+https://sharet.africa)';

    public function run(string $site, ?int $sample = null): array
    {
        $base = rtrim((string) (Sites::all()[$site]['url'] ?? ''), '/');
        $sample ??= (int) config('growth.audit_sample', 5);
        $now = now();
        $rows = [];

        $rows[] = $this->page($site, 'home', $base.'/');

        // robots.txt
        [$status, $ms, $body] = $this->fetch($base.'/robots.txt');
        $f = [];
        if ($status !== 200) {
            $f[] = ['amber', "robots.txt answers {$this->code($status)}: add one with a Sitemap: line."];
        } else {
            if (preg_match('/^\s*Disallow:\s*\/\s*$/mi', $body) && preg_match('/^\s*User-agent:\s*\*\s*$/mi', $body)) {
                $f[] = ['red', 'robots.txt blocks the whole site (Disallow: /). Search engines will not crawl it.'];
            }
            $f[] = preg_match('/^\s*Sitemap:\s*\S+/mi', $body) ? ['green', 'robots.txt lists the sitemap.'] : ['amber', 'robots.txt has no Sitemap: line.'];
        }
        $rows[] = $this->row($site, 'robots', $base.'/robots.txt', $status, $ms, findings: $f);

        // sitemap.xml (a sitemap index: follow the first child)
        [$status, $ms, $body] = $this->fetch($base.'/sitemap.xml');
        $urls = [];
        $f = [];
        if ($status === 200) {
            $urls = $this->locs($body);
            if (str_contains($body, '<sitemapindex') && $urls) {
                [, , $child] = $this->fetch($urls[0]);
                $urls = $this->locs($child);
            }
            $f[] = $urls ? ['green', count($urls).' URLs in the sitemap.'] : ['red', 'The sitemap has no URLs.'];
            $foreign = collect($urls)->reject(fn ($u) => parse_url($u, PHP_URL_HOST) === parse_url($base, PHP_URL_HOST))->count();
            if ($foreign) {
                $f[] = ['amber', "$foreign sitemap URLs point at another host."];
            }
        } else {
            $f[] = ['red', "sitemap.xml answers {$this->code($status)}. Add a sitemap and submit it in Search Console."];
        }
        $rows[] = $this->row($site, 'sitemap', $base.'/sitemap.xml', $status, $ms, sitemapUrls: count($urls), findings: $f);

        // A sample of sitemap pages (not the home page again)
        $home = rtrim($base, '/');
        foreach (collect($urls)->reject(fn ($u) => rtrim($u, '/') === $home)->take($sample) as $url) {
            $rows[] = $this->page($site, 'page', $url);
        }

        DB::transaction(function () use ($site, $rows, $now) {
            DB::table('growth_seo_checks')->where('site', $site)->delete();
            DB::table('growth_seo_checks')->insert(array_map(fn ($r) => $r + ['checked_at' => $now], $rows));
        });

        return $rows;
    }

    /** One HTML page: parse the head and score it. */
    protected function page(string $site, string $kind, string $url): array
    {
        [$status, $ms, $html, $headers] = $this->fetch($url);
        if ($status !== 200) {
            return $this->row($site, $kind, $url, $status, $ms, findings: [['red', "Answers {$this->code($status)} instead of 200."]]);
        }

        $title = $this->match('/<title[^>]*>(.*?)<\/title>/is', $html);
        $desc = $this->meta($html, 'name', 'description');
        $canonical = $this->match('/<link[^>]+rel=["\']canonical["\'][^>]*href=["\']([^"\']+)["\']/i', $html)
            ?? $this->match('/<link[^>]+href=["\']([^"\']+)["\'][^>]*rel=["\']canonical["\']/i', $html);
        preg_match_all('/<meta[^>]+(?:property|name)=["\']((?:og|twitter):[a-z:_]+)["\']/i', $html, $og);
        $og = array_values(array_unique(array_map('strtolower', $og[1])));
        preg_match_all('/<script[^>]+application\/ld\+json[^>]*>(.*?)<\/script>/is', $html, $ld);
        $types = [];
        foreach ($ld[1] as $json) {
            $this->types(json_decode(html_entity_decode(trim($json)), true), $types);
        }
        $types = array_values(array_unique($types));
        $h1 = preg_match_all('/<h1[\s>]/i', $html);
        $robots = strtolower((string) $this->meta($html, 'name', 'robots').' '.implode(' ', (array) ($headers['x-robots-tag'] ?? [])));
        $noindex = str_contains($robots, 'noindex');
        $lang = $this->match('/<html[^>]+lang=["\']([^"\']+)["\']/i', $html);

        $f = [];
        $f[] = $ms > 2000 ? ['red', "Slow: {$ms} ms to answer."] : ($ms > 800 ? ['amber', "{$ms} ms to answer (aim for under 800)."] : ['green', "Fast: {$ms} ms."]);
        $tl = mb_strlen((string) $title);
        $f[] = ! $title ? ['red', 'No <title>.'] : ($tl < 30 || $tl > 65 ? ['amber', "Title is $tl characters (aim for 30–65)."] : ['green', 'Title length is good.']);
        $dl = $desc === null ? null : mb_strlen($desc);
        $f[] = $dl === null ? ['red', 'No meta description.'] : ($dl < 70 || $dl > 160 ? ['amber', "Description is $dl characters (aim for 70–160)."] : ['green', 'Description length is good.']);
        if (! $canonical) {
            $f[] = ['amber', 'No canonical link.'];
        } elseif (! str_starts_with($canonical, 'https://') || parse_url($canonical, PHP_URL_HOST) !== parse_url($url, PHP_URL_HOST)) {
            $f[] = ['amber', "Canonical points elsewhere: $canonical"];
        } else {
            $f[] = ['green', 'Canonical set.'];
        }
        $f[] = in_array('og:title', $og, true) && in_array('og:image', $og, true) ? ['green', 'Open Graph title and image set.'] : ['amber', 'Missing og:title or og:image (link previews on WhatsApp / Facebook look bare).'];
        $f[] = $types ? ['green', 'Structured data: '.implode(', ', array_slice($types, 0, 6)).'.'] : ['amber', 'No JSON-LD structured data.'];
        $f[] = $h1 === 1 ? ['green', 'One h1.'] : ($h1 === 0 ? ['red', 'No h1 heading.'] : ['amber', "$h1 h1 headings (use one)."]);
        if ($noindex) {
            $f[] = ['red', 'noindex: search engines are told not to list this page.'];
        }
        if (! $lang) {
            $f[] = ['amber', 'No lang on <html>.'];
        }

        return $this->row($site, $kind, $url, $status, $ms, title: $title, descLength: $dl, canonical: $canonical, og: $og, types: $types, h1: $h1, noindex: $noindex, findings: $f);
    }

    /** @return array{0:?int,1:?int,2:string,3:array} status, ms, body, lower-cased headers */
    protected function fetch(string $url): array
    {
        $start = microtime(true);
        try {
            $r = Http::withHeaders(['User-Agent' => self::UA, 'Accept' => 'text/html,application/xml;q=0.9,*/*;q=0.8'])
                ->timeout((int) config('growth.audit_timeout', 15))->withOptions(['allow_redirects' => ['max' => 3]])->get($url);

            return [$r->status(), (int) round((microtime(true) - $start) * 1000), substr((string) $r->body(), 0, 3_000_000), array_change_key_case($r->headers(), CASE_LOWER)];
        } catch (\Throwable) {
            return [null, null, '', []];
        }
    }

    protected function row(string $site, string $kind, string $url, ?int $status, ?int $ms, ?string $title = null, ?int $descLength = null, ?string $canonical = null,
        array $og = [], array $types = [], ?int $h1 = null, bool $noindex = false, ?int $sitemapUrls = null, array $findings = []): array
    {
        return [
            'site' => $site, 'kind' => $kind, 'url' => mb_substr($url, 0, 500), 'status' => $status, 'response_ms' => $ms,
            'title' => $title !== null ? mb_substr($title, 0, 255) : null, 'description_length' => $descLength,
            'canonical' => $canonical !== null ? mb_substr($canonical, 0, 500) : null,
            'og_tags' => $og ? mb_substr(implode(',', $og), 0, 255) : null, 'jsonld_types' => $types ? mb_substr(implode(',', $types), 0, 255) : null,
            'h1_count' => $h1, 'noindex' => $noindex ? 1 : 0, 'sitemap_urls' => $sitemapUrls,
            'findings' => json_encode(array_map(fn ($f) => ['level' => $f[0], 'text' => $f[1]], $findings)),
        ];
    }

    private function code(?int $status): string
    {
        return $status === null ? 'nothing (timeout or no connection)' : (string) $status;
    }

    private function locs(string $xml): array
    {
        preg_match_all('/<loc>\s*([^<\s]+)\s*<\/loc>/i', $xml, $m);

        return array_map(fn ($u) => html_entity_decode($u), $m[1]);
    }

    private function match(string $re, string $html): ?string
    {
        return preg_match($re, $html, $m) ? trim(html_entity_decode(preg_replace('/\s+/', ' ', $m[1]), ENT_QUOTES)) : null;
    }

    private function meta(string $html, string $attr, string $value): ?string
    {
        return $this->match('/<meta[^>]+'.$attr.'=["\']'.$value.'["\'][^>]*content=["\']([^"\']*)["\']/i', $html)
            ?? $this->match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]*'.$attr.'=["\']'.$value.'["\']/i', $html);
    }

    private function types(mixed $node, array &$types): void
    {
        if (! is_array($node)) {
            return;
        }
        if (isset($node['@type'])) {
            foreach ((array) $node['@type'] as $t) {
                is_string($t) && $types[] = $t;
            }
        }
        foreach ($node as $k => $v) {
            if (is_array($v)) {
                $this->types($v, $types);
            }
        }
    }
}
