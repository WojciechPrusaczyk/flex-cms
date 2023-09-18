<?php

namespace App\Controller\Admin;

use App\Entity\StyleSheets;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StyleSheetsController extends AbstractController
{
    #[Route('/dashboard/stylesheets', name: 'dashboard_stylesheets')]
    public function index(): Response
    {
        return $this->render('stylesheets/index.html.twig');
    }

    #[Route('/admin-api/dashboard/stylesheets/get-stylesheets', name: 'admin_api_dashboard_stylesheets_get_stylesheets', methods: ["GET"])]
    public function getStylesheets(EntityManagerInterface $entityManager ): JsonResponse
    {
        $stylesheetsRepo = $entityManager->getRepository(StyleSheets::class);
        $allStylesheets = array_reverse($stylesheetsRepo->findAll());

        $items = [];

        foreach ($allStylesheets as $item)
        {
            $isItemActive = $stylesheetsRepo->isStylesheetActive($item->getId());
            $lastEditedBy = ($item->getEditedBy())?$item->getEditedBy()->getUsername():$item->getAddedBy()->getUsername();

            $items[] = [ $item->getId() => [
                "name" => $item->getName(),
                "lastEditedBy" => $lastEditedBy,
                "active" => $isItemActive,
            ]];
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => [
                "items" => $items,
            ],
        ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/dashboard/stylesheets/new', name: 'dashboard_stylesheets_new')]
    public function new(EntityManagerInterface $em, LoggerInterface $logger, Security $security): Response
    {
        $stylesheetsRepo = $em->getRepository(StyleSheets::class);
        $namelessStylesheetsNumber = $stylesheetsRepo->countNamelessStylesheets()+1;

        if (null != $security->getUser()) {
            $currentUser = $security->getUser();

            try {
                $newStylesheet = new StyleSheets();
                $newStylesheet->setName($stylesheetsRepo->namelessName .  " ($namelessStylesheetsNumber)");
                $newStylesheet->setAddedBy($currentUser);
                $newStylesheet->setActive(false);

                $em->persist($newStylesheet);
                $em->flush();

                return $this->redirectToRoute( "dashboard_stylesheets_edit", [ "id" => $newStylesheet->getId() ] );
            } catch (\Exception $e) { $logger->error('Wystąpił błąd: ' . $e->getMessage()); }
        }

        return $this->redirectToRoute( "dashboard_stylesheets" );
    }

    #[Route('/dashboard/stylesheets/delete', name: 'dashboard_stylesheets_delete')]
    public function edit(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');
        $stylesheetsRepo = $em->getRepository(StyleSheets::class);

        $stylesheetToDelete = $stylesheetsRepo->findOneBy( ["id" => $id] );

        if (null != $stylesheetToDelete)
        {
            try {

                $em->remove($stylesheetToDelete);
                $em->flush();

                return $this->redirectToRoute( "dashboard_stylesheets" );
            } catch (\Exception $e) {
                $logger->error('Wystąpił błąd: ' . $e->getMessage());
                return $this->redirectToRoute( "dashboard_stylesheets" );
            }
        }
        return $this->redirectToRoute( "dashboard_stylesheets" );
    }

    #[Route('/dashboard/stylesheets/edit', name: 'dashboard_stylesheets_edit')]
    public function delete(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');

        return $this->render('stylesheets/edit.html.twig', ["id" => $id]);
    }


//    #[Route('/admin-api/dashboard/settings/set-value', name: 'admin_api_dashboard_settings_set_value', methods: ["POST", "GET"])]
//    public function setValue(Security $security, Request $request, EntityManagerInterface $em, Filesystem $filesystem): JsonResponse
//    {
//        $stylesheetsRepo = $em->getRepository(StyleSheets::class);
//        $requestedId = $request->get('id');
//        $requestedEntity = $settingsRepo->findOneBy(["id" => $requestedId?:null]);
//
//        if ( null != $requestedEntity )
//        {
//
//            if ( $requestedEntity->getType() == "file" )
//            {
//                // ustawienie jest typem pliku
//                $uploadedFile = $request->files->get('file');
//
//                if (filesize($uploadedFile) > 5000000 )
//                {
//                    return new JsonResponse([
//                        'status' => 'error',
//                        'response' => [
//                            'message' => 'Przesłany plik jest za duży.'
//                        ],
//                    ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
//                }
//                if (null != $uploadedFile)
//                {
//                    try {
//                        $filesystem->remove($this->getParameter('settings_directory')."/".$requestedEntity->getValue());
//
//                        $extension = $uploadedFile->guessExtension();
//                        $safeFileName = md5(uniqid()) . '.' . $extension;
//                        $validFileTypes = [
//                            "gif", "jpg", "png", "svg"
//                        ];
//
//                        $uploadedFile->move(
//                            $this->getParameter('settings_directory'), // Ścieżka do katalogu, gdzie będą przechowywane przesłane pliki
//                            $safeFileName
//                        );
//
//                        // dodatkowe warunki sprawdzan przy dodawaniu plików
//                        if( file_exists($this->getParameter('settings_directory')."/".$safeFileName) && null != $security->getUser() && in_array($extension, $validFileTypes) )
//                        {
//                            try {
//                                // ustawienie odpowiednich wartości
//                                $requestedEntity->setValue($safeFileName);
//
//                                // upload do bazy
//                                $em->persist($requestedEntity);
//                                $em->flush();
//
//                                return new JsonResponse([
//                                    'status' => 'success',
//                                    'response' => [
//                                        'message' => 'Plik został pomyślnie dodany na serwer.',
//                                        'filename' => $safeFileName,
//                                    ],
//                                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
//
//                            } catch (\Exception) {  }
//                        } else {
//                            return new JsonResponse([
//                                'status' => 'error',
//                                'response' => 'Wystąpił błąd podczas zapisu pliku.',
//                            ], 500);
//                        }
//                    } catch (FileException $e) { }
//
//                } else {
//                    return new JsonResponse([
//                        'status' => 'error',
//                        'response' => [
//                            'message' => 'Nie przesłano żadnego pliku.'
//                        ],
//                    ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
//                }
//
//
//
//            }
//            else if ( $requestedEntity->getType() == "text" )
//            {
//                // ustawienie jest typu tekstowego
//                $requestedValue = $request->get('value');
//
//                // ustawienie odpowiednich wartości
//                $requestedEntity->setValue($requestedValue);
//
//                // upload do bazy
//                $em->persist($requestedEntity);
//                $em->flush();
//
//                return new JsonResponse([
//                    'status' => 'success',
//                    'response' => 'Ustawienie zostało pomyślnie zmienione.',
//                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
//            }
//            else if ( $requestedEntity->getType() == "boolean" )
//            {
//                // ustawienie jest typu tekstowego
//                $requestedValue = $request->get('value');
//
//                try{
//                    // ustawienie odpowiednich wartości, przy upewnieniu się czy zmienna jest typu bool
//                    $requestedEntity->setValue(filter_var($requestedValue, FILTER_VALIDATE_BOOLEAN)?1:0);
//
//                    // upload do bazy
//                    //dd($requestedEntity);
//                    $em->persist($requestedEntity);
//                    $em->flush();
//                } catch (\Exception $e) {}
//
//                return new JsonResponse([
//                    'status' => 'success',
//                    'response' => 'Ustawienie zostało pomyślnie zmienione.',
//                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
//            }
//            return new JsonResponse([
//                'status' => 'error',
//                'response' => 'Wystąpił błąd krytyczny przy zmianie wartości ustawienia.',
//            ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
//        }
//
//        return new JsonResponse([
//            'status' => 'error',
//            'response' => 'Nie znaleziono takiego ustawienia.',
//        ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
//    }
}
