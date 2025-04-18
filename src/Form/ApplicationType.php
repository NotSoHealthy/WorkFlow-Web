<?php

namespace App\Form;

use App\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'First Name'])
            ->add('lastName', TextType::class, ['label' => 'Last Name'])
            ->add('mail', EmailType::class, ['label' => 'Email'])
            ->add('jobOffer', null, ['label' => 'Job Offer', 'disabled' => true])
            ->add('CV', FileType::class, [
                'label' => 'Upload your CV',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '40M',
                        'maxSizeMessage' => 'The CV file is too large. Maximum size allowed is 40MB.',
                    ])
                ],
            ])
            ->add('Cover_Letter', FileType::class, [
                'label' => 'Upload your Cover Letter',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '40M',
                        'maxSizeMessage' => 'The cover letter file is too large. Maximum size allowed is 40MB.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
