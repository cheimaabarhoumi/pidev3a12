<?php

namespace App\Controller;

use App\Entity\Sponsoring;
use App\Form\SponsoringType;
use App\Repository\SponsoringRepository;
use App\Service\ReportingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;



#[Route('/sponsoring')]
class SponsoringController extends AbstractController
{
    #[Route('/', name: 'app_sponsoring_index', methods: ['GET'])]
    public function index(
        Request $request,
        SponsoringRepository $sponsoringRepository,
        PaginatorInterface $paginator,
        ReportingService $reportingService
    ): Response {
        $searchTerm = $request->query->get('search');
        $etat = $request->query->get('etat');
        $queryBuilder = $sponsoringRepository->search($searchTerm, $etat);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        $stats = $reportingService->getMonthlyStats();

        return $this->render('sponsoring/index.html.twig', [
            'pagination' => $pagination,
            'stats' => $stats,
            'searchTerm' => $searchTerm,
            'currentEtat' => $etat
        ]);
    }

    #[Route('/dashboard', name: 'app_sponsoring_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('sponsoring/dashboard.html.twig');
    }

    #[Route('/new', name: 'app_sponsoring_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sponsoring = new Sponsoring();
        $form = $this->createForm(SponsoringType::class, $sponsoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sponsoring);
            $entityManager->flush();

            $this->addFlash('success', 'Le sponsoring a été créé avec succès.');
            return $this->redirectToRoute('app_sponsoring_index');
        }

        return $this->render('sponsoring/new.html.twig', [
            'sponsoring' => $sponsoring,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_sponsoring_show', methods: ['GET'])]
    public function show(Sponsoring $sponsoring): Response
    {
        return $this->render('sponsoring/show.html.twig', [
            'sponsoring' => $sponsoring,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sponsoring_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sponsoring $sponsoring, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SponsoringType::class, $sponsoring);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le sponsoring a été modifié avec succès.');
            return $this->redirectToRoute('app_sponsoring_index');
        }

        return $this->render('sponsoring/edit.html.twig', [
            'sponsoring' => $sponsoring,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_sponsoring_delete', methods: ['POST'])]
    public function delete(Request $request, Sponsoring $sponsoring, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sponsoring->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sponsoring);
            $entityManager->flush();
            $this->addFlash('success', 'Le sponsoring a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_sponsoring_index');
    }

    #[Route('/{id}/change-status', name: 'app_sponsoring_change_status', methods: ['POST'])]
    public function changeStatus(Request $request, Sponsoring $sponsoring, EntityManagerInterface $entityManager): Response
    {
        $newStatus = $request->request->get('status');
        if (in_array($newStatus, ['Actif', 'Terminé', 'Annulé'])) {
            $sponsoring->setEtat($newStatus);
            $entityManager->flush();
            $this->addFlash('success', 'Le statut du sponsoring a été mis à jour.');
        }

        return $this->redirectToRoute('app_sponsoring_show', ['id' => $sponsoring->getId()]);
    }


}