<?php

namespace App\Form;

use App\Entity\Donation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class DonationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', null, [
                'label' => 'Montant du don',
                'attr' => [
                    'min' => 10,
                    'step' => 1,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le montant est obligatoire.']),
                    new Assert\Positive(['message' => 'Le montant doit être supérieur à 0.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => 10,
                        'message' => 'Le montant minimum de don est de 10 MAD.',
                    ]),
                ],
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
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir un moyen de paiement.']),
                ],
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
