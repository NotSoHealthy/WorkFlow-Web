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

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le Nom du Département'],
            ])
            ->add('Year_Budget', NumberType::class, [
                'label' => 'Budget Annuel',
                'attr' => ['placeholder' => 'Entrez le Budget Annuel'],
            ])
            ->add('manager', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'first_name', 
                'placeholder' => 'Sélectionnez un Manager',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ]);
    }
}