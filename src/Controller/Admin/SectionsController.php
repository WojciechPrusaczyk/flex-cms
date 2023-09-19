<?php

namespace App\Controller\Admin;

use App\Entity\Sections;
use App\Entity\StyleSheets;
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

    #[Route('/admin-api/dashboard/sections/get-sections', name: 'admin_api_dashboard_sections_get_stylesheets', methods: ["GET"])]
    public function getSections(EntityManagerInterface $entityManager, LoggerInterface $logger ): JsonResponse
    {
        try {
            $sectionsRepo = $entityManager->getRepository(Sections::class);
            $allSections = $sectionsRepo->findAll();

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


    #[Route('/dashboard/sections/delete', name: 'dashboard_sections_delete', methods: ["GET"])]
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
}