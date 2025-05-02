<?php

namespace App\Form;

use App\Entity\Department;
use App\Entity\User;
<<<<<<< HEAD
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

=======
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;
>>>>>>> parent of 5b270dd (Revert "bundle+api")
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le Nom du Département'],
<<<<<<< HEAD
                'required' => true,
                'empty_data' => '',
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
=======
>>>>>>> parent of 5b270dd (Revert "bundle+api")
                'required' => true,
                'empty_data' => '',
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
            ->add('manager', TypeaheadType::class, [
                'label' => 'Manager',
                'required' => true,
                'remote_route' => 'autocomplete_managers', // must match your controller route name
                'class' => User::class,
                'label_field' => 'firstName',
                'value_field' => 'id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le manager est requis.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Recherche de manager...',
                    'autocomplete' => 'off',
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
