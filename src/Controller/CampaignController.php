<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\Donation;
use App\Form\DonationType;
use App\Repository\CampaignRepository;
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

        // Bloquer si campagne terminée ou supprimée
        if (in_array($campaign->getStatus(), ['TERMINEE', 'SUPPRIMEE'], true)) {
            $this->addFlash('warning', 'Cette campagne est clôturée. Vous ne pouvez plus faire de don.');
            return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        // ✅ Calculer ce qui reste
        $target = (float) ($campaign->getTargetAmount() ?? 0);
        $collected = (float) ($campaign->getCollectedAmount() ?? 0);
        $remaining = max(0, $target - $collected);

        // Si objectif déjà atteint (sécurité)
        if ($target > 0 && $remaining <= 0) {
            $campaign->setStatus('TERMINEE');
            $em->flush();

            $this->addFlash('info', 'Cette campagne a déjà atteint son objectif. Les dons sont clôturés.');
            return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
        }

        $donation = new Donation();
        $donation->setCampaign($campaign);
        $donation->setCreatedAt(new \DateTimeImmutable());
        $donation->setUser($this->getUser());

        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        $errorMessage = null;

        if ($form->isSubmitted()) {

            $amount = (float) ($donation->getAmount() ?? 0);

            // ✅ 1) montant minimum
            if ($amount < 10) {
                $errorMessage = 'Le montant minimum de don est de 10 MAD.';
            }
            // ✅ 2) empêcher de dépasser l'objectif
            elseif ($target > 0 && $amount > $remaining) {
                $errorMessage = "Montant trop élevé. Il reste seulement {$remaining} MAD à collecter pour atteindre l'objectif.";
            }
            // ✅ OK si form valide
            elseif ($form->isValid()) {

                // Mise à jour collecte (sans dépasser)
                $newCollected = $collected + $amount;

                // Sécurité : si dépassement malgré tout, on bloque
                if ($target > 0 && $newCollected > $target) {
                    $errorMessage = "Montant non autorisé : cela dépasserait l'objectif de la campagne.";
                } else {
                    $campaign->setCollectedAmount($newCollected);

                    // Si objectif atteint exactement => terminer
                    if ($target > 0 && $newCollected >= $target) {
                        $campaign->setStatus('TERMINEE');
                    }

                    $em->persist($donation);
                    $em->flush();

                    // Redirection vers paiement
                    if ($donation->getPaymentMethod() === 'CARTE') {
                        return $this->redirectToRoute('payment_card', ['id' => $donation->getId()]);
                    }

                    if ($donation->getPaymentMethod() === 'PAYPAL') {
                        return $this->redirectToRoute('payment_paypal', ['id' => $donation->getId()]);
                    }

                    return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
                }
            }
        }

        // recalcul remaining après soumission éventuelle
        $collected = (float) ($campaign->getCollectedAmount() ?? 0);
        $remaining = max(0, $target - $collected);

        return $this->render('campaign/donate.html.twig', [
            'campaign'     => $campaign,
            'form'         => $form->createView(),
            'errorMessage' => $errorMessage,
            'remaining'    => $remaining, // ✅ IMPORTANT pour twig
        ]);
    }



    #[Route('/admin/campagnes', name: 'admin_campaigns')]
    public function adminIndex(CampaignRepository $campaignRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $campaigns = $campaignRepository->findBy([], ['createdAt' => 'DESC']);

        $totalCampaigns   = count($campaigns);
        $totalEnCours     = 0;
        $totalTerminees   = 0;
        $totalSupprimees  = 0;
        $totalCollecte    = 0.0;
        $totalObjectif    = 0.0;
        $totalDonations   = 0;

        foreach ($campaigns as $campaign) {
            switch ($campaign->getStatus()) {
                case 'EN_COURS':
                    $totalEnCours++;
                    break;
                case 'TERMINEE':
                    $totalTerminees++;
                    break;
                case 'SUPPRIMEE':
                    $totalSupprimees++;
                    break;
            }

            $totalCollecte += (float) $campaign->getCollectedAmount();
            $totalObjectif += (float) $campaign->getTargetAmount();
            $totalDonations += $campaign->getDonations()->count();
        }

        $progressGlobal = 0;
        if ($totalObjectif > 0) {
            $progressGlobal = (int) round($totalCollecte / $totalObjectif * 100);
        }

        return $this->render('admin/campaigns.html.twig', [
            'campaigns'       => $campaigns,
            'totalCampaigns'  => $totalCampaigns,
            'totalEnCours'    => $totalEnCours,
            'totalTerminees'  => $totalTerminees,
            'totalSupprimees' => $totalSupprimees,
            'totalCollecte'   => $totalCollecte,
            'totalObjectif'   => $totalObjectif,
            'totalDonations'  => $totalDonations,
            'progressGlobal'  => $progressGlobal,
        ]);
    }

    #[Route('/admin/campagnes/{id}/supprimer', name: 'admin_campaign_delete', methods: ['POST'])]
    public function delete(
        Campaign $campaign,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete_campaign_'.$campaign->getId(), $request->request->get('_token'))) {
            $campaign->setStatus('SUPPRIMEE');
            $em->flush();
            $this->addFlash('success', 'La campagne a été retirée de la plateforme.');
        }

        return $this->redirectToRoute('admin_campaigns');
    }

    #[Route('/admin/campagnes/{id}/statut', name: 'admin_campaign_toggle_status', methods: ['POST'])]
    public function toggleStatus(
        Campaign $campaign,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('toggle_campaign_'.$campaign->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_campaigns');
        }

        if ($campaign->getStatus() === 'SUPPRIMEE') {
            $this->addFlash('warning', 'Cette campagne a été retirée, son statut ne peut plus être modifié.');
            return $this->redirectToRoute('admin_campaigns');
        }

        if ($campaign->getStatus() === 'EN_COURS') {
            $campaign->setStatus('TERMINEE');
            $this->addFlash('info', 'La campagne a été marquée comme terminée.');
        } elseif ($campaign->getStatus() === 'TERMINEE') {
            $campaign->setStatus('EN_COURS');
            $this->addFlash('info', 'La campagne a été remise en cours.');
        } else {
            $this->addFlash('warning', 'Statut non géré : '.$campaign->getStatus());
        }

        $em->flush();
        return $this->redirectToRoute('admin_campaigns');
    }
}
