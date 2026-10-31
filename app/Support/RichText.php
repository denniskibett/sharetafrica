<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * Keeps only safe formatting from the rich-text editor: headings, paragraphs, lists, quotes, code,
 * bold/italic/underline, links (http/https/mailto, opened safely) and images from our own storage.
 */
class RichText
{
    protected const TAGS = ['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'a', 'img', 'hr', 'figure', 'figcaption'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->getElementById('root') ?? $doc->documentElement;

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    protected static function walk(DOMElement $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType === XML_COMMENT_NODE) {
                    $node->removeChild($child);
                }
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg'], true)) {
                $node->removeChild($child);
                continue;
            }

            self::walk($child);

            if (! in_array($tag, self::TAGS, true)) {
                // Unwrap unknown tags (div, span, font…) but keep their text
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            $keep = [];
            if ($tag === 'a' && preg_match('#^(https?://|mailto:|/)#i', (string) $child->getAttribute('href'))) {
                $keep = ['href' => $child->getAttribute('href'), 'rel' => 'noopener', 'target' => str_starts_with($child->getAttribute('href'), '/') ? null : '_blank'];
            }
            if ($tag === 'img') {
                $src = (string) $child->getAttribute('src');
                if (! str_starts_with($src, asset('storage/')) && ! str_starts_with($src, '/storage/')) {
                    $node->removeChild($child);
                    continue;
                }
                $keep = ['src' => $src, 'alt' => $child->getAttribute('alt'), 'loading' => 'lazy'];
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $child->removeAttribute($attr->name);
            }
            foreach (array_filter($keep, fn ($v) => $v !== null) as $name => $value) {
                $child->setAttribute($name, $value);
            }
        }
    }
}
