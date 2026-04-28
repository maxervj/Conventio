<?php

namespace App\Entity;

use App\Repository\ConventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConventionRepository::class)]
#[ORM\Table(name: 'convention')]
class Convention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Student $student = null;

    #[ORM\ManyToOne(targetEntity: Professor::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Professor $referentProfessor = null;

    #[ORM\OneToOne(targetEntity: InternshipCompanyInfo::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?InternshipCompanyInfo $companyInfo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $status = 'draft'; // draft, pending_validation, validated, signed, completed

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $validatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $signedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $completedAt = null;

    // Données du document PDF
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $documentPath = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $documentHash = null;

    // Signatures électroniques
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $studentSignatureToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $studentSignedAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $companySignatureToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $companySignedAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $schoolSignatureToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $schoolSignedAt = null;

    // Notes et commentaires
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $validationNotes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    // Yousign
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $yousignRequestId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $yousignDocumentId = null;

    // draft | ongoing | done | expired | cancelled
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $yousignStatus = null;

    /**
     * @var Collection<int, ConventionDate>
     */
    #[ORM\OneToMany(targetEntity: ConventionDate::class, mappedBy: 'convention')]
    private Collection $conventionDates;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = 'draft';
        $this->conventionDates = new ArrayCollection();
    }

    public function getYousignRequestId(): ?string
    {
        return $this->yousignRequestId;
    }

    public function setYousignRequestId(?string $yousignRequestId): static
    {
        $this->yousignRequestId = $yousignRequestId;
        return $this;
    }

    public function getYousignDocumentId(): ?string
    {
        return $this->yousignDocumentId;
    }

    public function setYousignDocumentId(?string $yousignDocumentId): static
    {
        $this->yousignDocumentId = $yousignDocumentId;
        return $this;
    }

    public function getYousignStatus(): ?string
    {
        return $this->yousignStatus;
    }

    public function setYousignStatus(?string $yousignStatus): static
    {
        $this->yousignStatus = $yousignStatus;
        return $this;
    }

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
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

    public function getReferentProfessor(): ?Professor
    {
        return $this->referentProfessor;
    }

    public function setReferentProfessor(?Professor $referentProfessor): static
    {
        $this->referentProfessor = $referentProfessor;
        return $this;
    }

    public function getCompanyInfo(): ?InternshipCompanyInfo
    {
        return $this->companyInfo;
    }

    public function setCompanyInfo(?InternshipCompanyInfo $companyInfo): static
    {
        $this->companyInfo = $companyInfo;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
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

    public function getValidatedAt(): ?\DateTime
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTime $validatedAt): static
    {
        $this->validatedAt = $validatedAt;
        return $this;
    }

    public function getSignedAt(): ?\DateTime
    {
        return $this->signedAt;
    }

    public function setSignedAt(?\DateTime $signedAt): static
    {
        $this->signedAt = $signedAt;
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

    public function getDocumentPath(): ?string
    {
        return $this->documentPath;
    }

    public function setDocumentPath(?string $documentPath): static
    {
        $this->documentPath = $documentPath;
        return $this;
    }

    public function getDocumentHash(): ?string
    {
        return $this->documentHash;
    }

    public function setDocumentHash(?string $documentHash): static
    {
        $this->documentHash = $documentHash;
        return $this;
    }

    public function getStudentSignatureToken(): ?string
    {
        return $this->studentSignatureToken;
    }

    public function setStudentSignatureToken(?string $studentSignatureToken): static
    {
        $this->studentSignatureToken = $studentSignatureToken;
        return $this;
    }

    public function getStudentSignedAt(): ?\DateTime
    {
        return $this->studentSignedAt;
    }

    public function setStudentSignedAt(?\DateTime $studentSignedAt): static
    {
        $this->studentSignedAt = $studentSignedAt;
        return $this;
    }

    public function getCompanySignatureToken(): ?string
    {
        return $this->companySignatureToken;
    }

    public function setCompanySignatureToken(?string $companySignatureToken): static
    {
        $this->companySignatureToken = $companySignatureToken;
        return $this;
    }

    public function getCompanySignedAt(): ?\DateTime
    {
        return $this->companySignedAt;
    }

    public function setCompanySignedAt(?\DateTime $companySignedAt): static
    {
        $this->companySignedAt = $companySignedAt;
        return $this;
    }

    public function getSchoolSignatureToken(): ?string
    {
        return $this->schoolSignatureToken;
    }

    public function setSchoolSignatureToken(?string $schoolSignatureToken): static
    {
        $this->schoolSignatureToken = $schoolSignatureToken;
        return $this;
    }

    public function getSchoolSignedAt(): ?\DateTime
    {
        return $this->schoolSignedAt;
    }

    public function setSchoolSignedAt(?\DateTime $schoolSignedAt): static
    {
        $this->schoolSignedAt = $schoolSignedAt;
        return $this;
    }

    public function getValidationNotes(): ?string
    {
        return $this->validationNotes;
    }

    public function setValidationNotes(?string $validationNotes): static
    {
        $this->validationNotes = $validationNotes;
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

    // Helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingValidation(): bool
    {
        return $this->status === 'pending_validation';
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPendingSignature(): bool
    {
        return $this->status === 'pending_signature';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isFullySigned(): bool
    {
        return $this->studentSignedAt !== null
            && $this->companySignedAt !== null
            && $this->schoolSignedAt !== null;
    }

    /**
     * @return Collection<int, ConventionDate>
     */
    public function getConventionDates(): Collection
    {
        return $this->conventionDates;
    }

    public function addConventionDate(ConventionDate $conventionDate): static
    {
        if (!$this->conventionDates->contains($conventionDate)) {
            $this->conventionDates->add($conventionDate);
            $conventionDate->setConvention($this);
        }

        return $this;
    }

    public function removeConventionDate(ConventionDate $conventionDate): static
    {
        if ($this->conventionDates->removeElement($conventionDate)) {
            // set the owning side to null (unless already changed)
            if ($conventionDate->getConvention() === $this) {
                $conventionDate->setConvention(null);
            }
        }

        return $this;
    }

    /**
     * Crée automatiquement une ContractDate à partir du Level->InternshipDate
     * si aucune ContractDate n'existe encore.
     */
    public function initializeContractDates(): void
    {
        // éviter doublons
        if (count($this->contractDates) > 0) {
            return;
        }

        if (!property_exists($this, 'student')) {
            return;
        }

        /** @var Student|null $student */
        $student = $this->student ?? null;
        if (!$student || !$student->getLevel()) {
            return;
        }

        $level = $student->getLevel();
        $internshipDate = $level->getInternshipDate();
        if (!$internshipDate) {
            return;
        }

        $contractDate = new ContractDate();
        $contractDate->setStartDate($internshipDate->getStartDate());
        $contractDate->setEndDate($internshipDate->getEndDate());
        $this->addContractDate($contractDate);
    }
}
