<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Type', ChoiceType::class, [
                'choices' => [
                    'Accès-Normal' => 40,   // 'Label' => 'Price'
                    'Semi-VIP' => 50,
                    'VIP' => 90,
                ],
                'placeholder' => 'Selectionner un type', // This field is not directly mapped to the entity
                'attr' => ['class' => 'type-selector'],
                'required' => false
            ])
            ->add('NombreDePlaces', NumberType::class, [
                'attr' => ['min' => 1, 'class' => 'places-input'],
                'required' => false
            ])
            ->add('Price', NumberType::class, [
                'disabled' => true, // Prevent manual modification
                'attr' => ['class' => 'price-input'],
                'required' => false
            ])
            ->add('qr_url', HiddenType::class, [
                'required' => false,
                'attr' => ['id' => 'qr_url'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
