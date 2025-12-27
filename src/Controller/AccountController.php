<?php

namespace App\Controller;

use App\Repository\DonationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/mon-compte/dons', name: 'account_donations')]
    public function donations(DonationRepository $donationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $donations = $donationRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('account/donations.html.twig', [
            'donations' => $donations,
        ]);
    }
}
