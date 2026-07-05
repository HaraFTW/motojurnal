<?php

$source = $argv[1] ?? null;

if ($source === null || ! is_file($source)) {
    fwrite(STDERR, "Usage: php scripts/generate-pwa-icons.php <source-image>\n");
    exit(1);
}

$outputDir = __DIR__.'/../public/app-icons';

if (! is_dir($outputDir) && ! mkdir($outputDir, 0777, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, "Could not create {$outputDir}\n");
    exit(1);
}

copy($source, $outputDir.'/icon-source.png');

$image = imagecreatefrompng($source);
imagesavealpha($image, true);

function resizePng($image, int $size, string $path, int $padding = 0): void
{
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);

    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);

    $sourceWidth = imagesx($image);
    $sourceHeight = imagesy($image);
    $inner = $size - ($padding * 2);
    $scale = min($inner / $sourceWidth, $inner / $sourceHeight);
    $targetWidth = (int) round($sourceWidth * $scale);
    $targetHeight = (int) round($sourceHeight * $scale);
    $offsetX = (int) floor(($size - $targetWidth) / 2);
    $offsetY = (int) floor(($size - $targetHeight) / 2);

    imagecopyresampled(
        $canvas,
        $image,
        $offsetX,
        $offsetY,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $sourceWidth,
        $sourceHeight,
    );

    imagepng($canvas, $path);
    imagedestroy($canvas);
}

$sizes = [
    16 => 'favicon-16x16.png',
    32 => 'favicon-32x32.png',
    180 => 'apple-touch-icon.png',
    192 => 'icon-192.png',
    512 => 'icon-512.png',
];

foreach ($sizes as $size => $filename) {
    resizePng($image, $size, $outputDir.'/'.$filename);
}

resizePng($image, 512, $outputDir.'/icon-512-maskable.png', padding: 64);

imagedestroy($image);

$favicon32 = $outputDir.'/favicon-32x32.png';
$faviconIco = __DIR__.'/../public/favicon.ico';
$png = file_get_contents($favicon32);

if ($png !== false) {
    $ico = pack('vvv', 0, 1, 1)
        .pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($png), 6 + 16)
        .$png;
    file_put_contents($faviconIco, $ico);
}

echo "Icons written to {$outputDir}\n";
echo "Favicon written to {$faviconIco}\n";
