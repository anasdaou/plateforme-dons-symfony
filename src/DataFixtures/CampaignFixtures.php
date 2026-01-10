<?php

namespace App\DataFixtures;

use App\Entity\Campaign;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampaignFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        $campaigns = [

            [
                'title' => 'Relogement des familles touchées par le séisme d’Al Haouz',
                'description' => 'Financer la reconstruction et l’aménagement d’habitations en zones montagneuses.',
                'target' => 500000,
                'collected' => 210000,
                'image' => '/uploads/campaigns/presets/seisme_haouz.jpg'
            ],

            [
                'title' => 'Soutien aux femmes rurales artisanes de montagne',
                'description' => 'Former et équiper des coopératives féminines en tissage et artisanat.',
                'target' => 120000,
                'collected' => 45000,
                'image' => '/uploads/campaigns/presets/artisanat_femmes.jpg'
            ],

            [
                'title' => 'Réhabilitation des routes rurales en montagne',
                'description' => 'Améliorer l’accès aux villages isolés pour le transport scolaire et médical.',
                'target' => 300000,
                'collected' => 138000,
                'image' => '/uploads/campaigns/presets/routes_montagne.jpg'
            ],

            [
                'title' => 'Soutien aux familles sinistrées par les inondations de Safi',
                'description' => 'Aider au remplacement des biens essentiels et réhabilitation des logements.',
                'target' => 200000,
                'collected' => 89000,
                'image' => '/uploads/campaigns/presets/inondations_safi.jpg'
            ],

            [
                'title' => 'Réouverture et équipement des écoles rurales',
                'description' => 'Réparer les classes et fournir du matériel scolaire aux enfants.',
                'target' => 150000,
                'collected' => 62000,
                'image' => '/uploads/campaigns/presets/ecoles_reconstruction.jpg'
            ],

            [
                'title' => 'Soutien psychologique aux enfants victimes du séisme',
                'description' => 'Mettre en place des ateliers et accompagnement spécialisé.',
                'target' => 80000,
                'collected' => 32000,
                'image' => '/uploads/campaigns/presets/soutien_psychologique.jpg'
            ],

            [
                'title' => 'Création de micro-entreprises pour jeunes diplômés',
                'description' => 'Financer du matériel et accompagner les projets locaux.',
                'target' => 100000,
                'collected' => 27000,
                'image' => '/uploads/campaigns/presets/micro_entreprises.jpg'
            ],

            [
                'title' => 'Réhabilitation des maisons traditionnelles en pisé',
                'description' => 'Consolidation des bâtisses fragilisées dans les villages du Haut Atlas.',
                'target' => 220000,
                'collected' => 104000,
                'image' => '/uploads/campaigns/presets/rehabilitation_maisons.jpg'
            ],

            [
                'title' => 'Accès à l’eau potable dans les villages isolés',
                'description' => 'Installation de réservoirs et canalisations rurales.',
                'target' => 90000,
                'collected' => 51000,
                'image' => '/uploads/campaigns/presets/eau_potable.jpg'
            ],

            [
                'title' => 'Transport scolaire pour enfants de zones montagneuses',
                'description' => 'Financement de minibus et carburant pour le trajet quotidien.',
                'target' => 130000,
                'collected' => 67000,
                'image' => '/uploads/campaigns/presets/relogement_montagne.jpg'
            ],

        ];

        foreach ($campaigns as $data) {

            $campaign = new Campaign();
            $campaign->setTitle($data['title']);
            $campaign->setDescription($data['description']);
            $campaign->setTargetAmount($data['target']);
            $campaign->setCollectedAmount($data['collected']);
            $campaign->setImageUrl($data['image']);
            $campaign->setStatus('EN_COURS');
            $campaign->setCreatedAt($now);
            $campaign->setStartDate($now);
            $campaign->setEndDate($now->modify('+45 days'));

            $manager->persist($campaign);
        }

        $manager->flush();
    }
}
