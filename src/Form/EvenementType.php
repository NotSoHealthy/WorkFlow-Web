<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Title', TextType::class, [
            'label' => 'Titre',
            'required' => false
        ])
        ->add('Description', TextType::class, [
            'required' => false,
        ])
        ->add('DateAndTime', null, [
            'widget' => 'single_text',
            'required' => false,
            'label' => 'Date & Temps',
            'empty_data' => (new \DateTime())->format('Y-m-d H:i:s'),
            'attr' => [
                    'placeholder' => 'YYYY-MM-DD HH:MM'
                ]
        ])
        ->add('Location', null, [
            'label' => 'Adresse',
            'required' => false
        ])
        ->add('Type', ChoiceType::class, [
            'label' => 'Type',
            'required' => false,
            'choices' => [
                'Conference' => 'conference',
                'Workshop' => 'workshop',
                'Webinar' => 'webinar',
                'Meetup' => 'meetup',
            ],
            'placeholder' => 'Selectionner un Type',
        ])
        ->add('NumberOfPlaces', null, [
            'label' => 'Nombre de place',
            'required' => false
        ])
        ->add('isOnline', null, [
            'label' => 'En Ligne'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
