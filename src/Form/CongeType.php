<?php

namespace App\Form;

use App\Entity\Conge;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CongeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('request_date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('start_date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('end_date', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('reason')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conge::class,
        ]);
    }
}
