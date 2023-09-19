<?php

namespace App\Controller\Admin;

use App\Entity\StyleSheets;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StyleSheetsController extends AbstractController
{
    #[Route('/dashboard/stylesheets', name: 'dashboard_stylesheets')]
    public function index(): Response
    {
        // Render the stylesheets index page
        return $this->render('stylesheets/index.html.twig');
    }

    #[Route('/admin-api/dashboard/stylesheets/get-stylesheets', name: 'admin_api_dashboard_stylesheets_get_stylesheets', methods: ["GET"])]
    public function getStylesheets(EntityManagerInterface $entityManager, LoggerInterface $logger ): JsonResponse
    {
        try {
            $stylesheetsRepo = $entityManager->getRepository(StyleSheets::class);
            $allStylesheets = array_reverse($stylesheetsRepo->findAll());

            $items = [];

            // Recreating entities, to items accessible through JavaScript Frontend
            foreach ($allStylesheets as $item) {
                $isItemActive = $stylesheetsRepo->isStylesheetActive($item->getId());
                $lastEditedBy = $item->getEditedBy() ? $item->getEditedBy()->getUsername() : $item->getAddedBy()->getUsername();

            $items[] = [ $item->getId() => [
                "name" => $item->getName(),
                "lastEditedBy" => $lastEditedBy,
                "active" => $isItemActive,
            ]];
        }

            // Return a JSON response with the list of stylesheets
            return new JsonResponse([
                'status' => 'success',
                'response' => [
                    'items' => $items,
                ],
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        } catch (\Exception $e) {

            // Log error
            $logger->error('An error occurred: ' . $e->getMessage());

            // Handle any exceptions that may occur
            return new JsonResponse([
                'status' => 'error',
                'message' => "An internal server error occurred."
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }

    #[Route('/dashboard/stylesheets/new', name: 'dashboard_stylesheets_new', methods: ["GET"])]
    public function new(EntityManagerInterface $em, LoggerInterface $logger, Security $security): Response
    {
        $stylesheetsRepo = $em->getRepository(StyleSheets::class);

        // Creating numeration for new entries
        $namelessStylesheetsNumber = $stylesheetsRepo->countNamelessStylesheets() + 1;

        if (null != $security->getUser()) {
            $currentUser = $security->getUser();

            try {

                // Creating basic entity with masic data included
                $newStylesheet = new StyleSheets();
                $newStylesheet->setName($stylesheetsRepo->namelessName . " ($namelessStylesheetsNumber)");
                $newStylesheet->setAddedBy($currentUser);
                $newStylesheet->setActive(false);
                $newStylesheet->setValue('{"time":0,"blocks":[],"version":"2.28.0"}');
                $newStylesheet->setStartBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));
                $newStylesheet->setStopBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));

                $em->persist($newStylesheet);
                $em->flush();

                return $this->redirectToRoute("dashboard_stylesheets_edit", ["id" => $newStylesheet->getId()]);
            } catch (\Exception $e) {
                // Log the error
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_stylesheets");
    }


    #[Route('/dashboard/stylesheets/delete', name: 'dashboard_stylesheets_delete', methods: ["GET"])]
    public function delete(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');
        $stylesheetsRepo = $em->getRepository(StyleSheets::class);

        $stylesheetToDelete = $stylesheetsRepo->findOneBy(["id" => $id]);

        if (null != $stylesheetToDelete) {
            try {
                $em->remove($stylesheetToDelete);
                $em->flush();

                return $this->redirectToRoute("dashboard_stylesheets");
            } catch (\Exception $e) {
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_stylesheets");
    }

    #[Route('/dashboard/stylesheets/edit', name: 'dashboard_stylesheets_edit', methods: ["GET"])]
    public function edit(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');

        // Transfer ID to template and generating it
        return $this->render('stylesheets/edit.html.twig', ["id" => $id]);
    }

    #[Route('/dashboard/stylesheets/get-stylesheet', name: 'dashboard_stylesheets_get_stylesheet', methods: ["GET"])]
    public function getStylesheet(EntityManagerInterface $em, LoggerInterface $logger, Request $request): JsonResponse
    {
        // Get the 'id' parameter from the request
        $id = $request->get('id');

        try {
            // Get the repository for StyleSheets
            $stylesheetsRepo = $em->getRepository(StyleSheets::class);

            // Find the stylesheet entity by 'id' if provided
            $stylesheetEntity = (null != $id) ? $stylesheetsRepo->findOneBy(["id" => $id]) : null;

            if ($stylesheetEntity) {
                // Prepare the data for the stylesheet
                $stylesheet = [
                    "id" => $stylesheetEntity->getId(),
                    "name" => $stylesheetEntity->getName(),
                    "active" => $stylesheetEntity->isActive(),
                    "value" => json_decode($stylesheetEntity->getValue()), // Parsing JSON data
                    "start_being_active" => $stylesheetEntity->getStartBeingActive(),
                    "stop_being_active" => $stylesheetEntity->getStopBeingActive(),
                ];

                // Return success response with the stylesheet data
                return new JsonResponse([
                    'status' => 'success',
                    'response' => ["entity" => $stylesheet],
                ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
            }

            // Return a not found response if the entity is not found
            return new JsonResponse([
                'status' => 'not_found',
                'response' => [
                    'message' => "Entity with the provided id not found."
                ],
            ], 404, ['Content-Type' => 'application/json;charset=UTF-8']);
        } catch (\Exception $e) {
            // Log and return an error response in case of an exception
            $logger->error('An error occurred: ' . $e->getMessage());

            return new JsonResponse([
                'status' => 'error',
                'response' => [
                    'message' => "An internal server error occurred."
                ],
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }

    #[Route('/dashboard/stylesheets/edit-stylesheet', name: 'dashboard_stylesheets_edit_stylesheet', methods: ["GET"])]
    public function editStylesheet(EntityManagerInterface $em, LoggerInterface $logger, Request $request, Security $security): Response
    {
        // Extract raw data from the request
        $rawData = [
            "id" => $request->get('id'),
            "name" => $request->get('name'),
            "active" => $request->get('active'),
            "value" => $request->get('value'),
            "start_being_active" => $request->get('start_being_active'),
            "stop_being_active" => $request->get('stop_being_active')
        ];

        try {
            // Get the repository for StyleSheets
            $stylesheetsRepo = $em->getRepository(StyleSheets::class);

            // Find the stylesheet entity by 'id' if provided
            $stylesheetEntity = (null != $rawData["id"]) ? $stylesheetsRepo->findOneBy(["id" => $rawData["id"]]) : null;

            if ($stylesheetEntity) {
                // Update the entity with the provided data
                $stylesheetEntity->setName($rawData["name"]);
                $stylesheetEntity->setActive($rawData["active"] == "true");
                $stylesheetEntity->setValue($rawData["value"]);
                $stylesheetEntity->setEditedBy($security->getUser());

                $dateTimeStart = DateTime::createFromFormat('Y-m-d\TH:i:s', $rawData["start_being_active"]);
                $dateTimeEnd = DateTime::createFromFormat('Y-m-d\TH:i:s', $rawData["stop_being_active"]);

                if ($dateTimeStart !== false && $dateTimeEnd !== false) {
                    // Set the start and stop being active timestamps
                    $stylesheetEntity->setStartBeingActive(new DateTimeImmutable($dateTimeStart->format('Y-m-d H:i:s')));
                    $stylesheetEntity->setStopBeingActive(new DateTimeImmutable($dateTimeEnd->format('Y-m-d H:i:s')));
                } else {
                    // Return an error response if there's an issue with timestamp formatting
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => [
                            'message' => "An error occurred while setting the time!"
                        ],
                    ], 400, ['Content-Type' => 'application/json;charset=UTF-8']);
                }

                // Persist changes and flush to the database
                $em->persist($stylesheetEntity);
                $em->flush();

                // Return a success response
                return new JsonResponse([
                    'status' => 'success',
                    'response' => [
                        'message' => "Entity has been added to the database."
                    ],
                ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
            }

            // Return a not found response if the entity is not found
            return new JsonResponse([
                'status' => 'not_found',
                'response' => [
                    'message' => "Entity with the provided id not found."
                ],
            ], 404, ['Content-Type' => 'application/json;charset=UTF-8']);
        } catch (\Exception $e) {
            // Log and return an error response in case of an exception
            $logger->error('An error occurred: ' . $e->getMessage());

            return new JsonResponse([
                'status' => 'error',
                'response' => [
                    'message' => "An internal server error occurred."
                ],
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }
}