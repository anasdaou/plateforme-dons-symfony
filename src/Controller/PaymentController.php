<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Form\CardPaymentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment/card/{id}', name: 'payment_card')]
    public function card(
        Request $request,
        Donation $donation,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $campaign = $donation->getCampaign();
        if (!$campaign) {
            throw $this->createNotFoundException('Campagne introuvable pour ce don.');
        }

        $form = $this->createForm(CardPaymentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ✅ Paiement simulé (si tu as un champ status un jour : setPaid etc.)
            // $donation->setPaymentStatus('PAID');
            // $em->flush();

            $this->addFlash('success', 'Paiement par carte effectué avec succès ✅');

            return $this->redirectToRoute('payment_success', [
                'id' => $donation->getId(),
            ]);
        }

        return $this->render('payment/card.html.twig', [
            'form' => $form->createView(),
            'donation' => $donation,
            'campaign' => $campaign,
        ]);
    }

    #[Route('/payment/paypal/{id}', name: 'payment_paypal')]
    public function paypal(
        Request $request,
        Donation $donation,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $campaign = $donation->getCampaign();
        if (!$campaign) {
            throw $this->createNotFoundException('Campagne introuvable pour ce don.');
        }

        if ($request->isMethod('POST')) {

            // ✅ CSRF
            if (!$this->isCsrfTokenValid('paypal_payment_'.$donation->getId(), $request->request->get('_token'))) {
                $this->addFlash('danger', 'Token CSRF invalide.');
                return $this->redirectToRoute('payment_paypal', ['id' => $donation->getId()]);
            }

            $paypalEmail = trim((string) $request->request->get('paypal_email'));
            $paypalPassword = trim((string) $request->request->get('paypal_password'));

            if ($paypalEmail === '' || $paypalPassword === '') {
                $this->addFlash('danger', 'Veuillez saisir email et mot de passe PayPal.');
                return $this->redirectToRoute('payment_paypal', ['id' => $donation->getId()]);
            }

            // ✅ Paiement simulé
            // $donation->setPaymentStatus('PAID');
            // $em->flush();

            $this->addFlash('success', 'Paiement PayPal effectué avec succès ✅');

            return $this->redirectToRoute('payment_success', [
                'id' => $donation->getId(),
            ]);
        }

        return $this->render('payment/paypal.html.twig', [
            'donation' => $donation,
            'campaign' => $campaign,
        ]);
    }

    #[Route('/payment/success/{id}', name: 'payment_success')]
    public function success(Donation $donation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $campaign = $donation->getCampaign();
        if (!$campaign) {
            throw $this->createNotFoundException('Campagne introuvable pour ce don.');
        }

        return $this->render('payment/success.html.twig', [
            'donation' => $donation,
            'campaign' => $campaign,
        ]);
    }
}
