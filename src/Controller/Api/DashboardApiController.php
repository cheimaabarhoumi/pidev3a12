<?php
// Pi2025/src/Controller/Api/DashboardApiController.php

namespace App\Controller\Api;

use App\Repository\SponsoringRepository;
use App\Service\ReportingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class DashboardApiController extends AbstractController
{
    public function __construct(
        private SponsoringRepository $sponsoringRepository,
        private ReportingService $reportingService
    ) {}

    #[Route('/sponsorings', name: 'sponsorings_list', methods: ['GET'])]
    public function getSponsors(): JsonResponse
    {
        $sponsors = $this->sponsoringRepository->findAll();
        $data = [];

        foreach ($sponsors as $sponsor) {
            $data[] = [
                'id' => $sponsor->getId(),
                'nomPartenaire' => $sponsor->getNomPartenaire(),
                'montant' => $sponsor->getMontant(),
                'dateDebut' => $sponsor->getDateDebut()->format('Y-m-d'),
                'dateFin' => $sponsor->getDateFin()->format('Y-m-d'),
                'etat' => $sponsor->getEtat(),
                'typeSponsoring' => $sponsor->getTypeSponsoring()
            ];
        }

        return $this->json($data);
    }

    #[Route('/sponsoring/{id}', name: 'sponsoring_show', methods: ['GET'])]
    public function getSponsor(int $id): JsonResponse
    {
        $sponsor = $this->sponsoringRepository->find($id);

        if (!$sponsor) {
            return $this->json(['message' => 'Sponsoring non trouvé'], 404);
        }

        return $this->json([
            'id' => $sponsor->getId(),
            'nomPartenaire' => $sponsor->getNomPartenaire(),
            'montant' => $sponsor->getMontant(),
            'dateDebut' => $sponsor->getDateDebut()->format('Y-m-d'),
            'dateFin' => $sponsor->getDateFin()->format('Y-m-d'),
            'etat' => $sponsor->getEtat(),
            'typeSponsoring' => $sponsor->getTypeSponsoring()
        ]);
    }

    #[Route('/dashboard/stats', name: 'dashboard_stats', methods: ['GET'])]
    public function getDashboardStats(): JsonResponse
    {
        return $this->json([
            'stats' => $this->reportingService->getMonthlyStats(),
            'type_distribution' => $this->sponsoringRepository->getTypeDistribution(),
            'total_amount' => $this->sponsoringRepository->getTotalAmount()
        ]);
    }

    #[Route('/dashboard/chart-data', name: 'dashboard_chart_data', methods: ['GET'])]
    public function getChartData(): JsonResponse
    {
        $currentYear = (new \DateTime())->format('Y');
        $monthlyData = [];

        // Données mensuelles pour l'année en cours
        for ($month = 1; $month <= 12; $month++) {
            $date = new \DateTime("$currentYear-$month-01");
            $monthlyData[$date->format('M')] = $this->sponsoringRepository->getMonthlyTotal($date);
        }

        return $this->json([
            'monthly_revenue' => $monthlyData,
            'sponsorship_types' => $this->sponsoringRepository->getTypeDistribution()
        ]);
    }

    #[Route('/dashboard/active-sponsors', name: 'dashboard_active_sponsors', methods: ['GET'])]
    public function getActiveSponsorStats(): JsonResponse
    {
        return $this->json([
            'active_count' => $this->sponsoringRepository->countActiveSponsors(),
            'monthly_new' => $this->sponsoringRepository->countNewSponsors(new \DateTime())
        ]);
    }
}
