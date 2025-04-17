<?php

namespace App\Form;

use App\Entity\User;
use App\Service\DelegationService;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    private $delegationService;

    public function __construct(DelegationService $delegationService)
    {
        $this->delegationService = $delegationService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $delegations = $this->delegationService->getDelegations();

        $builder
            ->add('first_name', TextType::class, [
                'label' => 'Prénom'
                ])
            ->add('last_name', TextType::class, [
                'label' => 'Nom'
                ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email'
                ])
            ->add('number', TelType::class, [
                'label' => 'Numéro de téléphone'
                ])
            ->add('gouvernorat', ChoiceType::class, [
                'choices' => array_combine($delegations, $delegations),
                'placeholder' => 'Selectionner une délégation',
                'label' => 'Délégation',
                'attr' => [
                    'placeholder' => 'Sélectionnez une délégation',
                    'list' => 'delegation_list'
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse'
                ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmez votre mot de passe'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
