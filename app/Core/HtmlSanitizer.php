<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Allowlist HTML cleaner for the admin rich-text editor (blog/page body,
 * product description). These fields are staff-authored, not user-submitted
 * — the threat model here is an accidental paste of styled/scripted HTML
 * from elsewhere, not a hostile admin — so this is defense-in-depth for
 * the fact that these fields render as raw HTML on the storefront rather
 * than escaped text.
 */
final class HtmlSanitizer
{
    private const ALLOWED_TAGS = ['p', 'br', 'b', 'strong', 'i', 'em', 'u', 'ul', 'ol', 'li', 'h2', 'h3', 'blockquote', 'a'];

    /** Tags whose content (not just the wrapping tag) is dropped entirely. */
    private const STRIP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'form'];

    public static function clean(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $root = $doc->getElementsByTagName('div')->item(0);
        if ($root === null) {
            return '';
        }

        self::cleanNode($doc, $root);

        $result = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $doc->saveHTML($child);
        }

        return trim($result);
    }

    private static function cleanNode(\DOMDocument $doc, \DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMText) {
                continue;
            }

            if (!($child instanceof \DOMElement)) {
                // Comments, processing instructions, CDATA — no legitimate use here.
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::STRIP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // Not on the allowlist but not dangerous either (e.g. a
                // pasted <span>/<div>) — unwrap it, keeping its content.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes ?? []) as $attr) {
                if (!($tag === 'a' && $attr->name === 'href')) {
                    $child->removeAttribute($attr->name);
                }
            }

            if ($tag === 'a') {
                $href = $child->getAttribute('href');
                if ($href === '' || !preg_match('#^(https?://|/|mailto:)#i', $href)) {
                    $child->removeAttribute('href');
                }
                $child->setAttribute('rel', 'noopener noreferrer');
                $child->setAttribute('target', '_blank');
            }

            self::cleanNode($doc, $child);
        }
    }
}
