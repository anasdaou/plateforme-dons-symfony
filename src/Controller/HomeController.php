<?php

namespace App\Controller;

use App\Repository\CampaignRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CampaignRepository $campaignRepository): Response
    {
        // Récupérer toutes les campagnes (on améliorera plus tard : statut, ordre, etc.)
        $campaigns = $campaignRepository->findAll();

        return $this->render('home/index.html.twig', [
            'campaigns' => $campaigns,
        ]);
    }
}
