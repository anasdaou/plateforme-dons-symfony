<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class CampaignNotificationService
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * Simulation : plus tard tu peux envoyer un email au proposeur.
     */
    public function notifyCampaignValidated(string $campaignTitle): void
    {
        // Ici on simule une notif (log)
        $this->logger->info('Campagne validée : ' . $campaignTitle);
    }
}
