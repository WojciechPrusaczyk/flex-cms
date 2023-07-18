<?php

namespace App\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render("mainPage.html.twig");
    }
}