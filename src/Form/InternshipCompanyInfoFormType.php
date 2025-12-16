<?php

namespace App\Form;

use App\Entity\InternshipCompanyInfo;
use App\Validator\FrenchPhone;
use App\Validator\Siret;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InternshipCompanyInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Organization information
            ->add('companyName', TextType::class, [
                'label' => 'form.company.name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'form.company.address',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                ],
            ])
            ->add('addressComplement', TextType::class, [
                'label' => 'form.company.address_complement',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'form.company.postal_code',
                'required' => true,
                'attr' => ['class' => 'form-control', 'maxlength' => 10],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'This field is required']),
                    new Assert\Length(['max' => 10]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'form.company.city',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'form.company.country',
                'required' => true,
                'choices' => $this->getCountries(),
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                ],
            ])

            // Responsible person
            ->add('responsibleLastName', TextType::class, [
                'label' => 'form.responsible.last_name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('responsibleFirstName', TextType::class, [
                'label' => 'form.responsible.first_name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('responsibleFunction', TextType::class, [
                'label' => 'form.responsible.function',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('landlinePhone', TextType::class, [
                'label' => 'form.responsible.landline_phone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '01 23 45 67 89'],
                'constraints' => [
                    new FrenchPhone(['allowMobile' => false]),
                ],
            ])
            ->add('mobilePhone', TextType::class, [
                'label' => 'form.responsible.mobile_phone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '06 12 34 56 78'],
                'constraints' => [
                    new FrenchPhone(['allowLandline' => false]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.responsible.email',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Email(['message' => 'validation.email_format']),
                ],
            ])
            ->add('website', UrlType::class, [
                'label' => 'form.company.website',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://example.com'],
            ])
            ->add('siret', TextType::class, [
                'label' => 'form.company.siret',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => '123 456 789 01234'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'This field is required']),
                    new Siret(),
                ],
            ])
            ->add('insurerName', TextType::class, [
                'label' => 'form.company.insurer_name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('insurerReference', TextType::class, [
                'label' => 'form.company.insurer_reference',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])

            // Internship location (if different)
            ->add('internshipAddress', TextType::class, [
                'label' => 'form.internship_location.address',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('internshipPostalCode', TextType::class, [
                'label' => 'form.internship_location.postal_code',
                'required' => false,
                'attr' => ['class' => 'form-control', 'maxlength' => 10],
            ])
            ->add('internshipCity', TextType::class, [
                'label' => 'form.internship_location.city',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('internshipCountry', ChoiceType::class, [
                'label' => 'form.internship_location.country',
                'required' => false,
                'choices' => $this->getCountries(),
                'attr' => ['class' => 'form-select'],
            ])
            ->add('internshipPhone', TextType::class, [
                'label' => 'form.internship_location.phone',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '01 23 45 67 89'],
                'constraints' => [
                    new FrenchPhone(),
                ],
            ])

            // Supervisor information
            ->add('supervisorLastName', TextType::class, [
                'label' => 'form.supervisor.last_name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('supervisorFirstName', TextType::class, [
                'label' => 'form.supervisor.first_name',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('supervisorFunction', TextType::class, [
                'label' => 'form.supervisor.function',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('supervisorPhone', TextType::class, [
                'label' => 'form.supervisor.phone',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => '01 23 45 67 89'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'This field is required']),
                    new FrenchPhone(),
                ],
            ])
            ->add('supervisorEmail', EmailType::class, [
                'label' => 'form.supervisor.email',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                    new Assert\Email(['message' => 'validation.email_format']),
                ],
            ])

            // Travel question
            ->add('hasTravel', ChoiceType::class, [
                'label' => 'form.travel.has_travel',
                'required' => true,
                'choices' => [
                    'form.choice.yes' => true,
                    'form.choice.no' => false,
                ],
                'expanded' => true,
                'data' => false,
            ])

            // Cost coverage
            ->add('coversTransportCosts', ChoiceType::class, [
                'label' => 'form.costs.transport',
                'required' => true,
                'choices' => [
                    'form.choice.yes' => true,
                    'form.choice.no' => false,
                ],
                'expanded' => true,
                'data' => false,
            ])
            ->add('transportCostsDetails', TextareaType::class, [
                'label' => 'form.costs.transport_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('coversMealCosts', ChoiceType::class, [
                'label' => 'form.costs.meal',
                'required' => true,
                'choices' => [
                    'form.choice.yes' => true,
                    'form.choice.no' => false,
                ],
                'expanded' => true,
                'data' => false,
            ])
            ->add('mealCostsDetails', TextareaType::class, [
                'label' => 'form.costs.meal_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('coversAccommodationCosts', ChoiceType::class, [
                'label' => 'form.costs.accommodation',
                'required' => true,
                'choices' => [
                    'form.choice.yes' => true,
                    'form.choice.no' => false,
                ],
                'expanded' => true,
                'data' => false,
            ])
            ->add('accommodationCostsDetails', TextareaType::class, [
                'label' => 'form.costs.accommodation_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('providesGratification', ChoiceType::class, [
                'label' => 'form.costs.gratification',
                'required' => true,
                'choices' => [
                    'form.choice.yes' => true,
                    'form.choice.no' => false,
                ],
                'expanded' => true,
                'data' => false,
            ])
            ->add('gratificationDetails', TextareaType::class, [
                'label' => 'form.costs.gratification_details',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])

            // Professional activities
            ->add('plannedActivities', TextareaType::class, [
                'label' => 'form.activities.planned',
                'required' => true,
                'attr' => ['class' => 'form-control', 'rows' => 8],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'company_info.required']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InternshipCompanyInfo::class,
            'translation_domain' => 'messages',
        ]);
    }

    private function getCountries(): array
    {
        return [
            'France' => 'France',
            'Allemagne / Germany / Alemania' => 'Germany',
            'Espagne / Spain / España' => 'Spain',
            'Italie / Italy / Italia' => 'Italy',
            'Royaume-Uni / United Kingdom / Reino Unido' => 'United Kingdom',
            'Belgique / Belgium / Bélgica' => 'Belgium',
            'Suisse / Switzerland / Suiza' => 'Switzerland',
            'Luxembourg' => 'Luxembourg',
            'Pays-Bas / Netherlands / Países Bajos' => 'Netherlands',
            'Portugal' => 'Portugal',
            'Autre / Other / Otro' => 'Other',
        ];
    }
}
