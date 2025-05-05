<?php

namespace App\Form;

use App\Entity\Entretien;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use DateTimeImmutable;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('dateRdv', DateTimeType::class, [
            'widget' => 'single_text',
            'input' => 'datetime_immutable',
            'html5' => false,
            'format' => 'yyyy-MM-dd HH:mm',
            'attr' => [
                'class' => 'form-control datetime-input',
                'autocomplete' => 'off',
            ],
        ])
        
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Confirmé' => 'confirmed',
                    'En attente' => 'pending',
                    'Annulé' => 'cancelled'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez le motif du rendez-vous'
                ]
            ])
            ->add('entretien', EntityType::class, [
                'class' => Entretien::class,
                'choice_label' => 'typeEntretien',
                'attr' => ['class' => 'form-select'],
                'choice_attr' => function(Entretien $entretien) {
                    return ['data-cout' => $entretien->getCout()];
                }
            ]);
            
     
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}