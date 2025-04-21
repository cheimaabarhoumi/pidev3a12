<?php

namespace App\Controller\back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AnnonceRepository;
use App\Entity\Annonce;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\VoitureRepository;

use App\Entity\Voiture;

class GestionAnnonceController extends AbstractController
{


    #[Route('/admin/gestion-annonce', name: 'gestion_annonce')]
    public function accueilAdminannonce(AnnonceRepository $annonceRepository): Response
    {
        $annonces = $annonceRepository->findAll(); 
        return $this->render('back/annonce/gestion_annonce.html.twig', [
            'annonces' => $annonces, 
        ]);
        
    }
    #[Route('/admin/gestion-annonce/delete/{id}', name: 'delete_annonce')]
    public function deleteAnnonce(EntityManagerInterface $entityManager, Annonce $annonce): Response
    {
        // Supprimer l'annonce
        $entityManager->remove($annonce);
        $entityManager->flush();

        // Rediriger vers la liste des annonces
        return $this->redirectToRoute('gestion_annonce');
    }
    #[Route('/admin/gestion-voiture', name: 'gestion_voiture')]
    public function accueilAdmin(VoitureRepository $voitureRepository): Response
    {
        $voitures = $voitureRepository->findAll();
        return $this->render('back/voiture/gestion_voiture.html.twig', [
            'voitures' => $voitures,
        ]);
    }
    
    #[Route('/admin/gestion-annonce/valider/{id}', name: 'valider_annonce')]
    public function validerAnnonce(EntityManagerInterface $entityManager, Annonce $annonce): Response
    {
        $annonce->setValider(true);
        $entityManager->flush();
    
        return $this->redirectToRoute('gestion_annonce');
    }




}
