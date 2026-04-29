<?php

namespace App\Form;

use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CollectionRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => [
                    'placeholder' => 'Ex: Entreprise SARL',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir le nom de l\'entreprise.'),
                    new Assert\Length(max: 255, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')
                ]
            ])
            ->add('contactName', TextType::class, [
                'label' => 'Nom de la personne contact entreprise',
                'attr' => [
                    'placeholder' => 'Ex: Dupont Marie',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir le nom du contact.'),
                    new Assert\Length(max: 255, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')
                ]
            ])
            ->add('contactEmail', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'contact@entreprise.fr',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir l\'adresse email.'),
                    new Assert\Email(message: 'L\'adresse email n\'est pas valide.')
                ]
            ])
            ->add('internshipStartDate', DateType::class, [
                'label' => 'Date de début du stage',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir la date de début.'),
                ]
            ])
            ->add('internshipEndDate', DateType::class, [
                'label' => 'Date de fin du stage',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Veuillez saisir la date de fin.'),
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData() ?? [];
            $form = $event->getForm();

            // Essayer de récupérer l'étudiant depuis le parent ou les options
            $student = $form->getConfig()->getOption('student') ?? $form->getParent()?->getData();

            if ($student instanceof Student && $student->getLevel()) {
                $internshipDate = $student->getLevel()->getInternshipDate();
                if ($internshipDate) {
                    // Pré-remplissage des dates dans le formulaire
                    if (is_array($data)) {
                        $data['internshipStartDate'] = $internshipDate->getStartDate();
                        $data['internshipEndDate'] = $internshipDate->getEndDate();
                        $event->setData($data);
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'student' => null,
        ]);
    }
}
