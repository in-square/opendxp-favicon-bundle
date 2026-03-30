<?php

declare(strict_types=1);

namespace InSquare\OpendxpFaviconBundle\Controller\Admin;

use InSquare\OpendxpFaviconBundle\Service\FaviconGenerator;
use OpenDxp\Bundle\AdminBundle\Controller\AdminAbstractController;
use OpenDxp\Image;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

class FaviconController extends AdminAbstractController
{
    private const SOURCE_FILENAME = 'source.png';

    #[Route('/display', name: 'insquare_opendxp_favicon_display', methods: ['GET'], options: ['expose' => true])]
    public function displayAction(): StreamedResponse
    {
        $this->checkPermission('favicon_settings');

        $sourcePath = $this->getSourcePath();
        if (!is_file($sourcePath)) {
            return $this->createPlaceholderResponse();
        }

        $stream = fopen($sourcePath, 'rb');
        $mime = mime_content_type($sourcePath) ?: 'image/png';

        return new StreamedResponse(function () use ($stream): void {
            if (is_resource($stream)) {
                fpassthru($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Security-Policy' => "script-src 'none'",
        ]);
    }

    #[Route('/upload', name: 'insquare_opendxp_favicon_upload', methods: ['POST'], options: ['expose' => true])]
    public function uploadAction(
        Request $request,
        Filesystem $filesystem,
        FaviconGenerator $faviconGenerator
    ): JsonResponse
    {
        $this->checkPermission('favicon_settings');

        $uploadedFile = $request->files->get('Filedata');
        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException('No file uploaded.');
        }

        $extension = strtolower((string) $uploadedFile->guessExtension());
        if (!in_array($extension, ['png', 'jpg', 'jpeg'], true)) {
            throw new \RuntimeException('Unsupported file format.');
        }

        $targetDir = $this->getFaviconDir();
        $filesystem->mkdir($targetDir);
        $this->clearDirectory($filesystem, $targetDir);

        $image = Image::getInstance();
        if (!$image->load($uploadedFile->getPathname())) {
            throw new \RuntimeException('Unable to read the uploaded file.');
        }

        $image->save($this->getSourcePath(), 'png');
        $faviconGenerator->generate($this->getSourcePath(), $targetDir);

        $response = $this->adminJson(['success' => true]);
        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    #[Route('/delete', name: 'insquare_opendxp_favicon_delete', methods: ['DELETE'], options: ['expose' => true])]
    public function deleteAction(Filesystem $filesystem): JsonResponse
    {
        $this->checkPermission('favicon_settings');

        $this->clearDirectory($filesystem, $this->getFaviconDir());

        return $this->adminJson(['success' => true]);
    }

    private function getFaviconDir(): string
    {
        $webRoot = \defined('OPENDXP_WEB_ROOT')
            ? OPENDXP_WEB_ROOT
            : \dirname(__DIR__, 6) . '/public';

        return $webRoot . '/favicon';
    }

    private function getSourcePath(): string
    {
        return $this->getFaviconDir() . '/' . self::SOURCE_FILENAME;
    }

    private function createPlaceholderResponse(): StreamedResponse
    {
        $transparentPng = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='
        );

        return new StreamedResponse(function () use ($transparentPng): void {
            echo $transparentPng;
        }, 200, [
            'Content-Type' => 'image/png',
            'Content-Security-Policy' => "script-src 'none'",
        ]);
    }

    private function clearDirectory(Filesystem $filesystem, string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        foreach (glob($directory . '/*') as $path) {
            $filesystem->remove($path);
        }
    }
}
