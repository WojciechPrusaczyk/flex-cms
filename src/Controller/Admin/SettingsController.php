<?php

namespace App\Controller\Admin;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends AbstractController
{
    #[Route('/dashboard/settings', name: 'dashboard_settings')]
    public function index(): Response
    {
        return $this->render('settings/index.html.twig', [
            'controller_name' => 'SettingsController',
        ]);
    }

    #[Route('/admin-api/dashboard/settings/get-settings', name: 'admin_api_dashboard_settings_get_settings', methods: ["GET"])]
    public function getSettings(EntityManagerInterface $entityManager, Request $request )
    {
        $settingsRepo = $entityManager->getRepository(Settings::class);
        $allSettings = $settingsRepo->findBy(["isEditable" => true ]);

        $items = [];

        foreach ($allSettings as $item)
        {
            $items[] = [ $item->getId() => [
                "name" => $item->getName(),
                "type" => $item->getType(),
                "value" => $item->getValue(),
                "description" => $item->getDescription(),
            ]];
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => [
                "items" => $items,
            ],
        ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
    }
}
