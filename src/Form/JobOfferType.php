<?php
// src/Form/JobOfferType.php

namespace App\Form;

use App\Entity\JobOffer;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr'  => ['class' => 'form-input'],
            ])
            ->add('description', CKEditorType::class, [
                'label'       => 'Description',
                'config_name' => 'default',
                'attr'        => ['class' => 'form-input'],
            ])
            ->add('publicationDate', DateType::class, [
                'widget'  => 'single_text',
                'label'   => 'Date de publication',
                'attr'    => ['class' => 'form-input', 'placeholder' => 'YYYY-MM-DD'],
            ])
            ->add('expirationDate', DateType::class, [
                'widget'   => 'single_text',
                'label'    => "Date d'expiration",
                'required' => false,
                'attr'     => ['class' => 'form-input', 'placeholder' => 'YYYY-MM-DD'],
            ])
            ->add('contractType', TextType::class, [
                'label' => 'Type de contrat',
                'attr'  => ['class' => 'form-input'],
            ])
            ->add('salary', MoneyType::class, [
                'label'    => 'Salaire',
                'currency' => 'TND',
                'attr'     => ['class' => 'form-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}
