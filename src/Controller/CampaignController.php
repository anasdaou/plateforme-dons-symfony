<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\Donation;
use App\Form\DonationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CampaignController extends AbstractController
{
    #[Route('/campaign/{id}', name: 'campaign_show')]
    public function show(Campaign $campaign): Response
    {
        return $this->render('campaign/show.html.twig', [
            'campaign' => $campaign,
        ]);
    }

    #[Route('/campaign/{id}/donate', name: 'campaign_donate')]
    public function donate(
        Campaign $campaign,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $donation = new Donation();
        $donation->setCampaign($campaign);
        $donation->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le montant collecté de la campagne
            $campaign->setCollectedAmount(
                $campaign->getCollectedAmount() + $donation->getAmount()
            );

            $em->persist($donation);
            $em->flush();

            // Redirection vers la "page de paiement" selon la méthode choisie
            if ($donation->getPaymentMethod() === 'CARTE') {
                return $this->redirectToRoute('payment_card', ['id' => $donation->getId()]);
            }

            if ($donation->getPaymentMethod() === 'PAYPAL') {
                return $this->redirectToRoute('payment_paypal', ['id' => $donation->getId()]);
            }

            // Sécurité : si aucune méthode reconnue, retour à la campagne
            return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
        }


        return $this->render('campaign/donate.html.twig', [
            'campaign' => $campaign,
            'form' => $form->createView(),
        ]);
    }
        #[Route('/payment/card/{id}', name: 'payment_card')]
    public function cardPayment(Donation $donation, Request $request): Response
    {
        $campaign = $donation->getCampaign();

        if ($request->isMethod('POST')) {
            // Ici on simule un paiement réussi
            $this->addFlash('success', 'Paiement par carte effectué avec succès !');

            return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
        }

        return $this->render('payment/card.html.twig', [
            'donation' => $donation,
            'campaign' => $campaign,
        ]);
    }

    #[Route('/payment/paypal/{id}', name: 'payment_paypal')]
    public function paypalPayment(Donation $donation, Request $request): Response
    {
        $campaign = $donation->getCampaign();

        if ($request->isMethod('POST')) {
            // Simulation paiement PayPal
            $this->addFlash('success', 'Paiement PayPal effectué avec succès !');

            return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
        }

        return $this->render('payment/paypal.html.twig', [
            'donation' => $donation,
            'campaign' => $campaign,
        ]);
    }

}
