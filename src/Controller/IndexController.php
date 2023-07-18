<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $em->getConnection()->connect();
        $connected = $em->getConnection()->isConnected();

        return $this->json([
            'message' => $connected?"yes":"no",
            'path' => 'src/Controller/IndexController.php',
        ]);
    }
}