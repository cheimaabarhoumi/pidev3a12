<?php

namespace App\Controller\Front;

use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\VoitureRepository;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // <-- Import correct
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnnonceController extends AbstractController
{
    #[Route('/annonces', name: 'front_annonces')]
    public function index(AnnonceRepository $annonceRepository): Response
    {
        $annonces = $annonceRepository->findBy(['valider' => true]);
    
        return $this->render('front/annonce/annonces.html.twig', [
            'annonces' => $annonces, 
        ]);
    }

    #[Route('/annonces/{id}', name: 'front_annonce_details')]
    public function show($id,AnnonceRepository $annonceRepository): Response
    {   
        $annonce = $annonceRepository->find($id);
        return $this->render('front/annonce/annonce_details.html.twig', [
            'annonce' => $annonce,
        ]);
    }
    #[Route('/annonces/create/{voitureId}', name: 'create_annonce')]
    public function createAnnonce(int $voitureId, Request $request, VoitureRepository $voitureRepository, EntityManagerInterface $em): Response
    {
        // Récupère la voiture correspondante (tu peux ajouter une gestion d'erreur si la voiture n'existe pas)
        $voiture = $voitureRepository->find($voitureId);

        if (!$voiture) {
            throw $this->createNotFoundException('La voiture n\'existe pas.');
        }

        // Créer une nouvelle instance d'annonce
        $annonce = new Annonce();
        $annonce->setVoiture($voiture);

        // Créer le formulaire
        $form = $this->createForm(AnnonceType::class, $annonce, [
            'voitures' => [$voiture] // Passer la voiture comme option dans le formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer l'annonce dans la base de données
            $em->persist($annonce);
            $em->flush();

            $this->addFlash('success', 'Annonce créée avec succès.');

            return $this->redirectToRoute('front_annonces');
        }

        return $this->render('front/annonce/create.html.twig', [
            'form' => $form->createView(),
            'voiture' => $voiture
        ]);
    }

#[Route('/annonces/edit/{id}', name: 'edit_annonce')]
public function editAnnonce(int $id, Request $request, AnnonceRepository $annonceRepository, EntityManagerInterface $em): Response
{
    $annonce = $annonceRepository->find($id);
    

    if (!$annonce) {
        throw $this->createNotFoundException('L\'annonce n\'existe pas.');
    }

    $form = $this->createForm(AnnonceType::class, $annonce, [
    'voitures' => [$annonce->getVoiture()]
]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($annonce);
        $em->flush();

        $this->addFlash('success', 'Annonce modifiée avec succès.');

        return $this->redirectToRoute('front_annonces');
    }

    return $this->render('front/annonce/edit.html.twig', [
        'form' => $form->createView(),
        'voiture' => $annonce->getVoiture() 
    ]);
}


#[Route('/annonces/{id}/delete', name: 'annonce_delete', methods: ['POST'])]
public function deleteAnnonce(Request $request, Annonce $annonce, EntityManagerInterface $entityManager): Response
{
    
        $entityManager->remove($annonce);
        $entityManager->flush();
        
        $this->addFlash('success', 'Annonce supprimée avec succès.');
    

    return $this->redirectToRoute('front_annonces', [], Response::HTTP_SEE_OTHER);
}



#[Route('/admin/gestion-annonce/valider/{id}', name: 'valider_annonce')]
public function validerAnnonce(EntityManagerInterface $entityManager, Annonce $annonce): Response
{
    $annonce->setValider(true);
    $entityManager->flush();

    return $this->redirectToRoute('gestion_annonce');
}

}
