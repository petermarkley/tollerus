<?php

namespace PeterMarkley\Tollerus\Support\Markup;

use Masterminds\HTML5;

/**
 * Tiptap's StarterKit extension doesn't support using `<div>` elements
 * to create sections around paragraphs, but `<div>` sections is what
 * Tollerus expects. So this class is to translate back and forth between
 * one structure for storage vs. another for editing in Tiptap.
 *
 * Example of storage structure:
 *
 *   <div>
 *     <p>Lorem ipsum.</p>
 *     <p>Dolor sit amet.</p>
 *   </div>
 *   <div>
 *     <p>Consectetur adipiscing elit.</p>
 *   </div>
 *
 * The same content, shown in the editor's structure:
 *
 *   <p>Lorem ipsum.</p>
 *   <p>Dolor sit amet.</p>
 *   <p></p>
 *   <p>Consectetur adipiscing elit.</p>
 *
 * That empty `<p>` should equate to a `</div><div>` boundary.
 *
 * We also support an 'inline' mode where `<p>` tags are unwrapped and
 * removed entirely.
 */
class BodyTextNormalizer
{
    /**
     * Normalize body text for saving/storage.
     */
    public function normalizeForSave(string $html): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTMLFragment($html);

        $divs = collect(iterator_to_array($dom->childNodes))
            // Skip over any text that's outside a `<div>` (should be only whitespace)
            ->filter(fn ($n) => $n->nodeType === XML_ELEMENT_NODE)
            ->values()
            // Chunk based on empty `<p>` tags
            ->chunkWhile(fn ($node) => $node->nodeName != 'p' || !empty(trim($node->textContent)))
            // Create a `<div>` tag for each chunk
            ->map(function ($chunk) use ($dom) {
                $div = $dom->ownerDocument->createElement('div');
                $dom->appendChild($div);
                foreach ($chunk as $node) {
                    if ($node->nodeName == 'p' && empty(trim($node->textContent))) {
                        $dom->removeChild($node);
                    } else {
                        $div->appendChild($dom->removeChild($node));
                    }
                }
                return $div;
            });

        return $html5->saveHTML($dom);
    }

    /**
     * Normalize body text for editing in a WYSIWYG
     */
    public function normalizeForWysiwyg(string $html): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTMLFragment($html);

        $nodes = collect(iterator_to_array($dom->childNodes))
            // Skip over any text that's outside a `<div>` (should be only whitespace)
            ->filter(fn ($n) => $n->nodeType === XML_ELEMENT_NODE)
            ->values()
            // Convert each `<div>` into an array of its members
            ->map(function ($node) {
                if ($node->nodeName == 'div') {
                    return collect(iterator_to_array($node->childNodes))
                        ->filter(fn ($n) => $n->nodeType === XML_ELEMENT_NODE)
                        ->values()->all();
                } else {
                    return $node;
                }
            // Flatten arrays with an empty `<p>` between each pair
            })->flatMap(function ($n, $i) use ($dom) {
                $p = $dom->ownerDocument->createElement('p');
                return ($i==0 ? $n : collect([$p])->concat($n));
            });

        return $nodes->map(fn ($node) => $html5->saveHTML($node))
            ->implode("\n");
    }

    /**
     * Normalize as an inline text field (entry etymology, sense/subsense body)
     */
    public function normalizeInlineForSave(string $html): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTMLFragment($html);
        $xp  = new \DOMXPath($dom->ownerDocument);
        $xp->registerNamespace('h', 'http://www.w3.org/1999/xhtml');

        $tags = iterator_to_array($xp->query('.//h:div|.//h:p', $dom));
        foreach ($tags as $tag) {
            $parent = $tag->parentNode;
            foreach (iterator_to_array($tag->childNodes) as $child) {
                $parent->insertBefore($child, $tag);
            }
            $parent->removeChild($tag);
        }

        return $html5->saveHTML($dom);
    }
    public function normalizeInlineForWysiwyg(string $html): string
    {
        return '<p>'.$html.'</p>';
    }
}