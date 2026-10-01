<?php

namespace App\Form;

use App\Entity\Appointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Services;


class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startTime', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true, // Permet de s'assurer que le format est bien respecté
            ])
            ->add('duration', ChoiceType::class, [
                'choices' => [
                    '30 minutes' => 0.5,
                    '1 heure' => 1,
                    '1 heure 30' => 1.5,
                    '2 heures' => 2,
                    '2 heures 30' => 2.5,
                    '3 heures' => 3,
                ],
                'label' => 'Durée',
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Choisir la durée',
            ])
            ->add('clientName')
            ->add('clientEmail')
            ->add('clientPhone')
            ->add('service', EntityType::class, [
            'class' => Services::class,
            'choice_label' => 'name', 
            'placeholder' => 'Choisir une prestation',
            'label' => 'Prestation demandée',
            'required' => true,
            'attr' => ['class' => 'form-control'],
        ])
            ->add('notes')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
