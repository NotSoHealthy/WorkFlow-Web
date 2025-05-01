<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('method', ChoiceType::class, [
                'choices' => [
                    'Email' => 'email',
                    'Phone' => 'phone',
                ],
                'label' => 'Recevez le lien de réinitialisation via',
                'expanded' => true,
                'multiple' => false,
                'data' => 'email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une méthode',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'Adresse email',
            ])
            ->add('phone', TelType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!isset($data['method'])) {
                return;
            }

            if ($data['method'] === 'email') {
                $form->add('email', EmailType::class, [
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer votre adresse e-mail',
                        ]),
                        new EmailConstraint([
                            'message' => "S'il vous plaît, mettez une adresse email valide",
                        ]),
                    ],
                    'label' => 'Adresse email',
                ]);
            }

            if ($data['method'] === 'phone') {
                $form->add('phone', TelType::class, [
                    'required' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer votre numéro de téléphone',
                        ]),
                        new Regex([
                            'pattern' => '/^[0-9]{8}$/',
                            'message' => "Veuillez saisir un numéro de téléphone valide",
                        ]),
                    ],
                    'label' => 'Numéro de téléphone',
                ]);
            }
        });
    }
}

