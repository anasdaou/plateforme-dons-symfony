<?php

namespace App\Controller;

use App\Repository\CampaignRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, CampaignRepository $campaignRepository): Response
    {
        $searchTerm      = $request->query->get('q');
        $sort            = $request->query->get('sort');
        $selectedCategory = $request->query->get('category');

        $qb = $campaignRepository->createQueryBuilder('c');

        // On cache seulement les campagnes supprimées
        $qb->where('c.status != :deleted')
        ->setParameter('deleted', 'SUPPRIMEE');

        // Filtre recherche
        if ($searchTerm) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(c.title)', ':term'),
                        $qb->expr()->like('LOWER(c.description)', ':term')
                    )
                )
                ->setParameter('term', '%'.mb_strtolower($searchTerm).'%');
        }

        // Filtre par catégorie (si choisie)
        if ($selectedCategory && $selectedCategory !== 'ALL') {
            $qb->andWhere('c.category = :category')
            ->setParameter('category', $selectedCategory);
        }

        // Tri
        switch ($sort) {
            case 'amount_desc':
                $qb->orderBy('c.targetAmount', 'DESC');
                break;
            case 'amount_asc':
                $qb->orderBy('c.targetAmount', 'ASC');
                break;
            case 'title_asc':
                $qb->orderBy('c.title', 'ASC');
                break;
            case 'title_desc':
                $qb->orderBy('c.title', 'DESC');
                break;
            default:
                $qb->orderBy('c.status', 'ASC')
                ->addOrderBy('c.createdAt', 'DESC');
                break;
        }

        $campaigns = $qb->getQuery()->getResult();

        return $this->render('home/index.html.twig', [
            'campaigns'        => $campaigns,
            'searchTerm'       => $searchTerm,
            'sort'             => $sort,
            'selectedCategory' => $selectedCategory,
        ]);
    }


    }
