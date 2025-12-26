<?php

namespace App\Form;

use App\Entity\Donation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class DonationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'Montant du don',
                'currency' => 'MAD',
            ])
            ->add('donorName', TextType::class, [
                'label' => 'Nom du donateur',
            ])
            ->add('donorEmail', EmailType::class, [
                'label' => 'Email du donateur',
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Moyen de paiement',
                'choices' => [
                    'Carte bancaire' => 'CARTE',
                    'PayPal' => 'PAYPAL',
                ],
                'expanded' => true,   // affiche des boutons radio
                'multiple' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
        ]);
    }
}
