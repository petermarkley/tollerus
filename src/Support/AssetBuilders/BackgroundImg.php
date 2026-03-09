<?php

namespace PeterMarkley\Tollerus\Support\AssetBuilders;

final class BackgroundImg
{
    private string $sourcePath;
    private string $outputPath;
    private string|int $seed;

    public function __construct(
        string $sourcePath = null,
        string $outputPath = null,
        string|int|null $seed = null
    ) {
        $this->sourcePath = $sourcePath ?? __DIR__.'/../../../resources/bg_svg_src/glyph_palette.svg';
        $this->outputPath = $outputPath ?? public_path('vendor/tollerus/bg.svg');
        $this->seed = $seed ?? time();
    }

    /**
     * Takes the Glyph palette in `resources/bg_svg_src/` and
     * generates an artistic background image. Returns true on
     * success, false on failure.
     */
    private function generate(): bool
    {
        // Load SVG data
        $svg = simplexml_load_file($this->sourcePath);
        $svg->registerXPathNamespace('svg', $svg->getDocNamespaces()['']);
        $paths = $svg->xpath("//svg:path");

        // Choose random glyphs
        $x_num = $y_num = 10;
        $num = $x_num * $y_num;
        mt_srand((int)$this->seed);

        // Create file
        $y_ratio = sqrt(3)/2;
        $width = 10000;
        $scale = 0.8;
        $height = $y_ratio*$width;
        $output = ["<svg width=\"$width\" height=\"$height\" viewBox=\"0 0 $width $height\" xmlns=\"http://www.w3.org/2000/svg\">"];
        for ($y=0; $y < $y_num; $y++) {
            $y_val = ($height/$y_num)*($y+0.5);
            for ($x=0; $x < $x_num; $x++) {
                $x_interval = ($width/$x_num);
                $x_val = $x_interval * ($x + ($y%2 ? 0.75 : 0.25));
                $i = mt_rand(0,count($paths)-1);

                // Drop glyph at <$x_val, $y_val>
                $path = clone $paths[$i];
                $path->addAttribute("transform", "translate($x_val,$y_val),scale($scale),translate(-500,-500)");
                $output[] = $path->asXML();
                // $output[] = "<circle cx=\"$x_val\" cy=\"$y_val\" r=\"50\" style=\"fill:#ff0000;\"/>";

                /**
                 * Some glyphs stick out of bounds (especially cursive Tengwar), so
                 * to be extra tile-safe let's put another copy of each edge glyph
                 * on the opposite side of the canvas.
                 */
                if ($x==0 && !(bool)($y%2)) {
                    // Left edge
                    $x_new = $x_val + $width;
                    $y_new = $y_val;
                    $path = clone $paths[$i];
                    $path->addAttribute("transform", "translate($x_new,$y_new),scale($scale),translate(-500,-500)");
                    $output[] = $path->asXML();
                } else if ($x==$x_num-1 && $y%2) {
                    // Right edge
                    $x_new = $x_val - $width;
                    $y_new = $y_val;
                    $path = clone $paths[$i];
                    $path->addAttribute("transform", "translate($x_new,$y_new),scale($scale),translate(-500,-500)");
                    $output[] = $path->asXML();
                }
                if ($y==0) {
                    // Top edge
                    $x_new = $x_val;
                    $y_new = $y_val + $height;
                    $path = clone $paths[$i];
                    $path->addAttribute("transform", "translate($x_new,$y_new),scale($scale),translate(-500,-500)");
                    $output[] = $path->asXML();
                    if ($x==0) {
                        // Top-left corner
                        $x_new = $x_val + $width;
                        $y_new = $y_val + $height;
                        $path = clone $paths[$i];
                        $path->addAttribute("transform", "translate($x_new,$y_new),scale($scale),translate(-500,-500)");
                        $output[] = $path->asXML();
                    }
                } else if ($y == $y_num-1) {
                    // Bottom edge
                    $x_new = $x_val;
                    $y_new = $y_val - $height;
                    $path = clone $paths[$i];
                    $path->addAttribute("transform", "translate($x_new,$y_new),scale($scale),translate(-500,-500)");
                    $output[] = $path->asXML();
                    if ($x == $x_num-1) {
                        // Top-right corner
                        $x_new = $x_val - $width;
                        $y_new = $y_val - $height;
                        $path = clone $paths[$i];
                        $path->addAttribute("transform", "translate($x_new,$y_new),scale($scale),translate(-500,-500)");
                        $output[] = $path->asXML();
                    }
                }
            }
        }
        $output[] = "</svg>";
        $output[] = "";

        // Save output
        file_put_contents($this->outputPath,implode("\n",$output));
        return true;
    }

    /**
     * Convenience method that deletes file first
     */
    public function generateForce(): bool
    {
        if (file_exists($this->outputPath)) {
            unlink($this->outputPath);
        }
        return $this->generate();
    }

    /**
     * Idempotent convenience method, checks first and only
     * runs if the file is absent.
     */
    public function generateIfMissing(): bool
    {
        if (file_exists($this->outputPath)) {
            return true;
        } else {
            return $this->generate();
        }
    }
}
