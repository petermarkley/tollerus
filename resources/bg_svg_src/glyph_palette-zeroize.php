<?php

$inFile = "glyph_palette-plain.svg";
$outFile = "glyph_palette.svg";

$svg = simplexml_load_file($inFile);
$svg->registerXPathNamespace('svg', $svg->getDocNamespaces()['']);

$rects = $svg->xpath("//svg:rect");
foreach ($rects as $rect) {
    $x = (int)($rect["x"]->__toString());
    $y = (int)($rect["y"]->__toString());
    $group = $rect->xpath("..")[0];
    $group->addAttribute("transform", "translate(-$x,-$y)");
}

$svg->asXML($outFile);

