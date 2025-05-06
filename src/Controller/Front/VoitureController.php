<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\VoitureRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Voiture;
use App\Form\VoitureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;



class VoitureController extends AbstractController
{
    // Liste des voitures
    #[Route('/voitures', name: 'app_voiture')]
    public function index(VoitureRepository $voitureRepository): Response
    {
        $voitures = $voitureRepository->findAll();
        return $this->render('front/voiture/voitures.html.twig', [
            'voitures' => $voitures
        ]);
    }

   

    #[Route('/voitures/add', name: 'voiture_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $voiture = new Voiture();
    $form = $this->createForm(VoitureType::class, $voiture);
    $form->handleRequest($request);
    

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();
        dump($imageFile);
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
            }

            $voiture->setImage($newFilename);
        }
        

        $em->persist($voiture);
        $em->flush();

        return $this->redirectToRoute('app_voiture');
    }

    return $this->render('front/voiture/add.html.twig', [
        'form' => $form->createView(),
    ]);
}

    // Détail d'une voiture
    #[Route('/voitures/{id}', name: 'front_voiture_details', methods: ['GET'])]
    public function show(Voiture $voiture): Response
    {
        return $this->render('front/voiture/details.html.twig', [
            'voiture' => $voiture
        ]);
    }

    #[Route('/voitures/{id}/edit', name: 'edit_voiture', methods: ['GET', 'POST'])]
    public function editVoiture(Request $request, Voiture $voiture, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VoitureType::class, $voiture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Voiture modifiée avec succès.');

            return $this->redirectToRoute('app_voiture');
        }

        return $this->renderForm('front/voiture/edit.html.twig', [
            'voiture' => $voiture,
            'form' => $form,
        ]);
    }

    #[Route('/voitures/{id}/delete', name: 'voiture_delete', methods: ['POST'])]
    public function delete(Request $request, Voiture $voiture, EntityManagerInterface $entityManager): Response
    {
        // Supprimer la voiture (les annonces liées seront supprimées automatiquement grâce à onDelete: CASCADE)
        $entityManager->remove($voiture);
        $entityManager->flush();
    
        $this->addFlash('success', 'La voiture et ses annonces associées ont été supprimées avec succès.');
    
        return $this->redirectToRoute('app_voiture', [], Response::HTTP_SEE_OTHER);
    }

}
