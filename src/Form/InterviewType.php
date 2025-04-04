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
            // Use "interviewDate" in the form view; map it to the entity property "Interview_Date"
            ->add('interviewDate', DateTimeType::class, [
                'widget'        => 'single_text',
                'label'         => 'Interview Date and Time',
                'property_path' => 'Interview_Date',
                'attr'          => [
                    'class'       => 'form-control',
                    'placeholder' => 'Select date and time'
                ],
            ])
            // Use "location" in the form view; map it to the entity property "Location"
            ->add('location', TextType::class, [
                'label'         => 'Location',
                'property_path' => 'Location',
                'attr'          => [
                    'class'       => 'form-control',
                    'placeholder' => 'e.g., Office Room 1'
                ],
            ])
            // Use "feedback" in the form view; map it to the entity property "Feedback"
            ->add('feedback', TextType::class, [
                'label'         => 'Feedback (optional)',
                'required'      => false,
                'property_path' => 'Feedback',
                'attr'          => [
                    'class'       => 'form-control',
                    'placeholder' => 'Any notes?'
                ],
            ])
            // Use "status" in the form view; map it to the entity property "Status"
            ->add('status', ChoiceType::class, [
                'label'         => 'Status',
                'choices'       => [
                    'Scheduled' => 'Scheduled',
                    'Completed' => 'Completed',
                    'Cancelled' => 'Cancelled',
                ],
                'property_path' => 'Status',
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
