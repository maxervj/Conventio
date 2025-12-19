<?php

namespace App\Form;

use App\Entity\Level;
use App\Entity\Student;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('levels', EntityType::class, [
                'class' => Level::class,
                'choice_label' => 'LevelName',
                'label' => 'Mes classes',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'help' => 'Sélectionnez vos classes',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}
