<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
    private $targetDirectory;
    private $slugger;
    private $urlHelper;

    public function __construct($targetDirectory, SluggerInterface $slugger, UrlHelper $urlHelper)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->urlHelper = $urlHelper;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            $errorMessage = $e->getMessage();
            $response = new Response();
            $response->setContent($errorMessage);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
    public function getUrl(?string $fileName, bool $absolute = true)
    {
        if (empty($fileName)) return null;

        if ($absolute) {
            return $this->urlHelper->getAbsoluteUrl($this->relativeUploadsDir . $fileName);
        }

        return $this->urlHelper->getRelativePath($this->relativeUploadsDir . $fileName);
    }
    public function uploadByUrl($url)
    {
        $filename = basename($url);
        $file = file_get_contents($url);
        $path = $this->targetDirectory . '/' . $filename;
        file_put_contents($path, $file);

        return $filename;
    }
}
