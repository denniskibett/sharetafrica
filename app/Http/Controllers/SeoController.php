<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Support\Carbon;

/** sitemap.xml for search engines and llms.txt for AI assistants, both built from App\Support\Seo. */
class SeoController extends Controller
{
    public function sitemap()
    {
        $urls = collect(Seo::PAGES)->map(function ($page, $route) {
            [$view, , $priority] = $page;
            $file = resource_path("views/$view.blade.php");

            return [
                'loc' => route($route),
                'lastmod' => Carbon::createFromTimestamp(is_file($file) ? filemtime($file) : time())->toAtomString(),
                'priority' => $priority,
            ];
        });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url><loc>{$u['loc']}</loc><lastmod>{$u['lastmod']}</lastmod><priority>{$u['priority']}</priority></url>\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8'])->setPublic()->setMaxAge(3600);
    }

    /** https://llmstxt.org: a plain summary AI assistants can read to describe and cite Sharet correctly. */
    public function llms()
    {
        $lines = [
            '# Sharet Africa',
            '',
            '> Sharet makes payments across East Africa cheaper and simpler: one wallet and one ledger that route across M-PESA, MTN, Airtel, Vodacom and banks. Internal transfers are free, and the person being paid does not need a Sharet account. Headquartered in Nairobi, Kenya; founded 2026; launch markets Kenya, Uganda and Tanzania.',
            '',
            '## Products',
            '',
        ];
        foreach (Seo::PAGES as $route => [, $summary]) {
            if (! in_array($route, ['company', 'contact'])) {
                $lines[] = '- ['.ucfirst($route === 'home' ? 'Overview' : $route).']('.route($route).'): '.$summary;
            }
        }
        array_push($lines, '', '## Company', '',
            '- [About Sharet]('.route('company').'): '.Seo::PAGES['company'][1],
            '- [Contact]('.route('contact').'): '.Seo::PAGES['contact'][1].' General: hello@sharet.africa. Support: support@sharet.africa. Press: press@sharet.africa.',
            '', '## Companies run by Sharet', '');
        foreach (Seo::COMPANIES as [$name, $url, $about]) {
            $lines[] = "- [$name]($url): $about";
        }
        array_push($lines, '', '## Optional', '', '- [Sitemap]('.route('sitemap').')', '');

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8'])->setPublic()->setMaxAge(3600);
    }
}
