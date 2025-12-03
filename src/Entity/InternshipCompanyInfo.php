<?php

namespace App\Entity;

use App\Repository\InternshipCompanyInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InternshipCompanyInfoRepository::class)]
class InternshipCompanyInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $token = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isCompleted = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $completedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $expiresAt = null;

    // Organization information
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $companyName = null;

    #[ORM\Column(type: 'text')]
    private ?string $address = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $addressComplement = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $postalCode = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $country = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $responsibleLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $responsibleFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $responsibleFunction = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $landlinePhone = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $mobilePhone = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: 'string', length: 14)]
    private ?string $siret = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $insurerName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $insurerReference = null;

    // Internship location (if different)
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $internshipAddress = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $internshipPostalCode = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $internshipCity = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $internshipCountry = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $internshipPhone = null;

    // Supervisor information
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $supervisorLastName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $supervisorFirstName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $supervisorFunction = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $supervisorPhone = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $supervisorEmail = null;

    // Travel during internship
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $hasTravel = false;

    // Cost coverage
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $coversTransportCosts = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $transportCostsDetails = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $coversMealCosts = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mealCostsDetails = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $coversAccommodationCosts = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $accommodationCostsDetails = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $providesGratification = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $gratificationDetails = null;

    // Work schedule (stored as JSON)
    #[ORM\Column(type: Types::JSON)]
    private array $workSchedule = [];

    // Professional activities
    #[ORM\Column(type: Types::TEXT)]
    private ?string $plannedActivities = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->expiresAt = (new \DateTime())->modify('+30 days');
        $this->token = bin2hex(random_bytes(32));
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;
        return $this;
    }

    public function getCompletedAt(): ?\DateTime
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTime $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTime $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isExpired(): bool
    {
        if (!$this->expiresAt) {
            return false;
        }
        return new \DateTime() > $this->expiresAt;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getAddressComplement(): ?string
    {
        return $this->addressComplement;
    }

    public function setAddressComplement(?string $addressComplement): static
    {
        $this->addressComplement = $addressComplement;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getResponsibleLastName(): ?string
    {
        return $this->responsibleLastName;
    }

    public function setResponsibleLastName(string $responsibleLastName): static
    {
        $this->responsibleLastName = $responsibleLastName;
        return $this;
    }

    public function getResponsibleFirstName(): ?string
    {
        return $this->responsibleFirstName;
    }

    public function setResponsibleFirstName(string $responsibleFirstName): static
    {
        $this->responsibleFirstName = $responsibleFirstName;
        return $this;
    }

    public function getResponsibleFunction(): ?string
    {
        return $this->responsibleFunction;
    }

    public function setResponsibleFunction(string $responsibleFunction): static
    {
        $this->responsibleFunction = $responsibleFunction;
        return $this;
    }

    public function getLandlinePhone(): ?string
    {
        return $this->landlinePhone;
    }

    public function setLandlinePhone(?string $landlinePhone): static
    {
        $this->landlinePhone = $landlinePhone;
        return $this;
    }

    public function getMobilePhone(): ?string
    {
        return $this->mobilePhone;
    }

    public function setMobilePhone(?string $mobilePhone): static
    {
        $this->mobilePhone = $mobilePhone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;
        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;
        return $this;
    }

    public function getInsurerName(): ?string
    {
        return $this->insurerName;
    }

    public function setInsurerName(string $insurerName): static
    {
        $this->insurerName = $insurerName;
        return $this;
    }

    public function getInsurerReference(): ?string
    {
        return $this->insurerReference;
    }

    public function setInsurerReference(string $insurerReference): static
    {
        $this->insurerReference = $insurerReference;
        return $this;
    }

    public function getInternshipAddress(): ?string
    {
        return $this->internshipAddress;
    }

    public function setInternshipAddress(?string $internshipAddress): static
    {
        $this->internshipAddress = $internshipAddress;
        return $this;
    }

    public function getInternshipPostalCode(): ?string
    {
        return $this->internshipPostalCode;
    }

    public function setInternshipPostalCode(?string $internshipPostalCode): static
    {
        $this->internshipPostalCode = $internshipPostalCode;
        return $this;
    }

    public function getInternshipCity(): ?string
    {
        return $this->internshipCity;
    }

    public function setInternshipCity(?string $internshipCity): static
    {
        $this->internshipCity = $internshipCity;
        return $this;
    }

    public function getInternshipCountry(): ?string
    {
        return $this->internshipCountry;
    }

    public function setInternshipCountry(?string $internshipCountry): static
    {
        $this->internshipCountry = $internshipCountry;
        return $this;
    }

    public function getInternshipPhone(): ?string
    {
        return $this->internshipPhone;
    }

    public function setInternshipPhone(?string $internshipPhone): static
    {
        $this->internshipPhone = $internshipPhone;
        return $this;
    }

    public function getSupervisorLastName(): ?string
    {
        return $this->supervisorLastName;
    }

    public function setSupervisorLastName(string $supervisorLastName): static
    {
        $this->supervisorLastName = $supervisorLastName;
        return $this;
    }

    public function getSupervisorFirstName(): ?string
    {
        return $this->supervisorFirstName;
    }

    public function setSupervisorFirstName(string $supervisorFirstName): static
    {
        $this->supervisorFirstName = $supervisorFirstName;
        return $this;
    }

    public function getSupervisorFunction(): ?string
    {
        return $this->supervisorFunction;
    }

    public function setSupervisorFunction(string $supervisorFunction): static
    {
        $this->supervisorFunction = $supervisorFunction;
        return $this;
    }

    public function getSupervisorPhone(): ?string
    {
        return $this->supervisorPhone;
    }

    public function setSupervisorPhone(string $supervisorPhone): static
    {
        $this->supervisorPhone = $supervisorPhone;
        return $this;
    }

    public function getSupervisorEmail(): ?string
    {
        return $this->supervisorEmail;
    }

    public function setSupervisorEmail(string $supervisorEmail): static
    {
        $this->supervisorEmail = $supervisorEmail;
        return $this;
    }

    public function hasTravel(): bool
    {
        return $this->hasTravel;
    }

    public function setHasTravel(bool $hasTravel): static
    {
        $this->hasTravel = $hasTravel;
        return $this;
    }

    public function coversTransportCosts(): bool
    {
        return $this->coversTransportCosts;
    }

    public function setCoversTransportCosts(bool $coversTransportCosts): static
    {
        $this->coversTransportCosts = $coversTransportCosts;
        return $this;
    }

    public function getTransportCostsDetails(): ?string
    {
        return $this->transportCostsDetails;
    }

    public function setTransportCostsDetails(?string $transportCostsDetails): static
    {
        $this->transportCostsDetails = $transportCostsDetails;
        return $this;
    }

    public function coversMealCosts(): bool
    {
        return $this->coversMealCosts;
    }

    public function setCoversMealCosts(bool $coversMealCosts): static
    {
        $this->coversMealCosts = $coversMealCosts;
        return $this;
    }

    public function getMealCostsDetails(): ?string
    {
        return $this->mealCostsDetails;
    }

    public function setMealCostsDetails(?string $mealCostsDetails): static
    {
        $this->mealCostsDetails = $mealCostsDetails;
        return $this;
    }

    public function coversAccommodationCosts(): bool
    {
        return $this->coversAccommodationCosts;
    }

    public function setCoversAccommodationCosts(bool $coversAccommodationCosts): static
    {
        $this->coversAccommodationCosts = $coversAccommodationCosts;
        return $this;
    }

    public function getAccommodationCostsDetails(): ?string
    {
        return $this->accommodationCostsDetails;
    }

    public function setAccommodationCostsDetails(?string $accommodationCostsDetails): static
    {
        $this->accommodationCostsDetails = $accommodationCostsDetails;
        return $this;
    }

    public function providesGratification(): bool
    {
        return $this->providesGratification;
    }

    public function setProvidesGratification(bool $providesGratification): static
    {
        $this->providesGratification = $providesGratification;
        return $this;
    }

    public function getGratificationDetails(): ?string
    {
        return $this->gratificationDetails;
    }

    public function setGratificationDetails(?string $gratificationDetails): static
    {
        $this->gratificationDetails = $gratificationDetails;
        return $this;
    }

    public function getWorkSchedule(): array
    {
        return $this->workSchedule;
    }

    public function setWorkSchedule(array $workSchedule): static
    {
        $this->workSchedule = $workSchedule;
        return $this;
    }

    public function getPlannedActivities(): ?string
    {
        return $this->plannedActivities;
    }

    public function setPlannedActivities(string $plannedActivities): static
    {
        $this->plannedActivities = $plannedActivities;
        return $this;
    }
}
