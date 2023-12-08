<?php

namespace App\Controller\Admin;

use App\Entity\Colors;
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
    public function getColors(EntityManagerInterface $entityManager): JsonResponse
    {
        // Get the repository for Colors entities
        $colorsRepo = $entityManager->getRepository(Colors::class);

        // Retrieve all colors from the database
        $allColors = $colorsRepo->findAll();

        // Initialize an array to store color items
        $items = [];

        // Iterate through all retrieved colors and create an array for each color
        foreach ($allColors as $item) {
            $items[] = [$item->getId() => [
                "name" => $item->getName(),
                "description" => $item->getDescription(),
                "value" => $item->getValue(),
                "type" => $item->getType(),
            ]];
        }

        // Return a JSON response with the color items
        return new JsonResponse([
            'status' => 'success',
            'response' => [
                "items" => $items,
            ],
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/admin-api/dashboard/colors/set-value', name: 'admin_api_dashboard_colors_set_value', methods: ["GET"])]
    public function setValue(Security $security, Request $request, EntityManagerInterface $em, LoggerInterface $logger): JsonResponse
    {
        // Get the repository for Colors entities
        $settingsRepo = $em->getRepository(Colors::class);

        // Get the requested color ID from the request
        $requestedId = $request->get('id');

        // Find the color entity based on the requested ID (or null if not found)
        $requestedEntity = $settingsRepo->findOneBy(["id" => $requestedId ?: null]);

        if (null != $requestedEntity) {
            // Get the requested color value from the request
            $requestedValue = $request->get('value');

            try {
                // Set the requested color value, ensuring it's of type bool
                $requestedEntity->setValue($requestedValue);

                // Persist the changes to the database
                $em->persist($requestedEntity);
                $em->flush();
            } catch (\Exception $e) {
                // Log and return an error response in case of an exception
                $logger->error('An error occurred: ' . $e->getMessage());
            }

            // Return a success JSON response
            return new JsonResponse([
                'status' => 'success',
                'response' => 'Color has been successfully updated.',
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        // Return an error JSON response if the requested color entity was not found
        return new JsonResponse([
            'status' => 'error',
            'response' => 'Color setting not found.',
        ], 400, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/admin-api/dashboard/colors/add-color', name: 'admin_api_dashboard_colors_add_color', methods: ["GET"])]
    public function addColor(Security $security, Request $request, EntityManagerInterface $em, LoggerInterface $logger): JsonResponse
    {
        // Get the repository for Colors entities
        $colorsRepo = $em->getRepository(Colors::class);

        // Get the requested values from the request
        $requestedName = $request->get('name');
        $requestedValue = $request->get('value');
        $requestedDescription = $request->get('description');
        $requestedType = $request->get('type');

        // Check if all data is requested correctly
        if (
            null != $requestedName &&
            null != $requestedDescription &&
            null != $requestedValue &&
            null != $requestedType
        ) {
            try {
                // Create new entity
                $newColorEntity = new Colors();
                $newColorEntity->setName($requestedName);
                $newColorEntity->setDescription($requestedDescription);
                $newColorEntity->setValue($requestedValue);
                $newColorEntity->setType($requestedType);

                // Persist the changes to the database
                $em->persist($newColorEntity);
                $em->flush();
            } catch (\Exception $e) {
                // Log and return an error response in case of an exception
                $logger->error('An error occurred: ' . $e->getMessage());
            }

            // Return a success JSON response
            return new JsonResponse([
                'status' => 'success',
                'response' => 'Color has been successfully created.',
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        // Return an error JSON response if the requested color entity was not found
        return new JsonResponse([
            'status' => 'error',
            'response' => 'Data is not provided correctly.',
        ], 400, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/admin-api/dashboard/colors/delete', name: 'admin_api_dashboard_colors_delete', methods: ["GET"])]
    public function delete(EntityManagerInterface $em, LoggerInterface $logger, Request $request): Response
    {
        $id = $request->get('id');
        $colorsRepo = $em->getRepository(Colors::class);

        $colorToDelete = $colorsRepo->findOneBy(["id" => $id]);

        if (null != $colorToDelete) {
            try {
                $em->remove($colorToDelete);
                $em->flush();

            } catch (\Exception $e) {
                $logger->error('An error occurred: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute("dashboard_colors");
    }
}
