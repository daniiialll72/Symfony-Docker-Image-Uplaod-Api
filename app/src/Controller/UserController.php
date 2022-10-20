<?php

namespace App\Controller;

use Doctrine\DBAL\Exception;
use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/v1', name: 'app_user_')]
class UserController extends AbstractController
{
    private $entityManager;
    private $serializer;
    public function __construct(private ManagerRegistry $doctrine, SerializerInterface $serializer)
    {
        $this->entityManager = $doctrine->getManager();
        $this->serializer = $serializer;
    }

    #[Route('/user/{id}/add', name: 'add', methods: ["POST"])]
    public function addToLibrary(Request $request, UserRepository $userRepository, ImageRepository $imageRepository, int $id): Response
    {
        try {
            $user = $userRepository->find($id);
            if (!$user) {
                throw $this->createNotFoundException(
                    'No User found for id ' . $id
                );
            };
            $image = $imageRepository->find($request->get('image_id'));
            if (!$image) {
                throw $this->createNotFoundException(
                    'No Image found for id ' . $request->get('image_id')
                );
            }
            $user->addImage($image);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return   $this->json(['status' => 'success', 'data' => []]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $response = new Response();
            $response->setContent($errorMessage);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{id}/library', name: 'library', methods: ["GET"])]
    public function index(UserRepository $userRepository, int $id): Response
    {
        try {
            $user = $userRepository
                ->find($id);
            $library = $user->getImages();
            $data = [];
            foreach($library as $item){
                $data = [
                    'id' => $item->getId(),
                    'provider' => $item->getProvider(),
                    'tags' => array_map(function($item){
                        return $item->getTitle();
                    },$item->getTags()->toArray()),
                    'path' => $item->getFilePath()
                ];
            }
            return $this->json($data);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $response = new Response();
            $response->setContent($errorMessage);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }
}
