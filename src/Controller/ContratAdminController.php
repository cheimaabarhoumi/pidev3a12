<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class ContratAdminController extends AbstractController
{
    private $contratRepository;
    private $entityManager;

    public function __construct(ContratRepository $contratRepository, EntityManagerInterface $entityManager)
    {
        $this->contratRepository = $contratRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'dashboard')]
    public function dashboard(Request $request): Response
    {
        // Get the search query from GET parameters (if any)
        $searchTerm = $request->query->get('q', '');
        
        if (!empty($searchTerm)) {
            // Use the custom search method if a search term is provided
            $recentContracts = $this->contratRepository->searchContracts($searchTerm);
        } else {
            // Otherwise, get all recent contracts (or limit as needed)
            $recentContracts = $this->contratRepository->findBy([], ['idContrat' => 'DESC']);
        }
        
        // Prepare statistics
        $stats = [
            'contracts_count' => $this->contratRepository->count([]),
            'active_contracts'  => $this->contratRepository->count(['statut' => 'Actif']),
            'recent_contracts'  => $recentContracts,
            'total_amount'      => $this->contratRepository->getTotalAmount(),
            'search_term'       => $searchTerm, // Pass search term to the view
        ];
    
        return $this->render('admin/dashboard/index.html.twig', $stats);
    }

    #[Route('/contrat', name: 'contrat_index')]
    public function index(): Response
    {
        return $this->render('admin/contrat/index.html.twig', [
            'contrats' => $this->contratRepository->findAll(),
        ]);
    }

    #[Route('/contrat/new', name: 'contrat_new')]
    public function new(Request $request): Response
    {
        $contrat = new Contrat();
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($contrat);
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_contrat_index');
        }

        return $this->render('admin/contrat/new.html.twig', [
            'contrat' => $contrat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/contrat/{id}', name: 'contrat_show')]
    public function show(Contrat $contrat): Response
    {
        return $this->render('admin/contrat/show.html.twig', [
            'contrat' => $contrat,
        ]);
    }

    #[Route('/contrat/{id}/edit', name: 'contrat_edit')]
    public function edit(Request $request, Contrat $contrat): Response
    {
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_contrat_index');
        }

        return $this->render('admin/contrat/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/contrat/{id}/delete', name: 'contrat_delete', methods: ['POST'])]
    public function delete(Request $request, Contrat $contrat): Response
    {
        if ($this->isCsrfTokenValid('delete' . $contrat->getIdContrat(), $request->getPayload()->getString('_token'))) {
            $this->entityManager->remove($contrat);
            $this->entityManager->flush();
        }
    
        return $this->redirectToRoute('admin_contrat_index');
    }
    
} 