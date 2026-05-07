<?php

namespace App\Form;

use App\Entity\DDFPTSettings;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DDFPTSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('requireYousignApproval', CheckboxType::class, [
                'label' => 'Activer l\'approbation Yousign',
                'required' => false,
                'help' => 'Recevrez une demande d\'approbation avant l\'envoi des signatures',
            ])
            ->add('approvalEmail', EmailType::class, [
                'label' => 'Email pour les approbations',
                'required' => false,
                'help' => 'Email qui recevra les demandes d\'approbation',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DDFPTSettings::class,
        ]);
    }
}
