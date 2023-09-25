<?php

namespace App\Controller\Admin;

use App\Entity\Photos;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GalleryController extends AbstractController
{
    #[Route('/dashboard/gallery', name: 'dashboard_gallery')]
    public function index(): Response
    {
        return $this->render('gallery/index.html.twig', [
            'controller_name' => 'GalleryController',
        ]);
    }
    #[Route('/admin-api/dashboard/gallery/upload-photo', name: 'admin_api_dashboard_gallery_upload_photo', methods: ["POST", "GET"])]
    public function uploadPhoto(Security $security, Request $request, EntityManagerInterface $em): JsonResponse
    {
        if ($request->files->has('file')) {
            $uploadedFile = $request->files->get('file');
            $filename = $request->get('filename');
            $requestUsername = $request->get('username');
            if (filesize($uploadedFile) > 5000000 )
            {
                return new JsonResponse([
                    'status' => 'error',
                    'response' => 'Przesłany plik jest za duży!',
                ], 500);
            }

            // Możesz teraz wykorzystać $uploadedFile do obsługi przesłanego pliku

            // Przykładowo, możesz go zapisać na serwerze:
            try {
                $extension = $uploadedFile->guessExtension();
                $safeFileName = md5(uniqid()) . '.' . $extension;
                $validFileTypes = [
                    "gif", "jpg", "png", "svg"
                ];
                $clearFileName = str_replace(".", "", str_replace($validFileTypes, "", $filename));
                $dateTimeNow = new \DateTime('@'.strtotime('now'));
                $dateTimeNow->setTimezone(new DateTimeZone('Europe/Warsaw'));

                $uploadedFile->move(
                    $this->getParameter('photos_upload_directory'), // Ścieżka do katalogu, gdzie będą przechowywane przesłane pliki
                    $safeFileName
                );

                // dodatkowe warunki sprawdzan przy dodawaniu plików
                if( file_exists($this->getParameter('photos_upload_directory')."/".$safeFileName) && null != $security->getUser() && $security->getUser()->getUsername() == $requestUsername && in_array($extension, $validFileTypes) )
                {
                    try {
                        $photoEntity = new Photos();
                        $photoEntity->setSafeFilename($safeFileName);
                        $photoEntity->setFilename($filename);
                        $photoEntity->setFileType($extension);
                        $photoEntity->setName($clearFileName);
                        $photoEntity->setAddedBY($security->getUser());
                        $photoEntity->setAddedDatetime($dateTimeNow);
                        $em->persist($photoEntity);
                        $em->flush();
                    } catch (\Exception) {  }

                    return new JsonResponse([
                        'status' => 'success',
                        'response' => 'Plik został pomyślnie dodany na serwer.',
                        'filename' => $filename,
                        'id' => $photoEntity->getId(),
                    ], 200);
                } else {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => 'Wystąpił błąd podczas zapisu pliku.',
                    ], 500);
                }
            } catch (FileException $e) {
                return new JsonResponse([
                    'status' => 'error',
                    'response' => 'Wystąpił błąd podczas zapisu pliku.',
                ], 500);
            }
        } else {
            return new JsonResponse([
                'status' => 'error',
                'response' => 'Brak załączonego pliku.',
            ], 400);
        }
    }

    #[Route('/admin-api/dashboard/gallery/get-photos', name: 'admin_api_dashboard_gallery_get_photos', methods: ["POST", "GET"])]
    public function getPhotos(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): JsonResponse
    {
        // wydobycie zapytania do bazy
        $photosRepo = $em->getRepository(Photos::class);
        $query = $photosRepo->getPhotosQuery();

        // pobranie odpowiednich wartości
        $requestedQuantity = $request->get('quantity');
        $requestedPage = $request->get('page');

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', $requestedPage), /*page number*/
            $requestedQuantity /*limit per page*/
        );

        // ważne informacje paginacji
        $totalItems = $pagination->getTotalItemCount();
        $currentPage = $pagination->getCurrentPageNumber();
        $pagesCount = ceil($totalItems / $pagination->getItemNumberPerPage());

        return new JsonResponse([
            'status' => 'success',
            'response' => [
                "items" => $this->paginationToFriendlyJson($pagination),
                "totalItems" => $totalItems,
                "currentPage" => $currentPage,
                "pagesCount" => $pagesCount,
            ],
        ], 200, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
    }

    private function paginationToFriendlyJson(PaginationInterface $pagination)
    {
        $paginationItems = $pagination->getItems();
//        dd($paginationItems);
        $items = [];

        foreach ($paginationItems as $paginationItem)
        {
             $itemToAdd = [ $paginationItem->getId() => [
                "name" => $paginationItem->getName(),
                "fileType" => $paginationItem->getFileType(),
                "safeFileName" => $paginationItem->getSafeFileName(),
                "addedBy" => $paginationItem->getAddedBy()->getUserName(),
                "addedDatetime" => $paginationItem->getAddedDateTime(),
            ]];
            $items[] = $itemToAdd;
        }

        return $items;
    }

    #[Route('/admin-api/dashboard/gallery/delete-photo', name: 'admin_api_dashboard_gallery_delete_photo', methods: ["GET"])]
    public function deletePhoto(Request $request, EntityManagerInterface $em, Filesystem $filesystem)
    {
        $photoId = $request->get('id');

        if( !is_nan($photoId) && $photoId > 0)
        {
            try {
                $photosRepo = $em->getRepository(Photos::class);
                $photo = $photosRepo->findOneBy( [ "id" => $photoId ] );

                if ( $photo )
                {
                    $fileName = $photo->getSafeFileName();

                    $em->remove($photo);
                    $em->flush();

                    if ( file_exists($this->getParameter('photos_upload_directory')."/".$fileName) )
                    {
                        $filesystem->remove($this->getParameter('photos_upload_directory')."/".$fileName);

                        return new JsonResponse([
                            'status' => 'success',
                            'response' => 'Zdjęcie zostało usunięte!',
                        ], 200);
                    }
                } else {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => 'Nie znaleziono podanego zdjęcia.',
                    ], 400);
                }


            } catch (EntityNotFoundException $e)
            {
                return new JsonResponse([
                    'status' => 'error',
                    'response' => 'Nie znaleziono podanego zdjęcia.',
                ], 400);
            }
        }
    }
}
