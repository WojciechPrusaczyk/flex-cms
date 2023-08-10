<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
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
                'check_db_connection' => $requestUri."db-connection-test",
                'register' => $requestUri."register?password={password}&username={username}",
                'get-user' => $requestUri."get-user",
                'logout' => $requestUri."logout",
                'dashboard-get-settings' => $requestUri."dashboard/get-settings",
                'dashboard-gallery-upload-photo' => $requestUri."dashboard/gallery/upload-photo",
                'dashboard-gallery-get-photos' => $requestUri."dashboard/gallery/get-photos?page={page}&quantity={quantity}",
                'dashboard-gallery-delete-photo' => $requestUri."dashboard/gallery/delete-photo?id={id}",
            ]);
        }
        return $this->json([
            'status' => 'error',
            'response' => 'Not authenticated',
        ]);
    }

    #[Route('/db-connection-test', name: '_check_db_connection', methods: ["GET", "HEAD"])]
    public function checkDbConnection(EntityManagerInterface $em, Security $security): JsonResponse
    {
        if (null != $security->getUser())
        {
            $connected = false;
            try {
                $em->getConnection()->connect();
                $connected = $em->getConnection()->isConnected();
            } catch (Exception) {

            }

            return $this->json([
                'message' => ($connected!=null)?"Database is connected":"Error! There is something wrong with database.",
            ]);
        }
        return $this->json([
            'status' => 'error',
            'response' => 'Not authenticated',
        ]);
    }

    #[Route('/register', name: '_register', methods: ["POST", "GET"])]
    public function createAdmin(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em, Security $security): JsonResponse
    {
        if (null == $security->getUser() || [] == $security->getUser())
        {
            return $this->json([
                'status' => 'error',
                'response' => 'Not authenticated',
            ]);
        }

        // pobieranie odpowednich przesłanych danych do utworzenia admina
        $requestedPassword = $request->get('password');
        $requestedUsername = $request->get('username');
        $dateTimeNow = new \DateTime('@'.strtotime('now'));

        $errors = [
            'password' => $this->validatePassword($requestedPassword),
            'username' => $this->validateUsername($requestedUsername)
        ];

        // walidacja
        if ( count($errors['password']) > 0 || count($errors['username']) > 0 )
        {
            return $this->json([
                'status' => 'error',
                'response' => 'Wystąpił błąd w podanych danych do rejestracji.',
                'errors' => $errors,
            ]);
        } else {
            try {
                // tworzenie użytkownika
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
            } catch (\Exception) {
                return $this->json([
                    'status' => 'error',
                    'response' => 'Błąd 500! Wsytąpił krytyczny błąd po stronie serwera.',
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'response' => 'Administrator został utworzony',
        ]);
    }
    #[Route('/get-user', name: '_get_user', methods: ["GET"])]
    public function getCurrentUser(Security $security, EntityManagerInterface $em): JsonResponse
    {
        if (null != $security->getUser())
        {
            $user = $security->getUser();
            return $this->json([
                'status' => 'success',
                'response' => [
                    'user' => [
                        'username' => $user->getUsername(),
                        'accountCreated' => $user->getAccountCreated(),
                        'lastLoginDate' => $user->getLastLoginDate(),
                        'active' => $user->isActive(),
                    ]
                ],
            ]);
        } else {
            return $this->json([
                'status' => 'error',
                'response' => 'Not authenticated',
            ]);
        }
    }

    #[Route('/logout', name: 'logout', methods: ["POST", "GET"])]
    public function logout(Security $security): JsonResponse
    {
        if (null != $security->getUser())
        {
            $security->logout(false);

            return $this->json([
                'status' => 'success',
                'response' => 'Wylogowano użytkownika.',
            ]);
        } else {
            return $this->json([
                'status' => 'error',
                'response' => 'Wystąpił błąd podczas wylogowania.',
            ]);
        }
    }

    #[Route('/dashboard/get-settings', name: 'dashboard_get_settings', methods: ["GET"])]
    public function getDashboardSettings(Security $security, Request $request): JsonResponse
    {
        $requestUri = $request->getBaseUrl();

        return $this->json([
            'status' => 'success',
            'response' => [
                ["name" => "Sekcje", "href" => $requestUri."sections", "icon" => "sections.svg"],
                ["name" => "Galeria", "href" => $requestUri."gallery", "icon" => "gallery.svg"],
                ["name" => "Kolory", "href" => $requestUri."colors", "icon" => "colors.svg"],
                ["name" => "Style", "href" => $requestUri."stylesheets", "icon" => "stylesheets.svg"],
                ["name" => "Ustawienia", "href" => $requestUri."settings", "icon" => "settings.svg"],
                ["name" => "Skrypty", "href" => $requestUri."scripts", "icon" => "scripts.svg"],
            ]
        ]);
    }


    // function used to validate usernames
    private function validateUsername(string $providedUsername): array
    {
        /*
         * Password conditions:
         * at least 3 characters
         * unique
         */

        $adminsRepo = $this->entityManager->getRepository(Admin::class);
        $errors = [];

        if ( strlen($providedUsername) < 3 )
        {
            array_push($errors, "Nazwa użytkownika jest za krótka.");
        }

        if ( $adminsRepo->findOneBy([ 'username' => $providedUsername ]) != null )
        {
            array_push($errors, "Nazwa użytkownika jest już zajęta.");
        }

        // function returns error array
        // if there are no errors, function returns empty array
        if ( count($errors) > 0 )
        {
            return $errors;
        } else return [];
    }

    // function used to validate passwords
    private function validatePassword(string $providedPassword): array
    {
        /*
         * Password conditions:
         * at least 8 characters
         * at least 1 number
         * at least 1 special character
         * at least 1 big letter
         */

        $errors = [];

        if ( strlen($providedPassword) < 8 )
        {
            array_push($errors, "Hasło jest zbyt krótkie.");
        }
        if ( !preg_match('/(?=\S*[A-Z])/', $providedPassword) )
        {
            array_push($errors, "Hasło musi zawierać co najmniej jedną wielką literę.");
        }
        if ( !preg_match('~[0-9]+~', $providedPassword) )
        {
            array_push($errors, "Hasło musi zawierać co najmniej jedną cyfrę.");
        }
        if ( !preg_match('/[\'^£!%&*()}{@#~?><>,|=_+-]/', $providedPassword) )
        {
            array_push($errors, "Hasło musi zawierać co najmniej jeden znak specjalny.");
        }

        // function returns error array
        // if there are no errors, function returns empty array
        if ( count($errors) > 0 )
        {
            return $errors;
        } else return [];
    }
}
