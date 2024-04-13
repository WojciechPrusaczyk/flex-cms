<?php

namespace App\Controller\User;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em): \Symfony\Component\HttpFoundation\Response
    {
        $settingsRepo = $em->getRepository(Settings::class);
        $icon = $settingsRepo->findOneBy([ "name" => "browserTabLogo" ]);
        $title = $settingsRepo->findOneBy([ "name" => "browserTabMainPageTitle" ]);

        if ( null != $icon && null != $icon->getValue() )
        {
            return $this->render("webpage/index.html.twig", [
                "icon" => $icon->getValue(),
                "title" => $title->getValue(),
            ]);
        } else {
            return $this->render("webpage/index.html.twig", [
                "icon" => '<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text></svg>'
            ]);
        }

    }
    #[Route('/gallery', name: 'app_gallery')]
    public function gallery(EntityManagerInterface $em): \Symfony\Component\HttpFoundation\Response
    {
        $settingsRepo = $em->getRepository(Settings::class);
        $isGalleryActive = $settingsRepo->findOneBy([ "name" => "isGalleryActive" ]);
        $icon = $settingsRepo->findOneBy([ "name" => "browserTabLogo" ]);
        $title = $settingsRepo->findOneBy([ "name" => "browserTabGalleryTitle" ]);

        if ( null != $isGalleryActive && $isGalleryActive->getValue() == true )
        {
            if ( null != $icon && null != $icon->getValue() )
            {
                return $this->render("webpage/gallery.html.twig", [
                    "icon" => $icon->getValue(),
                    "title" => $title->getValue(),
                ]);
            } else {
                return $this->render("webpage/gallery.html.twig", [
                    "icon" => '<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text></svg>'
                ]);
            }
        } else {
            throw $this->createNotFoundException("Resource is not active, or does not exist.");
        }

    }
    #[Route('/form', name: 'app_form')]
    public function form(EntityManagerInterface $em): \Symfony\Component\HttpFoundation\Response
    {
        $settingsRepo = $em->getRepository(Settings::class);
        $isFormActive = $settingsRepo->findOneBy([ "name" => "isFormActive" ]);
        $icon = $settingsRepo->findOneBy([ "name" => "browserTabLogo" ]);
        $title = $settingsRepo->findOneBy([ "name" => "browserTabFormTitle" ]);

        if ( null != $isFormActive && $isFormActive->getValue() == true )
        {
            if ( null != $icon && null != $icon->getValue() )
            {
                return $this->render("webpage/form.html.twig", [
                    "icon" => $icon->getValue(),
                    "title" => $title->getValue(),
                ]);
            } else {
                return $this->render("webpage/form.html.twig", [
                    "icon" => '<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text></svg>'
                ]);
            }
        } else {
            throw $this->createNotFoundException("Resource is not active, or does not exist.");
        }
    }
}