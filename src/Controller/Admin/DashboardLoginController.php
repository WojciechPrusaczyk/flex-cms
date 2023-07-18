<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/dashboard', name: 'dashboard_')]
class DashboardLoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function index(): Response
    {
        return $this->render('dashboard_login/index.html.twig', [
            'controller_name' => 'DashboardLoginController',
        ]);
    }
}
