<?php

namespace App\Controller\Admin;

use App\Entity\Sections;
use App\Entity\Stylesheets;
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

class SectionsController extends AbstractController
{
    #[Route('/dashboard/sections', name: 'dashboard_sections')]
    public function index(): Response
    {
        // Render the sections index page
        return $this->render('sections/index.html.twig');
    }

    #[Route('/admin-api/dashboard/sections/get-sections', name: 'admin_api_dashboard_sections_get_sections', methods: ["GET"])]
    public function getSections(EntityManagerInterface $entityManager, LoggerInterface $logger ): JsonResponse
    {
        try {
            $sectionsRepo = $entityManager->getRepository(Sections::class);
            $allSections = $sectionsRepo->findBy([], ["position" => "ASC"]);

            $items = [];

            // Recreating entities, to items accessible through JavaScript Frontend
            foreach ($allSections as $item) {
                $isItemActive = $sectionsRepo->isSectionActive($item->getId());
                $lastEditedBy = $item->getEditedBy() ? $item->getEditedBy()->getUsername() : $item->getAddedBy()->getUsername();

                $items[] = [ $item->getId() => [
                    "name" => $item->getName(),
                    "lastEditedBy" => $lastEditedBy,
                    "active" => $isItemActive,
                    "value" => $item->getValue(),
                    "position" => $item->getPosition(),
                    "isTitleVisible" => $item->isTitleVisible(),
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

            dd($e->getMessage());
            // Log error
            $logger->error('An error occurred: ' . $e->getMessage());

            // Handle any exceptions that may occur
            return new JsonResponse([
                'status' => 'error',
                'message' => 'A critical error occurred.',
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }

    #[Route('/dashboard/sections/new', name: 'dashboard_sections_new', methods: ["GET"])]
    public function new(EntityManagerInterface $em, LoggerInterface $logger, Security $security): Response
    {
        $sectionsRepo = $em->getRepository(Sections::class);

        // Creating numeration for new entries
        $namelessSectionsNumber = $sectionsRepo->countNamelessSections() + 1;
        $validPosition = $sectionsRepo->findValidPosition();

        if (null != $security->getUser()) {
            $currentUser = $security->getUser();

            try {

                // Creating basic entity with masic data included
                $newSection = new Sections();
                $newSection->setName($sectionsRepo->namelessName . " ($namelessSectionsNumber)");
                $newSection->setAddedBy($currentUser);
                $newSection->setActive(false);
                $newSection->setWide(true);
                $newSection->setIsTitleVisible(true);
                $newSection->setPosition($validPosition);
                $newSection->setValue(json_decode('{"time":0,"blocks":[],"version":"2.28.0"}', true));
                $newSection->setStartBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));
                $newSection->setStopBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));

                $em->persist($newSection);
                $em->flush();

                return $this->redirectToRoute("dashboard_sections_edit", ["id" => $newSection->getId()]);
            } catch (\Exception $e) {
                // Log the error
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_sections");
    }

    #[Route('/dashboard/sections/edit', name: 'dashboard_sections_edit', methods: ["GET"])]
    public function edit(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');

        // Transfer ID to template and generating it
        return $this->render('sections/edit.html.twig', ["id" => $id]);
    }

    #[Route('/admin-api/dashboard/sections/get-section', name: 'admin_api_dashboard_sections_get_section', methods: ["GET"])]
    public function getStylesheet(EntityManagerInterface $em, LoggerInterface $logger, Request $request): JsonResponse
    {
        // Get the 'id' parameter from the request
        $id = $request->get('id');

        try {
            // Get the repository for Sections
            $sectionsRepo = $em->getRepository(Sections::class);

            // Find the section entity by 'id' if provided
            $sectionEntity = (null != $id) ? $sectionsRepo->findOneBy(["id" => $id]) : null;

            if ($sectionEntity) {
                // Prepare the data for the section
                $sectionEntity = [
                    "id" => $sectionEntity->getId(),
                    "name" => $sectionEntity->getName(),
                    "active" => $sectionEntity->isActive(),
                    "isWide" => $sectionEntity->isWide(),
                    "value" => $sectionEntity->getValue(), // Parsing JSON data
                    "start_being_active" => $sectionEntity->getStartBeingActive(),
                    "stop_being_active" => $sectionEntity->getStopBeingActive(),
                    "isTitleVisible" => $sectionEntity->isTitleVisible(),
                ];

                // Return success response with the stylesheet data
                return new JsonResponse([
                    'status' => 'success',
                    'response' => ["entity" => $sectionEntity],
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

    #[Route('/admin-api/dashboard/sections/edit-section', name: 'admin_api_dashboard_section_edit_section', methods: ["POST"])]
    public function editStylesheet(EntityManagerInterface $em, LoggerInterface $logger, Request $request, Security $security): Response
    {
        $requestData = json_decode($request->getContent(), true);

        // Extract raw data from the POST request
        $rawData = [
            "id" => $requestData['id']?:null,
            "name" => $requestData['name']?:null,
            "active" => $requestData['active']?:false,
            "isWide" => $requestData['isWide']?:false,
            "value" => $requestData['value']?:null,
            "start_being_active" =>  $requestData['start_being_active']?:null,
            "stop_being_active" => $requestData['stop_being_active']?:null,
            "isTitleVisible" => $requestData['isTitleVisible']?:false,
        ];
        try {
            // Get the repository for Sections repository
            $sectionsRepo = $em->getRepository(Sections::class);

            // Find the section entity by 'id' if provided
            $sectionEntity = (null != $rawData["id"]) ? $sectionsRepo->findOneBy(["id" => $rawData["id"]]) : null;

            if ($sectionEntity) {
                // Update the entity with the provided data
                $sectionEntity->setName($rawData["name"]);
                $sectionEntity->setActive($rawData["active"] == "true");
                $sectionEntity->setWide($rawData["isWide"] == "true");
                $sectionEntity->setIsTitleVisible($rawData["isTitleVisible"] == "true");
                $sectionEntity->setValue(json_decode($rawData["value"], true));
                $sectionEntity->setEditedBy($security->getUser());

                try {
                    // Set the start being active timestamps
                    $sectionEntity->setStartBeingActive(new DateTimeImmutable($rawData["start_being_active"]));
                } catch(\Exception $e) {
                    // Return an error response if there's an issue with timestamp formatting
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => [
                            'message' => "An error occurred while setting the start time!",
                        ],
                    ], 400, ['Content-Type' => 'application/json;charset=UTF-8']);
                }
                try {
                    // Set the stop being active timestamps
                    $sectionEntity->setStopBeingActive(new DateTimeImmutable($rawData["stop_being_active"]));
                } catch(\Exception $e) {
                    // Return an error response if there's an issue with timestamp formatting
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => [
                            'message' => "An error occurred while setting end the time!",
                        ],
                    ], 400, ['Content-Type' => 'application/json;charset=UTF-8']);
                }

                // Persist changes and flush to the database
                $em->persist($sectionEntity);
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

    #[Route('/admin-api/dashboard/sections/delete', name: 'admin_api_dashboard_sections_delete', methods: ["GET"])]
    public function delete(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');
        $sectionsRepo = $em->getRepository(Sections::class);

        $sectionToDelete = $sectionsRepo->findOneBy(["id" => $id]);

        if (null != $sectionToDelete) {
            try {
                $em->remove($sectionToDelete);
                $em->flush();

                return $this->redirectToRoute("dashboard_sections");
            } catch (\Exception $e) {
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_stylesheets");
    }

    #[Route('/admin-api/dashboard/sections/change-order', name: 'admin_api_dashboard_sections_change_order', methods: ["POST"])]
    public function changeOrder(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);

        $sectionsRepo = $em->getRepository(Sections::class);
        $allSections = $sectionsRepo->findAll();

        if (null != $requestData && count($allSections) === count($requestData) ) {
            try {
                foreach ($requestData as $requestedPosition)
                {
                    $entity = $sectionsRepo->findOneBy(["id" => $requestedPosition["id"]]);
                    $entity->setPosition($requestedPosition["position"]);

                    $em->persist($entity);
                }
                $em->flush();

                return $this->redirectToRoute("dashboard_sections");
            } catch (\Exception $e) {
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_sections");
    }
}