<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_front_accueil')]
    public function index(): Response
    {
        return $this->render('front/accueil/accueil.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }
}
