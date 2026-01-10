<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CardPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'Numéro de carte',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 19,
                    'inputmode' => 'numeric',
                    'placeholder' => 'XXXX XXXX XXXX XXXX',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de carte est obligatoire.']),
                    new Assert\Regex([
                        'pattern' => '/^[0-9 ]+$/',
                        'message' => 'Le numéro de carte ne doit contenir que des chiffres.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9 ]{13,19}$/',
                        'message' => 'Le numéro de carte doit contenir entre 13 et 19 caractères.',
                    ]),
                ],
            ])

            ->add('expiration', TextType::class, [
                'label' => "Date d'expiration (MM/AA)",
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MM/AA',
                    'maxlength' => 5,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => "La date d'expiration est obligatoire."]),
                    new Assert\Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/\d{2}$/',
                        'message' => "La date d'expiration doit être au format MM/AA.",
                    ]),
                    new Assert\Callback(function ($value, $context) {
                        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', (string) $value)) {
                            return;
                        }

                        [$month, $year] = explode('/', (string) $value);
                        $month = (int) $month;
                        $year = (int) ('20' . $year);

                        $now = new \DateTimeImmutable();
                        $currentYear = (int) $now->format('Y');
                        $currentMonth = (int) $now->format('m');

                        if ($year < $currentYear || ($year === $currentYear && $month < $currentMonth)) {
                            $context->buildViolation("La carte est expirée.")->addViolation();
                        }
                    }),
                ],
            ])

            ->add('cvv', TextType::class, [
                'label' => 'Cryptogramme (CVV)',
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 4,
                    'inputmode' => 'numeric',
                    'placeholder' => '3 ou 4 chiffres',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le CVV est obligatoire.']),
                    new Assert\Regex([
                        'pattern' => '/^\d{3,4}$/',
                        'message' => 'Le CVV doit contenir 3 ou 4 chiffres.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
