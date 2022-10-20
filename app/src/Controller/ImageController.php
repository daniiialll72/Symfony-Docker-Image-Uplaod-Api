<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\Image;
use Doctrine\DBAL\Exception;
use App\Service\FileUploader;
use App\Repository\TagRepository;
use App\Repository\ImageRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/v1', name: 'app_image_')]
class ImageController extends AbstractController
{

    private $entityManager;
    private $uploader;
    private $serializer;
    public function __construct(private ManagerRegistry $doctrine, FileUploader $fileUploader, SerializerInterface $serializer)
    {
        $this->entityManager = $doctrine->getManager();
        $this->uploader = $fileUploader;
        $this->serializer = $serializer;
    }

    #[Route('/image', name: 'get', methods: ["GET"])]
    public function index(ImageRepository $imageRepository): Response
    {
        try {
            $images = $imageRepository
                ->findAll();
            $data = [];
            foreach($images as $image){
                $data = [
                    'id' => $image->getId(),
                    'provider' => $image->getProvider(),
                    'tags' => array_map(function($item){
                        return $item->getTitle();
                    },$image->getTags()->toArray()),
                    'path' => $image->getFilePath()
                ];
            }
            return $this->json(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $response = new Response();
            $response->setContent($errorMessage);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/image/search', name: 'search', methods: ["GET"])]
    public function search(Request $request, TagRepository $tagRepository): Response
    {
        try {
            if (!$request->get('tag')) {
                throw $this->createNotFoundException(
                    'Tag parameter is mandatory'
                );
            }
            $tag = $tagRepository->findOneBy(['title' => $request->get('tag')]);
            $images = $tag->getImage();
            if ($request->get('provider')) {
                $res = array_filter($images->toArray(), function ($i) use ($request) {
                    return $i->getProvider() == $request->get('provider');
                });
            }
            $data = [];
            foreach($res as $item){
                $data = [
                    'id' => $item->getId(),
                    'provider' => $item->getProvider(),
                    'tags' => array_map(function($item){
                        return $item->getTitle();
                    },$item->getTags()->toArray()),
                    'path' => $item->getFilePath()
                ];
            }
            return   $this->json(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $response = new Response();
            $response->setContent($errorMessage);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/image/add', name: 'add', methods: ["POST"])]
    public function add(Request $request): Response
    {
        try {
            $image = new Image();
            $image->setProvider($request->get('provider'));

            $uploadedFile = $request->files->get('file');
            if ($uploadedFile) {
                $image->setFilePath($this->uploader->upload($uploadedFile));
            } else {
                $image->setFilePath($this->uploader->uploadByUrl($request->get('file')));
            }

            foreach ($request->get('tags') as $tag_title) {
                $tag = new Tag();
                $tag->setTitle($tag_title);
                $image->addTag($tag);
                $this->entityManager->persist($tag);
            }

            $this->entityManager->persist($image);
            $this->entityManager->flush();

            return   $this->json(['status' => 'success', 'data' => []]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $response = new Response();
            $response->setContent($errorMessage);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }
}
