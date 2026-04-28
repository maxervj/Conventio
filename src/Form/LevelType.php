<?php

namespace App\Form;

use App\Entity\Level;
use Symfony\Component\Form\AbstractType;
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Level::class,
        ]);
    }
}
