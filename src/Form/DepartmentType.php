<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Name', TextType::class, [
            'label' => 'Nom',
            'attr' => ['placeholder' => 'Entrez le Nom du Département'],
            'required' => true,
            'empty_data' => '', // this is what fixes the error!
            'constraints' => [
                new NotBlank([
                    'message' => 'Le nom du département est requis.',
                ]),
            ],
        ])
        ->add('Year_Budget', NumberType::class, [
            'label' => 'Budget Annuel',
            'required' => true,
            'empty_data' => '0', 
            'attr' => ['placeholder' => 'Entrez le Budget Annuel'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Le budget annuel est requis.',
                ]),
            ],
        ])
            ->add('manager', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'first_name',
                'placeholder' => 'Sélectionnez un Manager',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le manager est requis.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }
}