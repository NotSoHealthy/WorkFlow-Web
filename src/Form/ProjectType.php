<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\Department;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le Nom du Projet'],
            ])
            ->add('Description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Entrez la Description du Projet'],
            ])
            ->add('Start_Date', DateType::class, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
            ])
            ->add('End_Date', DateType::class, [
                'label' => 'Date de Fin',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('Budget', NumberType::class, [
                'label' => 'Budget',
                'attr' => ['placeholder' => 'Entrez le Budget'],
            ])
            ->add('manager', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'placeholder' => 'Sélectionnez un Manager',
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'Name',
                'placeholder' => 'Sélectionnez un Département',
            ])
            ->add('State', TextType::class, [
                'label' => 'État',
                'attr' => ['placeholder' => 'Entrez l\'État du Projet'],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}