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
use Dompdf\Dompdf;
use Endroid\QrCode\QrCode; // <-- Ajout de l'import correct pour QrCode
use Endroid\QrCode\Writer\PngWriter; 
use Symfony\Component\Mailer\MailerInterface; // Import correct pour MailerInterface
use Symfony\Component\Mime\Email;
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
    public function show($id, AnnonceRepository $annonceRepository): Response
    {   
        // Récupérer l'annonce depuis la base de données
        $annonce = $annonceRepository->find($id);

        // Si l'annonce n'existe pas, afficher une erreur
        if (!$annonce) {
            throw $this->createNotFoundException('L\'annonce n\'existe pas.');
        }

        // Construire une chaîne de texte avec les informations de l'annonce
        $annonceInfo = "Titre: " . $annonce->getTitre() . "\n";
        $annonceInfo .= "Description: " . $annonce->getDescription() . "\n";
        $annonceInfo .= "Marque: " . $annonce->getVoiture()->getMarque() . " " . $annonce->getVoiture()->getModele() . "\n";
        $annonceInfo .= "Année: " . $annonce->getVoiture()->getAnnee() . "\n";
        $annonceInfo .= "Kilométrage: " . $annonce->getVoiture()->getKilometrage() . " km\n";
        $annonceInfo .= "Prix: " . ($annonce->getVoiture()->getPrix() ? $annonce->getVoiture()->getPrix() . ' TND' : 'Non spécifié') . "\n";

        // Générer un QR code avec les informations de l'annonce
        $qrCode = new QrCode($annonceInfo);
        $writer = new PngWriter();

        // Utiliser la méthode write() pour obtenir l'image en base64
        $result = $writer->write($qrCode);

        // Utiliser getDataUri() pour obtenir le QR code encodé en base64
        $qrCodeBase64 = $result->getDataUri();

        // Renvoyer la vue avec l'annonce et le QR code
        return $this->render('front/annonce/annonce_details.html.twig', [
            'annonce' => $annonce,
            'qrCode' => $qrCodeBase64,
        ]);
    }








    
    #[Route('/annonces/create/{voitureId}', name: 'create_annonce')]
    public function createAnnonce(int $voitureId, Request $request, VoitureRepository $voitureRepository, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        // Récupère la voiture correspondante
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
    
            // Envoi de l'email de notification
            $email = (new Email())
                ->from('cheymabarhoumi82@gmail.com')
                ->to('mejriachrefff@gmail.com') 
                ->subject('Nouvelle annonce créée')
                ->html(
                    '<p>Une nouvelle annonce a été créée pour la voiture ' . $voiture->getMarque() . ' ' . $voiture->getModele() . '.</p>
                    <p>Titre de l\'annonce: ' . $annonce->getTitre() . '</p>'
                );
    
            // Envoi de l'email via le mailer
            $mailer->send($email);
    
            // Message flash de succès
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

#[Route('/annonces/{id}/pdf', name: 'front_annonce_pdf')]
public function generateAnnoncePdf(Annonce $annonce): Response
{
    $dompdf = new Dompdf();

    // Générer le QR code
    $annonceInfo = "Titre: " . $annonce->getTitre() . "\n";
    $annonceInfo .= "Description: " . $annonce->getDescription() . "\n";
    $annonceInfo .= "Marque: " . $annonce->getVoiture()->getMarque() . " " . $annonce->getVoiture()->getModele() . "\n";
    $annonceInfo .= "Année: " . $annonce->getVoiture()->getAnnee() . "\n";
    $annonceInfo .= "Kilométrage: " . $annonce->getVoiture()->getKilometrage() . " km\n";
    $annonceInfo .= "Prix: " . ($annonce->getVoiture()->getPrix() ? $annonce->getVoiture()->getPrix() . ' TND' : 'Non spécifié') . "\n";
    
    $qrCode = new QrCode($annonceInfo);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    $qrCodeBase64 = $result->getDataUri(); // QR Code en base64

    // Récupérer l'image de la voiture
    $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $annonce->getVoiture()->getImage();
    $imageBase64 = null;
    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);
        $imageBase64 = 'data:' . $mimeType . ';base64,' . $imageData;
    }

    // Personnaliser le contenu HTML du PDF
    $html = $this->renderView('front/annonce/annonce_pdf.html.twig', [
        'annonce' => $annonce,
        'imageBase64' => $imageBase64,
        'qrCode' => $qrCodeBase64,  // Ajouter le QR code en base64
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response(
        $dompdf->output(),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="annonce_'.$annonce->getId().'.pdf"',
        ]
    );
}



#[Route('/admin/gestion-annonce/valider/{id}', name: 'valider_annonce')]
public function validerAnnonce(EntityManagerInterface $entityManager, Annonce $annonce): Response
{
    $annonce->setValider(true);
    $entityManager->flush();

    return $this->redirectToRoute('gestion_annonce');
}

}