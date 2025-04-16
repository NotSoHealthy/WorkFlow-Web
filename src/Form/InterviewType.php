<?php
// src/Form/InterviewType.php
namespace App\Form;

use App\Entity\Interview;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('interviewDate', DateTimeType::class, [
                'widget'        => 'single_text',
                'label'         => 'Interview Date and Time',
                'attr'          => [
                    'class'       => 'form-control',
                    'placeholder' => 'Select date and time'
                ],
            ])
            ->add('location', TextType::class, [
                'label'         => 'Location',
                'attr'          => [
                    'class'       => 'form-control',
                    'placeholder' => 'e.g., Office Room 1'
                ],
            ])
            ->add('feedback', TextType::class, [
                'label'         => 'Feedback (optional)',
                'required'      => false,
                'attr'          => [
                    'class'       => 'form-control',
                    'placeholder' => 'Any notes?'
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label'         => 'Status',
                'choices'       => [
                    'Pending'   => 'Pending',
                    'Completed' => 'Completed',
                    'Cancelled' => 'Cancelled',
                ],
                'attr'          => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interview::class,
        ]);
    }
}
