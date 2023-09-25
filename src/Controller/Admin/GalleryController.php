<?php

namespace App\Controller\Admin;

use App\Entity\Photos;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
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
    public function uploadPhoto(Security $security, Request $request, EntityManagerInterface $em, LoggerInterface $logger): JsonResponse
    {
        try {
            if ($request->files->has('file')) {
                $uploadedFile = $request->files->get('file');
                $filename = $request->get('filename');
                $requestUsername = $request->get('username');

                if (filesize($uploadedFile) > 5000000 ) {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => 'The uploaded file is too large!',
                    ], 500);
                }

                // You can now use $uploadedFile to handle the uploaded file

                // For example, you can save it on the server:
                $extension = $uploadedFile->guessExtension();
                $safeFileName = md5(uniqid()) . '.' . $extension;
                $validFileTypes = [
                    "gif", "jpg", "png", "svg"
                ];
                $clearFileName = str_replace(".", "", str_replace($validFileTypes, "", $filename));
                $dateTimeNow = new \DateTime('@'.strtotime('now'));
                $dateTimeNow->setTimezone(new DateTimeZone('Europe/Warsaw'));

                $uploadedFile->move(
                    $this->getParameter('photos_upload_directory'), // Path to the directory where uploaded files will be stored
                    $safeFileName
                );

                // Additional conditions for file uploads
                if (file_exists($this->getParameter('photos_upload_directory') . "/" . $safeFileName) && null != $security->getUser() && $security->getUser()->getUsername() == $requestUsername && in_array($extension, $validFileTypes)) {
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
                    } catch (\Exception) { }

                    return new JsonResponse([
                        'status' => 'success',
                        'response' => 'The file has been successfully added to the server.',
                        'filename' => $filename,
                        'id' => $photoEntity->getId(),
                    ], 200);
                } else {
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => 'An error occurred while saving the file.',
                    ], 500);
                }
            } else {
                return new JsonResponse([
                    'status' => 'error',
                    'response' => 'No attached file.',
                ], 400);
            }
        } catch (\Exception $e) {
            // Log the error
            $logger->error('An error occurred: ' . $e->getMessage());

            // Handle the error and return an appropriate JSON response
            return new JsonResponse([
                'status' => 'error',
                'response' => 'An error occurred while processing the file upload.',
            ], 500);
        }
    }

    #[Route('/admin-api/dashboard/gallery/get-photos', name: 'admin_api_dashboard_gallery_get_photos', methods: ["POST", "GET"])]
    public function getPhotos(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator, LoggerInterface $logger): JsonResponse
    {
        try {
            // Get the repository for Photos entities
            $photosRepo = $em->getRepository(Photos::class);

            // Get the query for fetching photos
            $query = $photosRepo->getPhotosQuery();

            // Get the requested quantity and page from the request
            $requestedQuantity = $request->get('quantity');
            $requestedPage = $request->get('page');

            // Use the paginator to paginate the results
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', $requestedPage), /* page number */
                $requestedQuantity /* limit per page */
            );

            // Get important pagination information
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
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        } catch (\Exception $e) {
            // Log the error
            $logger->error('An error occurred: ' . $e->getMessage());

            // Handle the error and return an appropriate JSON response
            return new JsonResponse([
                'status' => 'error',
                'response' => 'An error occurred while fetching photos.',
            ], 500);
        }
    }

    private function paginationToFriendlyJson(PaginationInterface $pagination)
    {
        // Changing pagination object to JSON which is easy to handle in Frontend
        $paginationItems = $pagination->getItems();
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
    public function deletePhoto(Request $request, EntityManagerInterface $em, Filesystem $filesystem, LoggerInterface $logger)
    {
        // Get the photo ID from the request
        $photoId = $request->get('id');

        // Check if the provided photoId is a valid positive number
        if (!is_nan($photoId) && $photoId > 0) {
            try {
                // Get the repository for Photos entities
                $photosRepo = $em->getRepository(Photos::class);

                // Find the photo entity based on the provided ID
                $photo = $photosRepo->findOneBy(["id" => $photoId]);

                if ($photo) {
                    // Get the file name associated with the photo
                    $fileName = $photo->getSafeFileName();

                    // Remove the photo entity from the database
                    $em->remove($photo);
                    $em->flush();

                    // Check if the physical file exists and delete it
                    if (file_exists($this->getParameter('photos_upload_directory') . "/" . $fileName)) {
                        $filesystem->remove($this->getParameter('photos_upload_directory') . "/" . $fileName);

                        // Return a success JSON response
                        return new JsonResponse([
                            'status' => 'success',
                            'response' => 'The photo has been deleted!',
                        ], 200);
                    }
                } else {
                    // Return an error JSON response if the photo entity was not found
                    return new JsonResponse([
                        'status' => 'error',
                        'response' => 'The specified photo was not found.',
                    ], 400);
                }
            } catch (EntityNotFoundException $e) {
                // Log the error
                $logger->error('An error occurred: ' . $e->getMessage());

                // Return an error JSON response if the photo entity was not found
                return new JsonResponse([
                    'status' => 'error',
                    'response' => 'The specified photo was not found.',
                ], 400);
            } catch (\Exception $e) {
                // Log the error
                $logger->error('An error occurred: ' . $e->getMessage());

                // Handle any other exceptions here, and return an appropriate JSON response
                return new JsonResponse([
                    'status' => 'error',
                    'response' => 'An error occurred while deleting the photo.',
                ], 500);
            }
        }
    }
}
