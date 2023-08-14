<?php

namespace App\Controller\Admin;

use App\Entity\Settings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
    public function getSettings(EntityManagerInterface $entityManager ): JsonResponse
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


    #[Route('/admin-api/dashboard/settings/set-value', name: 'admin_api_dashboard_settings_set_value', methods: ["POST", "GET"])]
    public function setValue(Security $security, Request $request, EntityManagerInterface $em, Filesystem $filesystem): JsonResponse
    {
        $settingsRepo = $em->getRepository(Settings::class);
        $requestedId = $request->get('id');
        $requestedEntity = $settingsRepo->findOneBy(["id" => $requestedId?:null]);

        if ( null != $requestedEntity )
        {

            if ( $requestedEntity->getType() == "file" )
            {
                // ustawienie jest typem pliku
                $uploadedFile = $request->files->get('file');

                if (filesize($uploadedFile) > 5000000 )
                {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => [
                            'message' => 'Przesłany plik jest za duży.'
                        ],
                    ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
                }
                if (null != $uploadedFile)
                {
                    try {
                        $filesystem->remove($this->getParameter('settings_directory')."/".$requestedEntity->getValue());

                        $extension = $uploadedFile->guessExtension();
                        $safeFileName = md5(uniqid()) . '.' . $extension;
                        $validFileTypes = [
                            "gif", "jpg", "png", "svg"
                        ];

                        $uploadedFile->move(
                            $this->getParameter('settings_directory'), // Ścieżka do katalogu, gdzie będą przechowywane przesłane pliki
                            $safeFileName
                        );

                        // dodatkowe warunki sprawdzan przy dodawaniu plików
                        if( file_exists($this->getParameter('settings_directory')."/".$safeFileName) && null != $security->getUser() && in_array($extension, $validFileTypes) )
                        {
                            try {
                                // ustawienie odpowiednich wartości
                                $requestedEntity->setValue($safeFileName);

                                // upload do bazy
                                $em->persist($requestedEntity);
                                $em->flush();

                                return new JsonResponse([
                                    'status' => 'success',
                                    'response' => [
                                        'message' => 'Plik został pomyślnie dodany na serwer.',
                                        'filename' => $safeFileName,
                                    ],
                                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);

                            } catch (\Exception) {  }
                        } else {
                            return new JsonResponse([
                                'status' => 'error',
                                'response' => 'Wystąpił błąd podczas zapisu pliku.',
                            ], 500);
                        }
                    } catch (FileException $e) { }

                } else {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => [
                            'message' => 'Nie przesłano żadnego pliku.'
                        ],
                    ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
                }



            }
            else if ( $requestedEntity->getType() == "text" )
            {
                // ustawienie jest typu tekstowego
                $requestedValue = $request->get('value');

                // ustawienie odpowiednich wartości
                $requestedEntity->setValue($requestedValue);

                // upload do bazy
                $em->persist($requestedEntity);
                $em->flush();

                return new JsonResponse([
                    'status' => 'success',
                    'response' => 'Ustawienie zostało pomyślnie zmienione.',
                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
            }
        }

        return new JsonResponse([
            'status' => 'error',
            'response' => 'Nie znaleziono takiego ustawienia.',
        ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
    }
}
