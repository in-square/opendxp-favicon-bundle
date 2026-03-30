<?php

declare(strict_types=1);

namespace InSquare\OpendxpFaviconBundle\Service;

use OpenDxp\Image;
use Symfony\Component\Filesystem\Filesystem;

final class FaviconGenerator
{
    private const APPLE_SIZES = [57, 60, 72, 76, 114, 120, 144, 152, 180];
    private const ANDROID_SIZES = [36, 48, 72, 96, 144, 192];
    private const FAVICON_SIZES = [16, 32, 96];
    private const MS_TILE_SIZE = 144;

    public function __construct(
        private Filesystem $filesystem,
        private string $manifestName,
        private string $manifestBasePath
    ) {
    }

    public function generate(string $sourcePath, string $targetDir): void
    {
        if (!is_file($sourcePath)) {
            throw new \RuntimeException(sprintf('Source image "%s" not found.', $sourcePath));
        }

        $this->filesystem->mkdir($targetDir);

        foreach (self::APPLE_SIZES as $size) {
            $this->generateIcon($sourcePath, $targetDir . sprintf('/apple-icon-%dx%d.png', $size, $size), $size);
        }

        foreach (self::ANDROID_SIZES as $size) {
            $this->generateIcon($sourcePath, $targetDir . sprintf('/android-icon-%dx%d.png', $size, $size), $size);
        }

        foreach (self::FAVICON_SIZES as $size) {
            $this->generateIcon($sourcePath, $targetDir . sprintf('/favicon-%dx%d.png', $size, $size), $size);
        }

        $this->generateIcon(
            $sourcePath,
            $targetDir . sprintf('/ms-icon-%dx%d.png', self::MS_TILE_SIZE, self::MS_TILE_SIZE),
            self::MS_TILE_SIZE
        );

        $this->writeManifest($targetDir . '/manifest.json');
    }

    private function generateIcon(string $sourcePath, string $targetPath, int $size): void
    {
        $image = Image::getInstance();
        if (!$image->load($sourcePath)) {
            throw new \RuntimeException(sprintf('Unable to load source image "%s".', $sourcePath));
        }

        $image->cover($size, $size);
        $image->save($targetPath, 'png');
    }

    private function writeManifest(string $targetPath): void
    {
        $icons = [];
        $densities = [
            36 => '0.75',
            48 => '1.0',
            72 => '1.5',
            96 => '2.0',
            144 => '3.0',
            192 => '4.0',
        ];

        foreach ($densities as $size => $density) {
            $icons[] = [
                'src' => sprintf('%s/android-icon-%dx%d.png', $this->getManifestBasePath(), $size, $size),
                'sizes' => sprintf('%dx%d', $size, $size),
                'type' => 'image/png',
                'density' => $density,
            ];
        }

        $manifest = [
            'name' => $this->manifestName,
            'icons' => $icons,
        ];

        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Unable to encode manifest.json.');
        }

        $this->filesystem->dumpFile($targetPath, $json . "\n");
    }

    private function getManifestBasePath(): string
    {
        $basePath = '/' . ltrim($this->manifestBasePath, '/');
        return rtrim($basePath, '/');
    }
}
