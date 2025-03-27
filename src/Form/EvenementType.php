<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Title', null, [
            'label' => 'Titre'
        ])
        ->add('Description')
        ->add('DateAndTime', null, [
            'widget' => 'single_text',
            'label' => 'Date & Temps'
        ])
        ->add('Location', null, [
            'label' => 'Adresse'
        ])
        ->add('Type', ChoiceType::class, [
            'label' => 'Type',
            'choices' => [
                'Conference' => 'conference',
                'Workshop' => 'workshop',
                'Webinar' => 'webinar',
                'Meetup' => 'meetup',
            ],
            'placeholder' => 'Selectionner un evenement',
            'required' => true
        ])
        ->add('NumberOfPlaces', null, [
            'label' => 'Nombre de place'
        ])
        ->add('isOnline', null, [
            'label' => 'Online'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
