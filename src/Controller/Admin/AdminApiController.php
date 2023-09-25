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
                    '-edit-section' => $requestUri."dashboard/sections/edit-section",
                    '-delete' => $requestUri."dashboard/sections/delete?id={id}",
                    '-change-order' => $requestUri."dashboard/sections/change-order",
                ],
                'admin-api-dashboard-settings'=> [
                    '-get-scripts' => $requestUri."dashboard/settings/get-settings",
                    '-set-value' => $requestUri."dashboard/settings/set-value?id={id}&value={value}",
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

    #[Route('/register', name: '_register', methods: ["POST"])]
    public function createAdmin(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em, Security $security, LoggerInterface $logger): JsonResponse
    {
        // Check if the user is authenticated
        if (null == $security->getUser() || [] == $security->getUser()) {
            return new JsonResponse([
                'status' => 'error',
                'response' => 'Not authenticated',
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        // Get relevant data for creating an admin
        $requestData = json_decode($request->getContent(), true);
        $requestedPassword = $requestData['password'];
        $requestedUsername = $requestData['username'];
        $dateTimeNow = new \DateTime('@' . strtotime('now'));

        // Validate password and username
        $errors = [
            'password' => $this->validatePassword($requestedPassword),
            'username' => $this->validateUsername($requestedUsername)
        ];

        // Validation
        if (count($errors['password']) > 0 || count($errors['username']) > 0) {
            return new JsonResponse([
                'status' => 'error',
                'response' => 'There was an error in the provided registration data.',
                'errors' => $errors,
            ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
        } else {
            try {
                // Create a new admin user
                $admin = new Admin();

                $hashedPassword = $hasher->hashPassword(
                    $admin,
                    $requestedPassword
                );

                $admin->setPassword($hashedPassword);
                $admin->setAccountCreated($dateTimeNow);
                $admin->setActive(true);
                $admin->setUsername($requestedUsername);

                $em->persist($admin);
                $em->flush();
            } catch (\Exception $e) {
                // Log critical errors
                $logger->critical('Critical error: ' . $e->getMessage());

                return new JsonResponse([
                    'status' => 'error',
                    'response' => 'Error 500! A critical server-side error occurred.',
                ], 500, ['Content-Type' => 'application/json;charset=UTF-8']);
            }
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => 'Administrator has been created',
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
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


    // Function used to validate usernames
    private function validateUsername(string $providedUsername): array
    {
        /*
         * Username conditions:
         * - At least 3 characters
         * - Unique (not already taken)
         */

        // Get the repository for Admin entities
        $adminsRepo = $this->entityManager->getRepository(Admin::class);

        // Initialize an array to store validation errors
        $errors = [];

        // Check if the provided username is too short
        if (strlen($providedUsername) < 3) {
            array_push($errors, "Username is too short.");
        }

        // Check if the provided username is already taken
        if ($adminsRepo->findOneBy(['username' => $providedUsername]) !== null) {
            array_push($errors, "Username is already taken.");
        }

        // Return an array of errors if there are any
        // If there are no errors, the function returns an empty array
        if (count($errors) > 0) {
            return $errors;
        } else {
            return [];
        }
    }

    // Function used to validate passwords
    private function validatePassword(string $providedPassword): array
    {
        /*
         * Password conditions:
         * - At least 8 characters
         * - At least 1 uppercase letter
         * - At least 1 digit
         * - At least 1 special character
         */

        // Initialize an array to store validation errors
        $errors = [];

        // Check if the provided password is too short
        if (strlen($providedPassword) < 8) {
            array_push($errors, "Password is too short.");
        }

        // Check if the provided password contains at least one uppercase letter
        if (!preg_match('/(?=\S*[A-Z])/', $providedPassword)) {
            array_push($errors, "Password must contain at least one uppercase letter.");
        }

        // Check if the provided password contains at least one digit
        if (!preg_match('~[0-9]+~', $providedPassword)) {
            array_push($errors, "Password must contain at least one digit.");
        }

        // Check if the provided password contains at least one special character
        if (!preg_match('/[\'^£!%&*()}{@#~?><>,|=_+-]/', $providedPassword)) {
            array_push($errors, "Password must contain at least one special character.");
        }

        // Return an array of errors if there are any
        // If there are no errors, the function returns an empty array
        if (count($errors) > 0) {
            return $errors;
        } else {
            return [];
        }
    }

}
