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
    // 🔒 si l'utilisateur n'est pas connecté
    if (!$this->getUser()) {
        $this->addFlash('warning', 'Vous devez vous connecter ou créer un compte pour effectuer un don.');
        return $this->redirectToRoute('app_login');
    }

    $donation = new Donation();
    $donation->setCampaign($campaign);
    $donation->setCreatedAt(new \DateTimeImmutable());
    $donation->setUser($this->getUser());

    $form = $this->createForm(DonationType::class, $donation);
    $form->handleRequest($request);

    // 🔴 message d'erreur qu'on va afficher dans le Twig
    $errorMessage = null;

    if ($form->isSubmitted()) {
        // Vérification MANUELLE du montant
        if ($donation->getAmount() === null || $donation->getAmount() < 10) {
            $errorMessage = 'Le montant minimum de don est de 10 MAD.';
        } elseif ($form->isValid()) {
            // Tout est bon : on enregistre le don

            // mise à jour du montant collecté
            $campaign->setCollectedAmount(
                $campaign->getCollectedAmount() + $donation->getAmount()
            );

            $em->persist($donation);
            $em->flush();

            // redirection vers le paiement
            if ($donation->getPaymentMethod() === 'CARTE') {
                return $this->redirectToRoute('payment_card', ['id' => $donation->getId()]);
            }

            if ($donation->getPaymentMethod() === 'PAYPAL') {
                return $this->redirectToRoute('payment_paypal', ['id' => $donation->getId()]);
            }

            // secours
            return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
        }
    }

    return $this->render('campaign/donate.html.twig', [
        'campaign' => $campaign,
        'form' => $form->createView(),
        'errorMessage' => $errorMessage, // 👈 on envoie le message à la vue
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
            // Vérifier que les deux champs sont remplis
            if (
                empty($request->request->get('paypal_email')) ||
                empty($request->request->get('paypal_password'))
            ) {
                $this->addFlash('danger', 'Veuillez saisir email et mot de passe PayPal.');

                return $this->redirectToRoute('payment_paypal', [
                    'id' => $donation->getId(),
                ]);
            }

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
