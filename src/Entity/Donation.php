<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use App\Repository\DonationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


#[ORM\Entity(repositoryClass: DonationRepository::class)]
class Donation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant est obligatoire.")]
    #[Assert\Positive(message: "Le montant doit être supérieur à 0.")]
    #[Assert\GreaterThanOrEqual(
        value: 10,
        message: "Le montant minimum de don est de 10 MAD."
    )]
    private ?float $amount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        max: 200,
        maxMessage: "Le commentaire ne doit pas dépasser {{ limit }} caractères."
    )]
    private ?string $comment = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du donateur est obligatoire.")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $donorName = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email '{{ value }}' n'est pas valide.")]
    private ?string $donorEmail = null;


    #[ORM\Column(length: 100, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'donations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'donations')]
    private ?User $user = null;

    #[Assert\Callback]
    public function validateMaxAmount(ExecutionContextInterface $context): void
    {
        if ($this->campaign === null || $this->amount === null) {
            return;
        }

        $target = (float) ($this->campaign->getTargetAmount() ?? 0);
        $collected = (float) ($this->campaign->getCollectedAmount() ?? 0);

        // Si pas d'objectif, on ne limite pas (ou tu peux décider de limiter)
        if ($target <= 0) {
            return;
        }

        $remaining = $target - $collected;

        // Si la campagne est déjà atteinte
        if ($remaining <= 0) {
            $context->buildViolation("Cette campagne a déjà atteint son objectif. Vous ne pouvez plus faire de don.")
                ->atPath('amount')
                ->addViolation();
            return;
        }

        // Si le don dépasse ce qu'il reste à collecter
        if ($this->amount > $remaining) {
            $context->buildViolation("Le montant dépasse le reste à collecter ({{ remaining }} MAD).")
                ->setParameter('{{ remaining }}', number_format($remaining, 2, '.', ' '))
                ->atPath('amount')
                ->addViolation();
        }
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }


    public function getDonorName(): ?string
    {
        return $this->donorName;
    }

    public function setDonorName(string $donorName): static
    {
        $this->donorName = $donorName;

        return $this;
    }

    public function getDonorEmail(): ?string
    {
        return $this->donorEmail;
    }

    public function setDonorEmail(string $donorEmail): static
    {
        $this->donorEmail = $donorEmail;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): static
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
