<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Entity\CampaignProposal;
use App\Form\CampaignProposalType;
use App\Repository\CampaignProposalRepository;
use App\Service\CampaignNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CampaignProposalController extends AbstractController
{
    #[Route('/proposer-campagne', name: 'campaign_propose')]
    public function propose(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
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

            // ✅ Image upload uniquement si form valide
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('campaign_images_directory'),
                        $newFilename
                    );
                    $proposal->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', "Impossible d'enregistrer l'image de la campagne.");
                }
            }

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $proposals = $proposalRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/proposals.html.twig', [
            'proposals' => $proposals,
        ]);
    }

    #[Route('/admin/propositions/{id}/valider', name: 'admin_proposal_validate')]
    public function validate(
        CampaignProposal $proposal,
        EntityManagerInterface $em,
        CampaignNotificationService $notifier
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

        if ($proposal->getImageFilename()) {
            $campaign->setImageUrl('/uploads/campaigns/' . $proposal->getImageFilename());
        }

        $proposal->setStatus('APPROVED');

        $em->persist($campaign);
        $em->flush();

        // ✅ notif simulée (log)
        $notifier->notifyCampaignValidated($campaign->getTitle());

        $this->addFlash('success', 'La campagne a été validée et ajoutée à la plateforme.');
        return $this->redirectToRoute('admin_proposals');
    }

    #[Route('/admin/propositions/{id}/refuser', name: 'admin_proposal_reject')]
    public function reject(CampaignProposal $proposal, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $proposal->setStatus('REJECTED');
        $em->flush();

        $this->addFlash('info', 'La proposition a été refusée.');
        return $this->redirectToRoute('admin_proposals');
    }
}
