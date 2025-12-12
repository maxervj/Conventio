<?php

namespace App\Entity;

use App\Repository\SignatureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SignatureRepository::class)]
class Signature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)] // Augmenté pour le chiffrement
    #[Assert\NotBlank(message: 'La civilité du proviseur est obligatoire')]
    #[Assert\Choice(choices: ['M.', 'Mme'], message: 'Veuillez choisir une civilité valide')]
    private ?string $civiliteProviseur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du proviseur est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $nomProviseur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom du proviseur est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $prenomProviseur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email du proviseur est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    private ?string $emailProviseur = null;

    #[ORM\Column(length: 500)] // Augmenté pour le chiffrement
    #[Assert\NotBlank(message: 'La civilité du DDF est obligatoire')]
    #[Assert\Choice(choices: ['M.', 'Mme'], message: 'Veuillez choisir une civilité valide')]
    private ?string $civiliteDDF = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du DDF est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $nomDDF = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom du DDF est obligatoire')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $prenomDDF = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email du DDF est obligatoire')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas valide')]
    private ?string $emailDDF = null;

    #[ORM\Column(length: 500)] // Augmenté pour le chiffrement
    #[Assert\NotBlank(message: 'Le téléphone du DDF est obligatoire')]
    #[Assert\Regex(
        pattern: '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/',
        message: 'Le numéro de téléphone n\'est pas valide (format français attendu)'
    )]
    private ?string $telDDF = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCiviliteProviseur(): ?string
    {
        return $this->civiliteProviseur;
    }

    public function setCiviliteProviseur(string $civiliteProviseur): static
    {
        $this->civiliteProviseur = $civiliteProviseur;

        return $this;
    }

    public function getNomProviseur(): ?string
    {
        return $this->nomProviseur;
    }

    public function setNomProviseur(string $nomProviseur): static
    {
        $this->nomProviseur = $nomProviseur;

        return $this;
    }

    public function getPrenomProviseur(): ?string
    {
        return $this->prenomProviseur;
    }

    public function setPrenomProviseur(string $prenomProviseur): static
    {
        $this->prenomProviseur = $prenomProviseur;

        return $this;
    }

    public function getEmailProviseur(): ?string
    {
        return $this->emailProviseur;
    }

    public function setEmailProviseur(string $emailProviseur): static
    {
        $this->emailProviseur = $emailProviseur;

        return $this;
    }

    public function getCiviliteDDF(): ?string
    {
        return $this->civiliteDDF;
    }

    public function setCiviliteDDF(string $civiliteDDF): static
    {
        $this->civiliteDDF = $civiliteDDF;

        return $this;
    }

    public function getNomDDF(): ?string
    {
        return $this->nomDDF;
    }

    public function setNomDDF(string $nomDDF): static
    {
        $this->nomDDF = $nomDDF;

        return $this;
    }

    public function getPrenomDDF(): ?string
    {
        return $this->prenomDDF;
    }

    public function setPrenomDDF(string $prenomDDF): static
    {
        $this->prenomDDF = $prenomDDF;

        return $this;
    }

    public function getEmailDDF(): ?string
    {
        return $this->emailDDF;
    }

    public function setEmailDDF(string $emailDDF): static
    {
        $this->emailDDF = $emailDDF;

        return $this;
    }

    public function getTelDDF(): ?string
    {
        return $this->telDDF;
    }

    public function setTelDDF(string $telDDF): static
    {
        $this->telDDF = $telDDF;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
