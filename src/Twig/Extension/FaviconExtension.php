<?php

declare(strict_types=1);

namespace InSquare\OpendxpFaviconBundle\Twig\Extension;

use Symfony\Component\Asset\Packages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FaviconExtension extends AbstractExtension
{
    public function __construct(
        private Packages $packages,
        private string $themeColor,
        private string $tileColor
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_favicon', [$this, 'renderFavicon'], ['is_safe' => ['html']]),
        ];
    }

    public function renderFavicon(): string
    {
        if (!$this->hasRequiredFiles()) {
            return '';
        }

        $tags = [
            $this->linkTag('apple-touch-icon', '57x57', 'favicon/apple-icon-57x57.png'),
            $this->linkTag('apple-touch-icon', '60x60', 'favicon/apple-icon-60x60.png'),
            $this->linkTag('apple-touch-icon', '72x72', 'favicon/apple-icon-72x72.png'),
            $this->linkTag('apple-touch-icon', '76x76', 'favicon/apple-icon-76x76.png'),
            $this->linkTag('apple-touch-icon', '114x114', 'favicon/apple-icon-114x114.png'),
            $this->linkTag('apple-touch-icon', '120x120', 'favicon/apple-icon-120x120.png'),
            $this->linkTag('apple-touch-icon', '144x144', 'favicon/apple-icon-144x144.png'),
            $this->linkTag('apple-touch-icon', '152x152', 'favicon/apple-icon-152x152.png'),
            $this->linkTag('apple-touch-icon', '180x180', 'favicon/apple-icon-180x180.png'),
            $this->iconTag('192x192', 'favicon/android-icon-192x192.png'),
            $this->iconTag('32x32', 'favicon/favicon-32x32.png'),
            $this->iconTag('96x96', 'favicon/favicon-96x96.png'),
            $this->iconTag('16x16', 'favicon/favicon-16x16.png'),
            sprintf('<link rel="manifest" href="%s">', $this->assetUrl('favicon/manifest.json')),
            sprintf('<meta name="msapplication-TileColor" content="%s">', $this->tileColor),
            sprintf('<meta name="msapplication-TileImage" content="%s">', $this->assetUrl('favicon/ms-icon-144x144.png')),
            sprintf('<meta name="theme-color" content="%s">', $this->themeColor),
        ];

        return implode("\n", $tags);
    }

    private function hasRequiredFiles(): bool
    {
        $dir = $this->getFaviconDir();
        if (!is_dir($dir)) {
            return false;
        }

        $required = [
            'favicon-16x16.png',
            'favicon-32x32.png',
            'android-icon-192x192.png',
            'manifest.json',
            'ms-icon-144x144.png',
        ];

        foreach ($required as $file) {
            if (!is_file($dir . '/' . $file)) {
                return false;
            }
        }

        return true;
    }

    private function linkTag(string $rel, string $size, string $assetPath): string
    {
        return sprintf('<link rel="%s" sizes="%s" href="%s">', $rel, $size, $this->assetUrl($assetPath));
    }

    private function iconTag(string $size, string $assetPath): string
    {
        return sprintf('<link rel="icon" type="image/png" sizes="%s" href="%s">', $size, $this->assetUrl($assetPath));
    }

    private function assetUrl(string $path): string
    {
        return $this->packages->getUrl($path);
    }

    private function getFaviconDir(): string
    {
        $webRoot = \defined('OPENDXP_WEB_ROOT')
            ? OPENDXP_WEB_ROOT
            : \dirname(__DIR__, 6) . '/public';

        return $webRoot . '/favicon';
    }
}
