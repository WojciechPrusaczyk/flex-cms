<?php

namespace App\Controller\User;

use App\Entity\Admin;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(path: '/api', name: "api_")]
class ApiController extends AbstractController
{
    #[Route("/", name: "index", methods: ["GET", "HEAD"])]
    public function index(Request $request): JsonResponse
    {
        $requestUri = $request->getUri();

        return $this->json([
            'status' => 'success',
            'login' => $requestUri."login?password={password}&username={username}",
        ]);
    }

    #[Route('/login', name: 'login', methods: ["POST"])]
    public function login(Request $request, Security $security, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $password = $requestData['password'];
        $username = $requestData['username'];
        $userRepo = $em->getRepository(Admin::class);

        $user = $userRepo->findOneByUsername($username);

        if ($hasher->isPasswordValid($user, $password)) {
            $security->login($user);

            return $this->json([
                'status' => 'success',
                'response' => 'Zalogowano '.$user->getUsername(),
            ]);
        } else {
            return $this->json([
                'status' => 'error',
                'response' => 'Nie zalogowano',
            ]);
        }
    }
}
