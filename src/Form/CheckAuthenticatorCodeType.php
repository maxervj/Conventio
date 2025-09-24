<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckAuthenticatorCodeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('authenticatorCode', IntegerType::class, [
                'label' => 'Entrez le code de votre application d\'authentification',
                'attr' => [
                    'placeholder' => 'Entrez le code de votre application d\'authentification',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
