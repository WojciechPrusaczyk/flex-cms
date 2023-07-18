<?php

namespace App\Controller;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api')]
class ApiController extends AbstractController
{
    #[Route('/db-connection-test', name: 'api_check_db_connection')]
    public function index(EntityManagerInterface $em): JsonResponse
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
