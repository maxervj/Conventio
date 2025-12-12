<?php

namespace App\Form;

use App\Entity\Signature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('civiliteProviseur', ChoiceType::class, [
                'choices' => [
                    'Monsieur' => 'M.',
                    'Madame' => 'Mme',
                ],
                'label' => 'Civilité du proviseur',
                'placeholder' => 'Sélectionnez une civilité',
            ])
            ->add('nomProviseur', TextType::class, [
                'label' => 'Nom du proviseur',
                'attr' => ['placeholder' => 'Dupont'],
            ])
            ->add('prenomProviseur', TextType::class, [
                'label' => 'Prénom du proviseur',
                'attr' => ['placeholder' => 'Jean'],
            ])
            ->add('emailProviseur', EmailType::class, [
                'label' => 'Email du proviseur',
                'attr' => ['placeholder' => 'proviseur@lycee-faure.fr'],
            ])
            ->add('civiliteDDF', ChoiceType::class, [
                'choices' => [
                    'Monsieur' => 'M.',
                    'Madame' => 'Mme',
                ],
                'label' => 'Civilité du Directeur Délégué aux Formations',
                'placeholder' => 'Sélectionnez une civilité',
            ])
            ->add('nomDDF', TextType::class, [
                'label' => 'Nom du DDF',
            ])
            ->add('prenomDDF', TextType::class, [
                'label' => 'Prénom du DDF',
            ])
            ->add('emailDDF', EmailType::class, [
                'label' => 'Email du DDF',
            ])
            ->add('telDDF', TelType::class, [
                'label' => 'Téléphone du DDF',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signature::class,
        ]);
    }
}
