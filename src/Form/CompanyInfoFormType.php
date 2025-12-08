<?php

namespace App\Form;

use App\Entity\InternshipCompanyInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CompanyInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Organization information
            ->add('companyName', TextType::class, [
                'label' => 'company_info.company_name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'company_info.address',
                'required' => true,
                'attr' => ['class' => 'form-control', 'rows' => 3],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('addressComplement', TextType::class, [
                'label' => 'company_info.address_complement',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'company_info.postal_code',
                'required' => true,
                'attr' => ['class' => 'form-control', 'maxlength' => 10],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'company_info.city',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'company_info.country',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('responsibleLastName', TextType::class, [
                'label' => 'company_info.responsible_lastname',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('responsibleFirstName', TextType::class, [
                'label' => 'company_info.responsible_firstname',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('responsibleFunction', TextType::class, [
                'label' => 'company_info.responsible_function',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('landlinePhone', TextType::class, [
                'label' => 'company_info.landline_phone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'maxlength' => 20]
            ])
            ->add('mobilePhone', TextType::class, [
                'label' => 'company_info.mobile_phone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'maxlength' => 20],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[\d\s\+\-\(\)]+$/',
                        'message' => 'validation.phone_format'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'company_info.email',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Email(['message' => 'validation.email_format', 'mode' => 'html5'])
                ]
            ])
            ->add('website', UrlType::class, [
                'label' => 'company_info.website',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('siret', TextType::class, [
                'label' => 'company_info.siret',
                'required' => true,
                'attr' => ['class' => 'form-control', 'maxlength' => 14],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Regex([
                        'pattern' => '/^\d{14}$/',
                        'message' => 'validation.siret_format'
                    ])
                ]
            ])
            ->add('insurerName', TextType::class, [
                'label' => 'company_info.insurer_name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('insurerReference', TextType::class, [
                'label' => 'company_info.insurer_reference',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])

            // Internship location (if different)
            ->add('internshipAddress', TextareaType::class, [
                'label' => 'company_info.internship_address',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('internshipPostalCode', TextType::class, [
                'label' => 'company_info.internship_postal_code',
                'required' => false,
                'attr' => ['class' => 'form-control', 'maxlength' => 10]
            ])
            ->add('internshipCity', TextType::class, [
                'label' => 'company_info.internship_city',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('internshipCountry', TextType::class, [
                'label' => 'company_info.internship_country',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('internshipPhone', TextType::class, [
                'label' => 'company_info.internship_phone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'maxlength' => 20]
            ])

            // Supervisor information
            ->add('supervisorLastName', TextType::class, [
                'label' => 'company_info.supervisor_lastname',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('supervisorFirstName', TextType::class, [
                'label' => 'company_info.supervisor_firstname',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('supervisorFunction', TextType::class, [
                'label' => 'company_info.supervisor_function',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('supervisorPhone', TextType::class, [
                'label' => 'company_info.supervisor_phone',
                'required' => true,
                'attr' => ['class' => 'form-control', 'maxlength' => 20],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ])
            ->add('supervisorEmail', EmailType::class, [
                'label' => 'company_info.supervisor_email',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Email(['message' => 'validation.email_format', 'mode' => 'html5'])
                ]
            ])

            // Questionnaire
            ->add('hasTravel', ChoiceType::class, [
                'label' => 'company_info.has_travel',
                'required' => true,
                'choices' => [
                    'company_info.yes' => true,
                    'company_info.no' => false,
                ],
                'expanded' => true,
                'attr' => ['class' => 'form-check']
            ])

            // Cost coverage
            ->add('coversTransportCosts', ChoiceType::class, [
                'label' => 'company_info.covers_transport',
                'required' => true,
                'choices' => [
                    'company_info.yes' => true,
                    'company_info.no' => false,
                ],
                'expanded' => true,
                'attr' => ['class' => 'form-check']
            ])
            ->add('transportCostsDetails', TextareaType::class, [
                'label' => 'company_info.transport_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
            ->add('coversMealCosts', ChoiceType::class, [
                'label' => 'company_info.covers_meals',
                'required' => true,
                'choices' => [
                    'company_info.yes' => true,
                    'company_info.no' => false,
                ],
                'expanded' => true,
                'attr' => ['class' => 'form-check']
            ])
            ->add('mealCostsDetails', TextareaType::class, [
                'label' => 'company_info.meal_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
            ->add('coversAccommodationCosts', ChoiceType::class, [
                'label' => 'company_info.covers_accommodation',
                'required' => true,
                'choices' => [
                    'company_info.yes' => true,
                    'company_info.no' => false,
                ],
                'expanded' => true,
                'attr' => ['class' => 'form-check']
            ])
            ->add('accommodationCostsDetails', TextareaType::class, [
                'label' => 'company_info.accommodation_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])
            ->add('providesGratification', ChoiceType::class, [
                'label' => 'company_info.provides_gratification',
                'required' => true,
                'choices' => [
                    'company_info.yes' => true,
                    'company_info.no' => false,
                ],
                'expanded' => true,
                'attr' => ['class' => 'form-check']
            ])
            ->add('gratificationDetails', TextareaType::class, [
                'label' => 'company_info.gratification_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2]
            ])

            // Planned activities
            ->add('plannedActivities', TextareaType::class, [
                'label' => 'company_info.planned_activities',
                'required' => true,
                'attr' => ['class' => 'form-control', 'rows' => 6],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InternshipCompanyInfo::class,
            'translation_domain' => 'messages',
        ]);
    }
}
