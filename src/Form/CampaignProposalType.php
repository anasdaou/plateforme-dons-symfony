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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;



class CampaignProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la campagne',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de la campagne (JPEG/PNG)',
                'mapped' => false,           // pas lié directement à l'entité
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize'      => '4M',

                        // Empêcher images trop petites
                        'minWidth'     => 800,
                        'minHeight'    => 400,

                        // Autoriser uniquement formats pro
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],

                        // Messages d’erreurs propres
                        'mimeTypesMessage' => 'Seules les images JPEG ou PNG sont autorisées.',
                        'minWidthMessage'  => 'L’image doit avoir une largeur minimale de 800px.',
                        'minHeightMessage' => 'L’image doit avoir une hauteur minimale de 400px.',
                    ]),
                ],

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
