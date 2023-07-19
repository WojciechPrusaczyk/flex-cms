<?php

namespace App\Controller\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em, Security $security): \Symfony\Component\HttpFoundation\Response
    {
        dd($security->getUser());
        return $this->render("mainPage.html.twig");
    }
}