<?php

namespace App\Controller\User;

use App\Entity\Admin;
use App\Entity\Colors;
use App\Entity\Photos;
use App\Entity\Scripts;
use App\Entity\Sections;
use App\Entity\Settings;
use App\Entity\Stylesheets;
use App\Repository\StylesheetsRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use EditorJS\EditorJS;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(path: '/api', name: "api_")]
class ApiController extends AbstractController
{
    private $em;
    private $configPath;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag){
        $this->em = $entityManager;
        $this->configPath = $parameterBag->get('editor_js_sections_config_path');
    }

    #[Route("/", name: "index", methods: ["GET", "HEAD"])]
    public function index(Request $request): JsonResponse
    {
        $requestUri = $request->getUri();

        return $this->json([
            'status' => 'success',
            'login' => $requestUri."login?password={password}&username={username}",
            'register' => $requestUri."register",
            'scripts' => $requestUri."get-scripts",
            'stylesheets' => $requestUri."get-stylesheets",
            'colors' => $requestUri."get-colors",
            'settings' => $requestUri."get-settings",
            'sections' => $requestUri."get-sections",
            'photos' => $requestUri."get-photos",
        ]);
    }

    #[Route('/login', name: 'login', methods: ["POST"])]
    public function login(Request $request, Security $security, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $password = $requestData['password'];
        $username = $requestData['username'];
        $userRepo = $this->em->getRepository(Admin::class);

        $user = $userRepo->findOneByUsername($username);

        if ($hasher->isPasswordValid($user, $password)) {
            $security->login($user);

            return $this->json([
                'status' => 'success',
                'response' => 'Zalogowano '.$user->getUsername(),
            ]);
        } else {
            return $this->json([
                'status' => 'error',
                'response' => 'Nie zalogowano',
            ]);
        }
    }

    #[Route('/register', name: '_register', methods: ["POST"])]
    public function createAdmin(Request $request, UserPasswordHasherInterface $hasher, Security $security, LoggerInterface $logger): JsonResponse
    {
        $adminsRepo = $this->em->getRepository(Admin::class);
        $admins = $adminsRepo->findAll();

        // Check if the user is authenticated
        if ( ( null == $security->getUser() || [] == $security->getUser() ) && count($admins) > 0 ) {
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

                $this->em->persist($admin);
                $this->em->flush();
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

    #[Route('/get-colors', name:'get_colors', methods: ["GET"])]
    public function getColors(): JsonResponse
    {
        $colorsRepo = $this->em->getRepository(Colors::class);
        $colors = $colorsRepo->findAll();
        $data  = [];

        foreach( $colors as $color ) {

            $data[ $color->getId() ] = [
                "name" => $color->getName(),
                "value" => $color->getValue(),
                "type" => $color->getType(),
            ];
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => $data,
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/get-stylesheets', name:'get_stylesheets', methods: ["GET"])]
    public function getStylesheets(): JsonResponse
    {
        $stylesheetsRepo = $this->em->getRepository(\App\Entity\Stylesheets::class);
        $stylesheets = $stylesheetsRepo->findAllAvilableStylesheets();
        $data  = [];

        foreach( $stylesheets as $stylesheet ) {

            $stylesheetId = $stylesheet->getId();

            if ( $stylesheetsRepo->isStylesheetActive($stylesheetId) )
            {
                $value = json_decode( $stylesheet->getValue(), true )["blocks"];
                $rewrittenValue = "";
                foreach ($value as $row)
                {
                    $rewrittenValue .= $row["data"]["text"];
                }

                // wycinanie linków
                $rewrittenValue = preg_replace('/<a\s.*?>(.*?)<\/a>/', '$1', $rewrittenValue);

                $valuesToCut = [
                    "<br>", "&nbsp;", " ", "<b>", "</b>", "<i>", "</i>"
                ];

                foreach ($valuesToCut as $valueToCut)
                {
                    $rewrittenValue = str_replace($valueToCut, "", $rewrittenValue);
                }

                $data[ $stylesheetId ] = [
                    "name" => $stylesheet->getName(),
                    "value" => $rewrittenValue ,
                ];
            }
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => $data,
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/get-scripts', name:'get_scripts', methods: ["GET"])]
    public function getScripts(): JsonResponse
    {
        $scriptsRepo = $this->em->getRepository(Scripts::class);
        $scripts = $scriptsRepo->findAllAvilableScripts();
        $data  = [];

        foreach( $scripts as $script ) {

            $scriptId = $script->getId();

            if ( $scriptsRepo->isScriptActive($scriptId) )
            {
                $value = json_decode( $script->getValue(), true )["blocks"];
                $rewrittenValue = "";
                foreach ($value as $row)
                {
                    $rewrittenValue .= $row["data"]["text"];
                }

                // wycinanie linków
                $rewrittenValue = preg_replace('/<a\s.*?>(.*?)<\/a>/', '$1', $rewrittenValue);

                $valuesToCut = [
                    "<br>", "&nbsp;", " ", "<b>", "</b>", "<i>", "</i>"
                ];
                foreach ($valuesToCut as $valueToCut)
                {
                    $rewrittenValue = str_replace($valueToCut, "", $rewrittenValue);
                }

                $data[ $scriptId ] = [
                    "name" => $script->getName(),
                    "value" => $rewrittenValue ,
                ];
            }
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => $data,
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/get-settings', name:'get_settings', methods: ["GET"])]
    public function getSettings(): JsonResponse
    {
        $settingsRepo = $this->em->getRepository(Settings::class);
        $settings = $settingsRepo->findAll();
        $data  = [];

        foreach( $settings as $setting ) {
            if ( $setting->isPublic() )
            {
                $data[ $setting->getId() ] = [
                    "name" => $setting->getName(),
                    "value" => $setting->getValue(),
                    "type" => $setting->getType(),
                ];
            }
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => $data,
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/get-sections', name:'get_sections', methods: ["GET"])]
    public function getSections(): JsonResponse
    {
        $sectionsRepo = $this->em->getRepository(Sections::class);
        $sections = $sectionsRepo->findBy([], ["position" => "ASC"]);
        $data  = [];

        foreach( $sections as $section ) {

            $sectionId = $section->getId();

            if ( $sectionsRepo->isSectionActive($sectionId) )
            {
                try {
                    $jsonValue = json_encode( $section->getValue(), true );
                    $editor = new EditorJS( $jsonValue, file_get_contents($this->configPath));
                    $blocks = $editor->getBlocks();

                    $html = "";

                    foreach ($blocks as $block) {
                        switch ($block['type']) {
                            case 'paragraph':
                                $html .= '<p>' . $block['data']['text'] . '</p>';
                                break;
                            case 'list':
                                $items = implode('</li><li>', $block['data']['items']);
                                $html .= '<ul><li>' . $items . '</li></ul>';
                                break;
                            case 'table':
                                $html .= '<table>';
                                foreach ($block['data']['content'] as $row) {
                                    $html .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
                                }
                                $html .= '</table>';
                                break;
                            case 'image':
                                $html .= '<img src="' . $block['data']['url'] . '" alt="' . $block['data']['caption'] . '">';
                                break;
                        }
                    }

                    $data[ $section->getPosition() ] = [
                        "name" => $section->getName(),
                        "value" => $html ,
                        "isWide" => $section->isWide() ,
                        "isTitleVisible" => $section->isTitleVisible() ,
                    ];

                } catch (\Exception  $e) {
                }
            }
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => $data,
        ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    #[Route('/get-photos', name:'get_photos', methods: ["GET"])]
    public function getPhotos(): JsonResponse
    {
        $photosRepo = $this->em->getRepository(Photos::class);
        $photos = $photosRepo->findAll();

        $data  = [];

        foreach( $photos as $photo ) {
            $data[ $photo->getId() ] = [
                "name" => $photo->getName(),
                "fileName" => $photo->getSafeFileName(),
            ];
        }

        return new JsonResponse([
            'status' => 'success',
            'response' => $data,
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
        $adminsRepo = $this->em->getRepository(Admin::class);

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
