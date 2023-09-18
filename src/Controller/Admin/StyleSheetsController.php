<?php

namespace App\Controller\Admin;

use App\Entity\StyleSheets;
use DateTime;
use DateTimeImmutable;
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
                $newStylesheet->setValue('{"time":0,"blocks":[],"version":"2.28.0"}');
                $newStylesheet->setStartBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));
                $newStylesheet->setStopBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));

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

    #[Route('/dashboard/stylesheets/get-stylesheet', name: 'dashboard_stylesheets_get_stylesheet', methods: ["GET"])]
    public function getStylesheet(EntityManagerInterface $em, LoggerInterface $logger, Request $request): JsonResponse
    {
        $id = $request->get('id');

        try {
            $stylesheetsRepo = $em->getRepository(StyleSheets::class);

            $stylesheetEntity = ( null != $id )?$stylesheetsRepo->findOneBy(["id" => $id]):null;

            if ($stylesheetEntity)
            {
                $stylesheet = [
                    "id" => $stylesheetEntity->getId(),
                    "name" => $stylesheetEntity->getName(),
                    "active" => $stylesheetEntity->isActive(),
                    "value" => json_decode($stylesheetEntity->getValue()),// nie wiem jak, ale to działa
                    "start_being_active" => $stylesheetEntity->getStartBeingActive(),
                    "stop_being_active" => $stylesheetEntity->getStopBeingActive(),
                ];

                return new JsonResponse([
                    'status' => 'success',
                    'response' => [ "entity" => $stylesheet ],
                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
            }

            return new JsonResponse([
                'status' => 'not_found',
                'response' => [
                    'message' => "Nie znaleziono encji o podanym id."
                ],
            ], 404, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        } catch (\Exception $e) {
            $logger->error('Wystąpił błąd: ' . $e->getMessage());

            return new JsonResponse([
                'status' => 'error',
                'response' => [
                    'message' => "Wsytąpił błąd 500 na serwerze!"
                ],
            ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }

    #[Route('/dashboard/stylesheets/edit-stylesheet', name: 'dashboard_stylesheets_edit_stylesheet', methods: ["GET"])]
    public function editStylesheet(EntityManagerInterface $em, LoggerInterface $logger, Request $request, Security $security): Response
    {
        $rawData = [
            "id" => $request->get('id'),
            "name" => $request->get('name'),
            "active" => $request->get('active'),
            "value" => $request->get('value'),
            "start_being_active" => $request->get('start_being_active'),
            "stop_being_active" => $request->get('stop_being_active')
        ];
//        dd($rawData);

        try {
            $stylesheetsRepo = $em->getRepository(StyleSheets::class);

            $stylesheetEntity = ( null != $rawData["id"] )?$stylesheetsRepo->findOneBy(["id" => $rawData["id"]]):null;

            $stylesheetEntity->setName($rawData["name"]);
            $stylesheetEntity->setActive($rawData["active"] == "true");
            $stylesheetEntity->setValue($rawData["value"]);
            $stylesheetEntity->setEditedBy( $security->getUser() );

            $dateTimeStart = DateTime::createFromFormat('Y-m-d\TH:i:s', $rawData["start_being_active"]);
            $dateTimeEnd = DateTime::createFromFormat('Y-m-d\TH:i:s', $rawData["stop_being_active"]);
            if ($dateTimeStart !== false && $dateTimeEnd !== false)
            {
                $stylesheetEntity->setStartBeingActive(new DateTimeImmutable($dateTimeStart->format('Y-m-d H:i:s')));
                $stylesheetEntity->setStopBeingActive(new DateTimeImmutable($dateTimeEnd->format('Y-m-d H:i:s')));
            } else {
                return new JsonResponse([
                    'status' => 'error',
                    'response' => [
                        'message' => "Wsytąpił przy ustawianiu czasu!"
                    ],
                ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
            }
            $em->persist($stylesheetEntity);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'response' => [
                    'message' => "Encja została dodana do bazy danych."
                ],
            ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);

        } catch (\Exception $e) {
            $logger->error('Wystąpił błąd: ' . $e->getMessage());

            return new JsonResponse([
                'status' => 'error',
                'response' => [
                    'message' => "Wystąpił błąd 500 na serwerze!"
                ],
            ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }
}