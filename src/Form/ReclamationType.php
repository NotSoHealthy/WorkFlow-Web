<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Security;

class ReclamationType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Technique' => 'technique',
                    'Administratif' => 'facturation',
                    'Service' => 'service',
                    'Autre' => 'autre',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Plainte' => 'plainte',
                    'Demande' => 'demande',
                    'Feedback' => 'feedback',
                ],
            ])
            ->add('attachedfile', FileType::class, [
                'label' => 'Fichier joint',
                'required' => false,
                'mapped' => false,
            ]);

        // Only add 'etat' if the user has ROLE_RESPONSABLE and it’s an edit form
        if ($this->security->isGranted('ROLE_RESPONSABLE') && $options['data'] && $options['data']->getId()) {
            $builder->add('etat', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'Ouvert' => 'ouvert',
                    'En cours' => 'enc_cours',
                    'En attente' => 'en_attente',
                    'Fermé' => 'ferme',
                    'Rejeté' => 'rejete',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
