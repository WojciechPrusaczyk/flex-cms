<?php

namespace App\Controller\Admin;

use App\Entity\Settings;
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
    public function getSettings(EntityManagerInterface $entityManager): JsonResponse
    {
        // Get the repository for settings
        $settingsRepo = $entityManager->getRepository(Settings::class);

        // Find all settings that are editable
        $allSettings = $settingsRepo->findBy(["isEditable" => true]);

        $items = [];

        // Loop through each setting and build the response array
        foreach ($allSettings as $item)
        {
            $items[] = [$item->getId() => [
                "name" => $item->getName(),
                "type" => $item->getType(),
                "value" => $item->getValue(),
                "description" => $item->getDescription(),
            ]];
        }

        // Return a JSON response with the settings data
        return new JsonResponse([
            'status' => 'success',
            'response' => [
                "items" => $items,
            ],
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/admin-api/dashboard/settings/set-value', name: 'admin_api_dashboard_settings_set_value', methods: ["POST", "GET"])]
    public function setValue(Security $security, Request $request, EntityManagerInterface $em, Filesystem $filesystem, LoggerInterface $logger): JsonResponse
    {
        $settingsRepo = $em->getRepository(Settings::class);
        $requestedId = $request->get('id');

        // Find the requested setting entity by ID
        $requestedEntity = $settingsRepo->findOneBy(["id" => $requestedId ?: null]);

        if (null != $requestedEntity) {
            if ($requestedEntity->getType() == "file") {
                // Setting is of type "file"
                $uploadedFile = $request->files->get('file');

                if (filesize($uploadedFile) > 5000000) {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => [
                            'message' => 'The uploaded file is too large.'
                        ],
                    ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
                }

                if (null != $uploadedFile) {
                    try {
                        $filesystem->remove($this->getParameter('settings_directory') . "/" . $requestedEntity->getValue());

                        $extension = $uploadedFile->guessExtension();
                        $safeFileName = md5(uniqid()) . '.' . $extension;
                        $validFileTypes = [
                            "gif", "jpg", "png", "svg"
                        ];

                        $uploadedFile->move(
                            $this->getParameter('settings_directory'), // Path to the directory where files will be stored
                            $safeFileName
                        );

                        if (file_exists($this->getParameter('settings_directory') . "/" . $safeFileName) && null != $security->getUser() && in_array($extension, $validFileTypes)) {
                            try {
                                // Set the appropriate value
                                $requestedEntity->setValue($safeFileName);

                                // Upload to the database
                                $em->persist($requestedEntity);
                                $em->flush();

                                return new JsonResponse([
                                    'status' => 'success',
                                    'response' => [
                                        'message' => 'File has been successfully added to the server.',
                                        'filename' => $safeFileName,
                                    ],
                                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
                            } catch (\Exception) { }
                        } else {
                            return new JsonResponse([
                                'status' => 'error',
                                'response' => 'An error occurred while saving the file.',
                            ], 500);
                        }
                    } catch (FileException $e) {
                        // Log the error
                        $logger->error('An error occurred: ' . $e->getMessage());
                    }
                } else {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => [
                            'message' => 'No file was uploaded.'
                        ],
                    ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
                }
            } elseif ($requestedEntity->getType() == "text") {
                // Setting is of type "text"
                $requestedValue = $request->get('value');

                // Set the appropriate value
                $requestedEntity->setValue($requestedValue);

                // Upload to the database
                $em->persist($requestedEntity);
                $em->flush();

                return new JsonResponse([
                    'status' => 'success',
                    'response' => 'Setting has been successfully changed.',
                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
            } elseif ($requestedEntity->getType() == "boolean") {
                // Setting is of type "boolean"
                $requestedValue = $request->get('value');

                try {
                    // Set the appropriate value, ensuring it's a boolean
                    $requestedEntity->setValue(filter_var($requestedValue, FILTER_VALIDATE_BOOLEAN) ? 1 : 0);

                    // Upload to the database
                    $em->persist($requestedEntity);
                    $em->flush();
                } catch (\Exception $e) {
                    // Log the error
                    $logger->error('An error occurred: ' . $e->getMessage());
                }

                return new JsonResponse([
                    'status' => 'success',
                    'response' => 'Setting has been successfully changed.',
                ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
            }

            return new JsonResponse([
                'status' => 'error',
                'response' => 'A critical error occurred while changing the setting value.',
            ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        return new JsonResponse([
            'status' => 'error',
            'response' => 'The requested setting was not found.',
        ], 400, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/admin-api/dashboard/settings/add-setting', name: 'admin_api_dashboard_settings_add_setting', methods: ["GET"])]
    public function addColor(Security $security, Request $request, EntityManagerInterface $em, LoggerInterface $logger): JsonResponse
    {
        // Get the repository for Settings entities
        $settingsRepo = $em->getRepository(Settings::class);

        // Get the requested values from the request
        $requestedName = $request->get('name');
        $requestedType = $request->get('type');
        $requestedDescription = $request->get('description');
        $requestedValue = $request->get('value');
        $requestedIsEditable = $request->get('isEditable');
        $requestedIsPublic = $request->get('isPublic');

        // Check if all data is requested correctly
        if (
            null != $requestedName &&
            null != $requestedType &&
            null != $requestedDescription &&
            null != $requestedValue &&
            null != $requestedIsEditable &&
            null != $requestedIsPublic
        ) {
            /*try {*/
                // Create new entity
                $newSettingEntity = new Settings();
                $newSettingEntity->setName($requestedName);
                $newSettingEntity->setType($requestedType);
                $newSettingEntity->setDescription($requestedDescription);
                $newSettingEntity->setValue($requestedValue);
                $newSettingEntity->setEditable($requestedIsEditable);
                $newSettingEntity->setPublic($requestedIsPublic);

                // Persist the changes to the database
                $em->persist($newSettingEntity);
                $em->flush();
            /*} catch (\Exception $e) {
                // Log and return an error response in case of an exception
                $logger->error('An error occurred: ' . $e->getMessage());
            }*/

            // Return a success JSON response
            return new JsonResponse([
                'status' => 'success',
                'response' => 'Setting has been successfully created.',
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        // Return an error JSON response if the requested color entity was not found
        return new JsonResponse([
            'status' => 'error',
            'response' => 'Data is not provided correctly.',
        ], 400, ['Content-Type' => 'application/json;charset=UTF-8']);
    }
}
