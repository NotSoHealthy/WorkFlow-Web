<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('date_begin', null, [
                'label' => 'Date Debut',
                'widget' => 'single_text',
                'attr' => [
                'min' => (new \DateTime())->format('Y-m-d'), 
                ],
            ])
            ->add('date_end', null, [
                'label' => 'Date Fin',
                'widget' => 'single_text',
                'attr' => [
                'min' => (new \DateTime('+1 week'))->format('Y-m-d'),
                ],
            ])
            ->add('Participants_Max', IntegerType::class, [
                'label' => 'Participants Max',
                'required' => false,
                'empty_data' => '0',
                'data' => 0,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
