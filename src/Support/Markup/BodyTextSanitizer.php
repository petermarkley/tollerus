<?php

namespace PeterMarkley\Tollerus\Support\Markup;

use Masterminds\HTML5;

use PeterMarkley\Tollerus\Enums\GlobalIdKind;
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;

/**
 * Sanitize an HTML input field by only allowing tags/attributes
 * from an approved list of known safe ones.
 */
class BodyTextSanitizer
{
    public const array SAFE_TAG_LIST = [
        'div',    'p',
        'ol',     'ul',
        'li',     'blockquote',
        'span',   'b',
        'strong', 'i',
        'em',     'a',
        'sup',    'sub',
    ];
    public const array SAFE_ATTR_LIST = [
        'href',           'target',
        'rel',            'class',
        'data-tollerus',  'data-id',
        'data-lang',      'data-neography-id',
        'data-neography',
    ];

    public function sanitize(string $html): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTMLFragment($html);

        foreach (iterator_to_array($dom->childNodes) as $node) {
            $this->handleNode($dom, $node);
        }

        return $html5->saveHTML($dom);
    }

    private function handleNode(\DOMDocumentFragment $dom, \DOMNode $node): void
    {
        switch ($node->nodeType) {
            case XML_TEXT_NODE:
            case XML_ENTITY_NODE:
            case XML_COMMENT_NODE:
                // Leave text, entities, and comments alone
                return;
            break;
            case XML_ELEMENT_NODE:
                // Unrecognized tags should be converted to text nodes
                if (!in_array(strtolower($node->tagName), self::SAFE_TAG_LIST)) {
                    $text = $dom->ownerDocument->createTextNode($node->textContent);
                    $node->parentNode->replaceChild($text, $node);
                    return;
                }
                foreach (iterator_to_array($node->attributes) as $attr) {
                    $this->handleNode($dom, $attr);
                }
                foreach (iterator_to_array($node->childNodes) as $child) {
                    $this->handleNode($dom, $child);
                }
            break;
            case XML_ATTRIBUTE_NODE:
                // The tag is recognized, but unknown attributes should be stripped
                if (!in_array(strtolower($node->name), self::SAFE_ATTR_LIST)) {
                    $node->ownerElement->removeAttributeNode($node);
                }
            break;
            default:
                // Whatever this is, it should not be here
                $node->parent->removeChild($node);
            break;
        }
    }
}