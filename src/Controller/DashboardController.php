<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('base.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/tables', name: 'app_tables')]
    public function tables(): Response
    {
        return $this->render('backPage/tables.html.twig');
    }

    #[Route('/billing', name: 'app_billing')]
    public function billing(): Response
    {
        return $this->render('backPage/billing.html.twig');
    }

    #[Route('/virtual-reality', name: 'app_virtual_reality')]
    public function virtualReality(): Response
    {
        return $this->render('backPage/virtual_reality.html.twig');
    }

    #[Route('/rtl', name: 'app_rtl')]
    public function rtl(): Response
    {
        return $this->render('backPage/rtl.html.twig');
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        return $this->render('backPage/profile.html.twig');
    }

    #[Route('/sign-in', name: 'app_sign_in')]
    public function signIn(): Response
    {
        return $this->render('backPage/sign_in.html.twig');
    }

    #[Route('/sign-up', name: 'app_sign_up')]
    public function signUp(): Response
    {
        return $this->render('backPage/sign_up.html.twig');
    }
}

