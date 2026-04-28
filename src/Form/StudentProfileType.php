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
                'choice_label' => 'levelCode',
                'label' => 'Classe dont vous êtes',
                'required' => false,
                'placeholder' => 'Sélectionnez votre classe',
                'help' => 'Sélectionnez votre classe',
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
