<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\DashboardSettings;
use App\Entity\Photos;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: '/admin-api', name: "admin_api_")]
class AdminApiController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    #[Route("/", name: "index", methods: ["GET", "HEAD"])]
    public function index(Request $request, Security $security): JsonResponse
    {
        // REST API
        if (null != $security->getUser())
        {
            $requestUri = $request->getUri();
            return $this->json([
                'status' => 'success',
                'check_db_connection' => $requestUri."db-connection-scripts",
                'register' => $requestUri."register?password={password}&username={username}",
                'get-user' => $requestUri."get-user",
                'logout' => $requestUri."logout",
                'dashboard-settings-get-dashboard-settings' => $requestUri."dashboard/get-dashboard-settings",
                'admin-api-dashboard-colors'=> [
                    '-get-scripts' => $requestUri."dashboard/colors/get-colors",
                    '-set-value' => $requestUri."dashboard/colors/set-value?id={id}&value={value}",
                    '-add-color' => $requestUri."dashboard/colors/add-color?name={name}&description={description}&value={value}&type={type}",
                    '-delete' => $requestUri."dashboard/colors/delete?id={id}",
                ],
                'admin-api-dashboard-gallery'=> [
                    '-upload-photo' => $requestUri."dashboard/gallery/upload-photo",
                    '-get-photos' => $requestUri."dashboard/gallery/get-photos?page={page}&quantity={quantity}",
                    '-delete-photo' => $requestUri."dashboard/gallery/delete-photo?id={id}",
                ],
                'admin-api-dashboard-scripts'=> [
                    '-get-scripts' => $requestUri."dashboard/scripts/get-scripts",
                    '-get-script' => $requestUri."dashboard/scripts/get-script?id={id}",
                    '-edit-script' => $requestUri."dashboard/scripts/edit-script?id={id}&name={name}&active={active}&value={value}&start_being_active={start_being_active}&stop_being_active={stop_being_active}",
                    '-delete' => $requestUri."dashboard/scripts/delete?id={id}",
                ],
                'admin-api-dashboard-sections'=> [
                    '-get-sections' => $requestUri."dashboard/sections/get-sections",
                    '-get-section' => $requestUri."dashboard/sections/get-section?id={id}",
                    '-edit-section' => $requestUri."dashboard/sections/edit-section?id={id}&name={name}&active={active}&isWide={isWide}&value={value}&start_being_active={start_being_active}&stop_being_active={stop_being_active}&isTitleVisible={isTitleVisible}",
                    '-delete' => $requestUri."dashboard/sections/delete?id={id}",
                    '-change-order' => $requestUri."dashboard/sections/change-order",
                ],
                'admin-api-dashboard-settings'=> [
                    '-get-scripts' => $requestUri."dashboard/settings/get-settings",
                    '-set-value' => $requestUri."dashboard/settings/set-value?id={id}&value={value}",
                    '-add-setting' => $requestUri."dashboard/settings/add-setting?name={name}&type={type}&value={value}&description={description}&isEditable={isEditable}&isPublic={isPublic}",
                    '-delete' => $requestUri."dashboard/settings/delete?id={id}",
                ],
                'admin-api-dashboard-stylesheets'=> [
                    '-get-stylesheets' => $requestUri."dashboard/stylesheets/get-stylesheets",
                    '-get-stylesheet' => $requestUri."dashboard/stylesheets/get-stylesheet?id={id}",
                    '-edit-stylesheet' => $requestUri."dashboard/stylesheets/edit-stylesheet?id={id}&name={name}&active={active}&value={value}&start_being_active={start_being_active}&stop_being_active={stop_being_active}",
                    '-delete' => $requestUri."dashboard/stylesheets/delete?id={id}",
                ],
                ]);
        }
        return $this->json([
            'status' => 'error',
            'response' => 'Not authenticated',
        ]);
    }

    #[Route('/db-connection-scripts', name: '_check_db_connection', methods: ["GET"])]
    public function checkDbConnection(EntityManagerInterface $em, Security $security, LoggerInterface $logger): JsonResponse
    {
        // Check if the user is authenticated
        if (null != $security->getUser()) {
            $connected = false;
            try {
                // Attempt to connect to the database
                $em->getConnection()->connect();
                $connected = $em->getConnection()->isConnected();
            } catch (Exception $e) {
                // Log critical errors
                $logger->critical('Critical error: ' . $e->getMessage());
            }

            // Return a JSON response indicating the database connection status
            return $this->json([
                'message' => ($connected != null) ? "Database is connected" : "Error! There is something wrong with the database.",
            ]);
        }

        // Return an error JSON response if the user is not authenticated
        return $this->json([
            'status' => 'error',
            'response' => 'Not authenticated',
        ]);
    }

    #[Route('/get-user', name: '_get_user', methods: ["GET"])]
    public function getCurrentUser(Security $security, EntityManagerInterface $em): JsonResponse
    {
        // Check if the user is authenticated
        if (null != $security->getUser()) {
            $user = $security->getUser();

            return new JsonResponse([
                'status' => 'success',
                'response' => [
                    'user' => [
                        'username' => $user->getUsername(),
                        'accountCreated' => $user->getAccountCreated(),
                        'lastLoginDate' => $user->getLastLoginDate(),
                        'active' => $user->isActive(),
                    ]
                ],
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        } else {
            return new JsonResponse([
                'status' => 'error',
                'response' => 'Not authenticated',
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }

    #[Route('/logout', name: 'logout', methods: ["GET"])]
    public function logout(Security $security): JsonResponse
    {
        // Check if the user is authenticated
        if (null != $security->getUser()) {
            // Logout the user
            $security->logout(false);

            return new JsonResponse([
                'status' => 'success',
                'response' => 'User has been logged out',
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        } else {
            return new JsonResponse([
                'status' => 'error',
                'response' => 'Not authenticated',
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        }
    }

    #[Route('/dashboard/get-dashboard-settings', name: 'dashboard_get_dashboard_settings', methods: ["GET"])]
    public function getDashboardSettings(Security $security, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Get the base URL from the request
        $requestUri = $request->getBaseUrl();

        // Get the repository for DashboardSettings
        $dashboardSettingsRepo = $em->getRepository(DashboardSettings::class);

        $settingsArray = null;

        // Check if dashboard settings are visible based on app configuration
        if ($this->getParameter('app.are_settings_visible') == 1) {
            $settingsArray = $dashboardSettingsRepo->findAll();
        } else if ($this->getParameter('app.are_settings_visible') == 0) {
            $settingsArray = $dashboardSettingsRepo->findBy(["isActive" => 1]);
        }

        // Create an array to store dashboard settings
        $settings = [];
        foreach ($settingsArray as $setting) {
            $settings[] = [
                "name" => $setting->getName(),
                "href" => $requestUri . $setting->getEnglishName(),
                "icon" => $setting->getIconFileName(),
                "isActive" => $setting->isActive()
            ];
        }

        // Return a JSON response with the dashboard settings
        return new JsonResponse([
            'status' => 'success',
            'response' => [$settings],
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

}
