<?php

namespace App\Controller\Admin;

use App\Entity\Colors;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ColorsController extends AbstractController
{
    #[Route('/dashboard/colors', name: 'dashboard_colors')]
    public function index(): Response
    {
        return $this->render('colors/index.html.twig', [
            'controller_name' => 'ColorsController',
        ]);
    }

    #[Route('/admin-api/dashboard/colors/get-colors', name: 'admin_api_dashboard_colors_get_colors', methods: ["GET"])]
    public function getColors(EntityManagerInterface $entityManager ): JsonResponse
    {
        $colorsRepo = $entityManager->getRepository(Colors::class);
        $allColors = $colorsRepo->findAll();

        $items = [];

        foreach ($allColors as $item)
        {
            $items[] = [ $item->getId() => [
                "name" => $item->getName(),
                "description" => $item->getDescription(),
                "value" => $item->getValue(),
                "type" => $item->getType(),
            ]];
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => [
                "items" => $items,
            ],
        ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/admin-api/dashboard/colors/set-value', name: 'admin_api_dashboard_colors_set_value', methods: ["GET"])]
    public function setValue(Security $security, Request $request, EntityManagerInterface $em, Filesystem $filesystem): JsonResponse
    {
        $settingsRepo = $em->getRepository(Colors::class);
        $requestedId = $request->get('id');
        $requestedEntity = $settingsRepo->findOneBy(["id" => $requestedId?:null]);

        if ( null != $requestedEntity )
        {
            $requestedValue = $request->get('value');

            try{
                // ustawienie odpowiednich wartości, przy upewnieniu się czy zmienna jest typu bool
                $requestedEntity->setValue($requestedValue);

                // upload do bazy
                $em->persist($requestedEntity);
                $em->flush();
            } catch (\Exception $e) {}

            return new JsonResponse([
                'status' => 'success',
                'response' => 'Kolor został pomyślnie zmieniony.',
            ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        return new JsonResponse([
            'status' => 'error',
            'response' => 'Nie znaleziono takiego ustawienia.',
        ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
    }
}
