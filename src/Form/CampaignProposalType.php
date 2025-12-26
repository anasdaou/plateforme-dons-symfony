<?php

namespace App\Form;

use App\Entity\CampaignProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CampaignProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la campagne',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('targetAmount', MoneyType::class, [
                'label' => 'Montant visé',
                'currency' => 'MAD',
            ])
            ->add('proposerName', TextType::class, [
                'label' => 'Votre nom',
            ])
            ->add('proposerEmail', EmailType::class, [
                'label' => 'Votre email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CampaignProposal::class,
        ]);
    }
}
