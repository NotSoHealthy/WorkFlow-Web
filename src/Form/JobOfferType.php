<?php

namespace App\Form;

use App\Entity\JobOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType; // <-- Correct import
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Title', TextType::class, [
                'label' => 'Title',
                'required' => true,
            ])
            ->add('Description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('Publication_Date', DateType::class, [
                'label' => 'Publication Date',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('Expiration_Date', DateType::class, [
                'label' => 'Expiration Date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('Contract_Type', TextType::class, [
                'label' => 'Contract Type',
                'required' => false,
            ])
            ->add('Salary', NumberType::class, [
                'label' => 'Salary',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}
