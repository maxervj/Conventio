<?php

namespace App\Form;

use App\Entity\InternshipDate;
use App\Entity\Level;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LevelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('levelCode', TextType::class, [
                'label' => 'Code de la classe',
                'constraints' => [
                    new NotBlank(message: 'Le code de la classe est obligatoire.'),
                ],
                'attr' => ['placeholder' => 'Ex : SIO1'],
            ]);

        $builder
            ->add('levelName', TextType::class, [
                'label' => 'Intitulé de la classe',
                'constraints' => [
                    new NotBlank(message: 'Intitulé de la classe est obligatoire.'),
                ],
                'attr' => ['placeholder' => 'Ex : Services Informatiques aux Organisations 1ère année'],
            ])
        ;

        $builder
            ->add('internshipDateStart', DateType::class, [
                'label' => 'Date de début du stage',
                'constraints' => [
                    new NotBlank(message: 'La date de début du stage est obligatoire.'),
                ],
                'attr' => ['placeholder' => 'Ex : 01/05/2026'],
                'required' => false,
                'mapped' => false,
            ]);

        $builder
            ->add('internshipDateEnd', DateType::class, [
                'label' => 'Date de fin du stage',
                'constraints' => [
                    new NotBlank(message: 'La date de fin du stage est obligatoire.'),
                ],
                'attr' => ['placeholder' => 'Ex : 31/05/2026'],
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Level::class,
        ]);
    }
}
