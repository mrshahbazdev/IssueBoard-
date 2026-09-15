<?php

namespace Modules\IssueBoard\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichText
{
    private const ALLOWED_TAGS = [
        'a',
        'blockquote',
        'br',
        'div',
        'em',
        'i',
        'li',
        'ol',
        'p',
        's',
        'strong',
        'u',
        'ul',
    ];

    private const SAFE_LINK_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function clean(?string $html): ?string
    {
        if (! filled($html)) {
            return null;
        }

        if ($html === strip_tags($html)) {
            $html = preg_replace('/\r\n|\r|\n/', '<br>', e($html));
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="issueboard-rich-text-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('issueboard-rich-text-root');

        if (! $root) {
            return null;
        }

        $clean = static::childrenToHtml($root);

        return trim(strip_tags($clean)) === '' ? null : trim($clean);
    }

    private static function childrenToHtml(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= static::nodeToHtml($child);
        }

        return $html;
    }

    private static function nodeToHtml(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return e($node->textContent);
        }

        if (! $node instanceof DOMElement) {
            return '';
        }

        $tag = mb_strtolower($node->tagName);

        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
            return '';
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            return static::childrenToHtml($node);
        }

        if ($tag === 'br') {
            return '<br>';
        }

        $attributes = '';

        if ($tag === 'a') {
            $href = trim($node->getAttribute('href'));

            if (static::isSafeHref($href)) {
                $attributes = ' href="'.e($href).'" target="_blank" rel="noopener noreferrer"';
            }
        }

        return '<'.$tag.$attributes.'>'.static::childrenToHtml($node).'</'.$tag.'>';
    }

    private static function isSafeHref(string $href): bool
    {
        if ($href === '') {
            return false;
        }

        if (str_starts_with($href, '#')) {
            return true;
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(mb_strtolower($scheme), self::SAFE_LINK_SCHEMES, true);
    }
}
