<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Contrat;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contrat')]
final class ContratController extends AbstractController
{
    #[Route(name: 'app_contrat_index', methods: ['GET'])]
    public function index(ContratRepository $contratRepository): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();
        
        // Fetch contracts that belong only to this user
        $contrats = $contratRepository->findBy(['utilisateur' => $user]);
    
        return $this->render('contrat/index.html.twig', [
            'contrats' => $contrats,
        ]);
    }

    #[Route('/new', name: 'app_contrat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contrat = new Contrat();
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the logged-in user as the owner of the contract
            $contrat->setUtilisateur($this->getUser());
        
            $entityManager->persist($contrat);
            $entityManager->flush();
        
            return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat/new.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{idContrat}', name: 'app_contrat_show', methods: ['GET'])]
    public function show(Contrat $contrat): Response
    {
        if ($contrat->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à ce contrat.");
        }
        // Check if the user is the owner of the contract
        
        return $this->render('contrat/show.html.twig', [
            'contrat' => $contrat,
            'contratId' => $contrat->getIdContrat(),

        ]);
    }

    #[Route('/{idContrat}/edit', name: 'app_contrat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();

    // Restrict access: only owner can edit
        if ($contrat->getUtilisateur() !== $currentUser) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier ce contrat.");
        }

        $form = $this->createForm(ContratType::class, $contrat, [
            'is_user_edit' => !$this->isGranted('ROLE_ADMIN'), // true if not admin
        ]);        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                // Re-assign utilisateur in case it's lost
                $contrat->setUtilisateur($currentUser);
                $entityManager->flush();
                

            return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{idContrat}', name: 'app_contrat_delete', methods: ['POST'])]
    public function delete(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        if ($contrat->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Suppression non autorisée.");
        }

        if ($this->isCsrfTokenValid('delete'.$contrat->getIdContrat(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contrat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/pdf', name: 'admin_contrat_pdf', methods: ['GET'])]
public function generatePdf(Contrat $contrat): Response
{
    $html = $this->renderView('admin/contrat/pdf.html.twig', [
        'contrat' => $contrat,
    ]);

    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');

    $dompdf = new Dompdf($pdfOptions);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrat_'.$contrat->getReference().'.pdf"',
        ]
    );
}
}
