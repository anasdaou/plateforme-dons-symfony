<?php

namespace App\Controller;

use App\Entity\CampaignProposal;
use App\Form\CampaignProposalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Campaign;
use App\Repository\CampaignProposalRepository;


class CampaignProposalController extends AbstractController
{
    #[Route('/proposer-campagne', name: 'campaign_propose')]
    public function propose(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Vous devez vous connecter ou créer un compte pour proposer une campagne.');
            return $this->redirectToRoute('app_login');
        }
        $proposal = new CampaignProposal();
        $proposal->setCreatedAt(new \DateTimeImmutable());
        $proposal->setStatus('PENDING');

        $form = $this->createForm(CampaignProposalType::class, $proposal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($proposal);
            $em->flush();

            $this->addFlash('success', 'Votre campagne a été proposée. Elle sera étudiée par l’administrateur.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('campaign_proposal/propose.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/propositions', name: 'admin_proposals')]
    public function listProposals(CampaignProposalRepository $proposalRepository): Response
    {
            $proposals = $proposalRepository->findBy([], ['createdAt' => 'DESC']);

            return $this->render('admin/proposals.html.twig', [
                'proposals' => $proposals,
            ]);
    }

    #[Route('/admin/propositions/{id}/valider', name: 'admin_proposal_validate')]
    public function validate(
        CampaignProposal $proposal,
        EntityManagerInterface $em
    ): Response {
        // Créer une vraie campagne à partir de la proposition
        $now = new \DateTimeImmutable();

        $campaign = new Campaign();
        $campaign->setTitle($proposal->getTitle());
        $campaign->setDescription($proposal->getDescription());
        $campaign->setTargetAmount($proposal->getTargetAmount());
        $campaign->setCollectedAmount(0);
        $campaign->setStatus('EN_COURS');
        $campaign->setCreatedAt($now);
        $campaign->setStartDate($now);
        $campaign->setEndDate($now->modify('+30 days'));

        $proposal->setStatus('APPROVED');

        $em->persist($campaign);
        $em->flush();

        $this->addFlash('success', 'La campagne a été validée et ajoutée à la plateforme.');

        return $this->redirectToRoute('admin_proposals');
    }

    #[Route('/admin/propositions/{id}/refuser', name: 'admin_proposal_reject')]
    public function reject(
        CampaignProposal $proposal,
        EntityManagerInterface $em
    ): Response {
        $proposal->setStatus('REJECTED');

        $em->flush();

        $this->addFlash('info', 'La proposition a été refusée.');

        return $this->redirectToRoute('admin_proposals');
    }

}
