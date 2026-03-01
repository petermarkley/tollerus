<?php

namespace PeterMarkley\Tollerus\Support\Markup;

use Masterminds\HTML5;

use PeterMarkley\Tollerus\Enums\GlobalIdKind;
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;

/**
 * This validates and normalizes body text for display.
 *
 * Expected element example formats:
 *   - `<span data-tollerus="smallcaps">`
 *   - `<a href="/tollerus?id=AAR3" data-tollerus="word" data-id="AAR3" data-lang="myconlang">` or `<span data-tollerus="word" data-id="AAR3" data-lang="myconlang">`
 *   - `<span data-tollerus="native" data-neography-id="1" data-neography="myneography" class="tollerus_custom_myneography">`
 *   - `<span data-tollerus="phonemic">`
 */
class BodyTextRenderer
{
    public function render(string $html): string
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTMLFragment($html);
        $xp  = new \DOMXPath($dom->ownerDocument);

        $tags = iterator_to_array($xp->query('*[@data-tollerus]|.//*[@data-tollerus]', $dom));
        foreach ($tags as $tag) {
            switch ($tag->getAttribute('data-tollerus')) {
                case 'smallcaps':
                    // Nothing to do, already styled correctly in `tollerus.css`
                break;
                case 'word':
                    // Check for required data attrs
                    if (!$tag->hasAttribute('data-id')) {
                        continue 2;
                    }
                    $id = $tag->getAttribute('data-id');
                    // Validate ID
                    $globalId = GlobalId::fromStr($id);
                    if (!($globalId instanceof GlobalId)) {
                        continue 2;
                    }
                    $obj = $globalId->resolve();
                    $visible = false;
                    if ($globalId->kind == GlobalIdKind::Glyph) {
                        $neography = $obj->neography;
                        $language = $neography->languagesWherePrimary->firstWhere('visible', true) ?? $neography->languages->firstWhere('visible', true);
                        if ($neography->visible && ($language instanceof Language)) {
                            $visible = true;
                            $url = route('tollerus.public.languages.show', ['language' => $language, 'hl' => $id], false);
                        }
                    } else {
                        $language = $obj->language;
                        if ($language->visible) {
                            $visible = true;
                            $url = route('tollerus.public.index', ['id' => $id], false);
                        }
                    }
                    if ($visible) {
                        // Language is visible; make sure this is an `<a>` tag with correct href
                        if ($tag->tagName == 'a') {
                            $newTag = $tag;
                        } else {
                            $newTag = $dom->ownerDocument->createElement('a');
                            foreach (iterator_to_array($tag->childNodes) as $child) {
                                $newTag->appendChild($tag->removeChild($child));
                            }
                            $tag->parentNode->replaceChild($newTag, $tag);
                            $newTag->setAttribute('data-tollerus', 'word');
                            $newTag->setAttribute('data-id', $id);
                        }
                        $newTag->setAttribute('href', $url);
                        $newTag->setAttribute('data-lang', $language->machine_name);
                    } else {
                        // Language is NOT visible; make sure this is a `<span>` tag with no href
                        if ($tag->tagName == 'span') {
                            $newTag = $tag;
                        } else {
                            $newTag = $dom->ownerDocument->createElement('span');
                            foreach (iterator_to_array($tag->childNodes) as $child) {
                                $newTag->appendChild($tag->removeChild($child));
                            }
                            $tag->parentNode->replaceChild($newTag, $tag);
                            $newTag->setAttribute('data-tollerus', 'word');
                            $newTag->setAttribute('data-id', $id);
                        }
                        if ($newTag->hasAttribute('href')) {
                            $newTag->removeAttribute('href');
                        }
                        $newTag->setAttribute('data-lang', $language->machine_name);
                    }
                break;
                case 'native':
                    // Check for required data attrs
                    if (!$tag->hasAttribute('data-neography-id')) {
                        continue 2;
                    }
                    // Validate neography
                    $neography = Neography::find($tag->getAttribute('data-neography-id'));
                    if (!($neography instanceof Neography)) {
                        continue 2;
                    }
                    $tag->setAttribute('data-neography', $neography->machine_name);
                    $className = 'tollerus_custom_' . $neography->machine_name;
                    $classList = explode(' ', $tag->getAttribute('class'));
                    if ($neography->visible) {
                        // Ensure presence of neography style class
                        if (!in_array($className, $classList)) {
                            $classList[] = $className;
                            $tag->setAttribute('class', implode(' ', $classList));
                        }
                    } else {
                        // Neography should be invisible; demote this to just a regular `<span>` or whatever
                        $tag->removeAttribute('data-tollerus');
                        $tag->removeAttribute('data-neography');
                        if (in_array($className, $classList)) {
                            $classList = array_filter($classList, fn ($c) => $c !== $className);
                            $tag->setAttribute('class', implode(' ', $classList));
                        }
                    }
                break;
                case 'phonemic':
                    // Nothing to do, already styled correctly in `tollerus.css`
                break;
            }
        }

        return $html5->saveHTML($dom);
    }
}