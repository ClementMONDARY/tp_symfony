<?php

namespace App\Form;

use App\Entity\Event;
use App\Enum\EventStatus;
use App\Enum\EventType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Event Name',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Event Description',
                'required' => false
            ])
            ->add('location', TextType::class, [
                'label' => 'Event Location',
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Event Type',
                'choices' => EventType::cases(),
                'choice_label' => fn($choice) => $choice->getLabel(),
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Event Status',
                'choices' => EventStatus::cases(),
                'choice_label' => fn($choice) => $choice->getLabel(),
                'data' => EventStatus::DRAFT,  // Valeur par défaut
            ]);

        if (!$options['is_edit']) {
            $builder->add('save', SubmitType::class, [
                'label' => 'Create Event',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'is_edit' => false,
        ]);
    }
}
