<?php

namespace App\Form;

use App\Entity\JobOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label'    => 'Title',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => true,
            ])
            ->add('publicationDate', DateType::class, [
                'label'    => 'Publication Date',
                'widget'   => 'single_text',
                'format'   => 'yyyy-MM-dd',
                'input'    => 'datetime',  // Ensures transformation to DateTime
                'required' => true,
            ])
            ->add('expirationDate', DateType::class, [
                'label'    => 'Expiration Date',
                'widget'   => 'single_text',
                'format'   => 'yyyy-MM-dd',
                'input'    => 'datetime',  // Ensures transformation to DateTime
                'required' => false,
            ])
            ->add('contractType', TextType::class, [
                'label'    => 'Contract Type',
                'required' => true,
            ])
            ->add('salary', NumberType::class, [
                'label'    => 'Salary',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}
