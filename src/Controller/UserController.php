<?php

namespace App\Controller;

use App\Repository\DonationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/mes-dons', name: 'user_donations')]
    public function donations(DonationRepository $donationRepository): Response
    {
        // Sécurité : seulement les utilisateurs connectés
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupérer tous les dons de l'utilisateur connecté, du plus récent au plus ancien
        $donations = $donationRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        // Calcul du total donné
        $totalAmount = 0;
        foreach ($donations as $donation) {
            $totalAmount += $donation->getAmount();
        }

        return $this->render('user/donations.html.twig', [
            'donations'   => $donations,
            'totalAmount' => $totalAmount,
        ]);
    }
}
