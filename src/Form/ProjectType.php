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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Entrez le Nom du Projet'],
                'required' => true, 
            ])
            ->add('Description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Entrez la Description du Projet'],
                'required' => true, 
            ])
            ->add('Start_Date', DateType::class, [
                'label' => 'Date de Début',
                'widget' => 'single_text',
                'required' => true, 
            ])
            ->add('End_Date', DateType::class, [
                'label' => 'Date de Fin',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('Budget', NumberType::class, [
                'label' => 'Budget',
                'attr' => ['placeholder' => 'Entrez le Budget'],
                'required' => true, 
            ])
            ->add('manager', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'first_name',
                'placeholder' => 'Sélectionnez un Manager',
                'required' => true, 
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => 'Name',
                'placeholder' => 'Sélectionnez un Département',
                'required' => true, 
            ])
            ->add('State', ChoiceType::class, [
                'choices'  => [
                    'En cours' => 'en cours',
                    'Terminé' => 'terminé',
                    'Annulé' => 'annulé',
                    'required' => true, 
                ],
                'placeholder' => 'Sélectionnez un état',
                'expanded' => false,
                'multiple' => false,
                'required' => true, 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}