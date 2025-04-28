<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Entrez le titre de la tâche'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Entrez une description...'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices'  => [
                    'À faire' => 'To Do',
                    'En cours' => 'In Progress',
                    'Terminé' => 'Completed',
                    'Bloqué' => 'Blocked',
                ],
            ])->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices'  => [
                    'À faire' => 'To Do',
                    'En cours' => 'In Progress',
                    'Terminé' => 'Completed',
                    'Bloqué' => 'Blocked',
                ],
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Basse' => 'Low',
                    'Moyenne' => 'Medium',
                    'Haute' => 'High',
                ],
            ])
            ->add('start_date', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
            ])
            ->add('due_date', DateType::class, [
                'label' => 'Date limite',
                'widget' => 'single_text',
            ])
            ->add('completion_date', DateType::class, [
                'label' => 'Date de fin (si terminé)',
                'widget' => 'single_text',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
