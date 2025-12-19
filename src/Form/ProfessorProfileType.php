<?php

namespace App\Form;

use App\Entity\Level;
use App\Entity\Professor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfessorProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('taughtLevels', EntityType::class, [
                'class' => Level::class,
                'choice_label' => 'LevelName',
                'label' => 'Classes que vous enseignez',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'help' => 'Sélectionnez les classes dans lesquelles vous enseignez',
            ])
            ->add('referentLevel', EntityType::class, [
                'class' => Level::class,
                'choice_label' => 'LevelName',
                'label' => 'Classe dont vous êtes référent',
                'required' => false,
                'placeholder' => 'Aucune classe référente',
                'help' => 'Sélectionnez la classe dont vous êtes le professeur référent',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Professor::class,
        ]);
    }
}
