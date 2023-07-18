<?php

namespace App\Controller;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api', name: "api_")]
class ApiController extends AbstractController
{
    #[Route("/", name: "index", methods: ["GET", "HEAD"])]
    public function index(Request $request): JsonResponse
    {
        return $this->json([
            'check_db_connection' => $request->getUri()."db-connection-test",
        ]);
    }

    #[Route('/db-connection-test', name: '_check_db_connection', methods: ["GET", "HEAD"])]
    public function checkDbConnection(EntityManagerInterface $em): JsonResponse
    {
        $connected = false;
        try {
            $em->getConnection()->connect();
            $connected = $em->getConnection()->isConnected();
        } catch (Exception) {

        }

        return $this->json([
            'message' => ($connected!=null)?"Database is connected":"Error! There is something wrong with database.",
        ]);
    }
}
