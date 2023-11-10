<?php

namespace App\Controller\Admin;

use App\Entity\Scripts;
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

class ScriptsController extends AbstractController
{
    #[Route('/dashboard/scripts', name: 'dashboard_scripts')]
    public function index(): Response
    {
        // Render the scripts index page
        return $this->render('scripts/index.html.twig');
    }

    #[Route('/admin-api/dashboard/scripts/get-scripts', name: 'admin_api_dashboard_scripts_get_scripts', methods: ["GET"])]
    public function getScripts(EntityManagerInterface $entityManager, LoggerInterface $logger ): JsonResponse
    {
        try {
            $scriptsRepo = $entityManager->getRepository(Scripts::class);
            $allScripts = array_reverse($scriptsRepo->findAll());

            $items = [];

            // Recreating entities, to items accessible through JavaScript Frontend
            foreach ($allScripts as $item) {
                $isItemActive = $scriptsRepo->isScriptActive($item->getId());
                $lastEditedBy = $item->getEditedBy() ? $item->getEditedBy()->getUsername() : $item->getAddedBy()->getUsername();

            $items[] = [ $item->getId() => [
                "name" => $item->getName(),
                "lastEditedBy" => $lastEditedBy,
                "active" => $isItemActive,
            ]];
        }

            // Return a JSON response with the list of scripts
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

    #[Route('/dashboard/scripts/new', name: 'dashboard_scripts_new', methods: ["GET"])]
    public function new(EntityManagerInterface $em, LoggerInterface $logger, Security $security): Response
    {
        $scriptsRepo = $em->getRepository(Scripts::class);

        // Creating numeration for new entries
        $namelessScriptsNumber = $scriptsRepo->countNamelessScripts() + 1;

        if (null != $security->getUser()) {
            $currentUser = $security->getUser();

            try {

                // Creating basic entity with masic data included
                $newScript = new Scripts();
                $newScript->setName($scriptsRepo->namelessName . " ($namelessScriptsNumber)");
                $newScript->setAddedBy($currentUser);
                $newScript->setActive(false);
                $newScript->setValue('{"time":0,"blocks":[],"version":"2.28.0"}');
                $newScript->setStartBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));
                $newScript->setStopBeingActive(DateTimeImmutable::createFromFormat('Y-m-d\TH:i', '2000-10-10T00:00'));

                $em->persist($newScript);
                $em->flush();

                return $this->redirectToRoute("dashboard_scripts_edit", ["id" => $newScript->getId()]);
            } catch (\Exception $e) {
                // Log the error
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_scripts");
    }


    #[Route('/admin-api/dashboard/scripts/delete', name: 'admin_api_dashboard_scripts_delete', methods: ["GET"])]
    public function delete(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');
        $scriptsRepo = $em->getRepository(Scripts::class);

        $scriptToDelete = $scriptsRepo->findOneBy(["id" => $id]);

        if (null != $scriptToDelete) {
            try {
                $em->remove($scriptToDelete);
                $em->flush();

                return $this->redirectToRoute("dashboard_scripts");
            } catch (\Exception $e) {
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_scripts");
    }

    #[Route('/dashboard/scripts/edit', name: 'dashboard_scripts_edit', methods: ["GET"])]
    public function edit(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');

        // Transfer ID to template and generating it
        return $this->render('scripts/edit.html.twig', ["id" => $id]);
    }

    #[Route('/admin-api/dashboard/scripts/get-script', name: 'admin_api_dashboard_scripts_get_script', methods: ["GET"])]
    public function getScript(EntityManagerInterface $em, LoggerInterface $logger, Request $request): JsonResponse
    {
        // Get the 'id' parameter from the request
        $id = $request->get('id');

        try {
            // Get the repository for Stylesheets
            $scriptsRepo = $em->getRepository(Scripts::class);

            // Find the script entity by 'id' if provided
            $scriptEntity = (null != $id) ? $scriptsRepo->findOneBy(["id" => $id]) : null;

            if ($scriptEntity) {
                // Prepare the data for the script
                $script = [
                    "id" => $scriptEntity->getId(),
                    "name" => $scriptEntity->getName(),
                    "active" => $scriptEntity->isActive(),
                    "value" => json_decode($scriptEntity->getValue()), // Parsing JSON data
                    "start_being_active" => $scriptEntity->getStartBeingActive(),
                    "stop_being_active" => $scriptEntity->getStopBeingActive(),
                ];

                // Return success response with the script data
                return new JsonResponse([
                    'status' => 'success',
                    'response' => ["entity" => $script],
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

    #[Route('/admin-api/dashboard/scripts/edit-script', name: 'admin_api_dashboard_scripts_edit_script', methods: ["GET"])]
    public function editScript(EntityManagerInterface $em, LoggerInterface $logger, Request $request, Security $security): Response
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
            // Get the repository for Stylesheets
            $scriptsRepo = $em->getRepository(Scripts::class);

            // Find the script entity by 'id' if provided
            $scriptEntity = (null != $rawData["id"]) ? $scriptsRepo->findOneBy(["id" => $rawData["id"]]) : null;

            if ($scriptEntity) {
                // Update the entity with the provided data
                $scriptEntity->setName($rawData["name"]);
                $scriptEntity->setActive($rawData["active"] == "true");
                $scriptEntity->setValue($rawData["value"]);
                $scriptEntity->setEditedBy($security->getUser());

                $dateTimeStart = DateTime::createFromFormat('Y-m-d\TH:i:s', $rawData["start_being_active"]);
                $dateTimeEnd = DateTime::createFromFormat('Y-m-d\TH:i:s', $rawData["stop_being_active"]);

                if ($dateTimeStart !== false && $dateTimeEnd !== false) {
                    // Set the start and stop being active timestamps
                    $scriptEntity->setStartBeingActive(new DateTimeImmutable($dateTimeStart->format('Y-m-d H:i:s')));
                    $scriptEntity->setStopBeingActive(new DateTimeImmutable($dateTimeEnd->format('Y-m-d H:i:s')));
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
                $em->persist($scriptEntity);
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