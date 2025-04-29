<?php

namespace App\Controller;

use App\Entity\Entretien;
use App\Form\EntretienType;
use App\Repository\EntretienRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/entretien')]
final class EntretienController extends AbstractController
{
    #[Route('/', name: 'app_entretien_index', methods: ['GET'])]
    public function index(EntretienRepository $entretienRepository): Response
    {
        return $this->render('entretien/index.html.twig', [
            'entretiens' => $entretienRepository->findBy([], ['datePlanifiee' => 'ASC']),
            'total_entretiens' => $entretienRepository->count([]),
            'entretiens_termines' => $entretienRepository->count(['status' => 'Terminé']),
            'entretiens_attente' => $entretienRepository->count(['status' => 'En attente']),
        ]);
    }

    #[Route('/calendar', name: 'app_entretien_calendar', methods: ['GET'])]
    public function calendar(EntretienRepository $entretienRepository): JsonResponse
    {
        $entretiens = $entretienRepository->findAll();
        $events = [];

        foreach ($entretiens as $entretien) {
            $events[] = [
                'title' => $entretien->getTypeEntretien(),
                'start' => $entretien->getDatePlanifiee()->format('Y-m-d'),
                'color' => $this->getStatusColor($entretien->getStatus()),
                'extendedProps' => [
                    'id' => $entretien->getId(),
                    'status' => $entretien->getStatus(),
                    'cout' => $entretien->getCout()
                ]
            ];
        }

        return new JsonResponse($events);
    }

    #[Route('/new', name: 'app_entretien_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entretien = new Entretien();
        $form = $this->createForm(EntretienType::class, $entretien);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entretien);
            $entityManager->flush();

            $this->addFlash('success', 'Entretien créé avec succès');
            return $this->redirectToRoute('app_entretien_index');
        }

        return $this->render('entretien/new.html.twig', [
            'entretien' => $entretien,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_entretien_show', methods: ['GET'])]
    public function show(Entretien $entretien): Response
    {
        return $this->render('entretien/show.html.twig', [
            'entretien' => $entretien,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_entretien_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entretien $entretien, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntretienType::class, $entretien);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Entretien modifié avec succès');
            return $this->redirectToRoute('app_entretien_index');
        }

        return $this->render('entretien/edit.html.twig', [
            'entretien' => $entretien,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_entretien_delete', methods: ['POST'])]
    public function delete(Request $request, Entretien $entretien, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entretien->getId(), $request->request->get('_token'))) {
            $entityManager->remove($entretien);
            $entityManager->flush();
            $this->addFlash('success', 'Entretien supprimé avec succès');
        }

        return $this->redirectToRoute('app_entretien_index');
    }

    private function getStatusColor(string $status): string
    {
        switch ($status) {
            case 'Terminé':
                return '#28a745'; // Vert
            case 'En attente':
                return '#ffc107'; // Jaune
            case 'Annulé':
                return '#dc3545'; // Rouge
            default:
                return '#007bff'; // Bleu
        }
    }
}