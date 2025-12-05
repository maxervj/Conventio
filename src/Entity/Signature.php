<?php

namespace App\Entity;

use App\Repository\SignatureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignatureRepository::class)]
class Signature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $civiliteProviseur = null;

    #[ORM\Column(length: 255)]
    private ?string $nomProviseur = null;

    #[ORM\Column(length: 255)]
    private ?string $prenomProviseur = null;

    #[ORM\Column(length: 255)]
    private ?string $emailProviseur = null;

    #[ORM\Column(length: 255)]
    private ?string $civiliteDDF = null;

    #[ORM\Column(length: 255)]
    private ?string $nomDDF = null;

    #[ORM\Column(length: 255)]
    private ?string $prenomDDF = null;

    #[ORM\Column(length: 255)]
    private ?string $emailDDF = null;

    #[ORM\Column(length: 255)]
    private ?string $telDDF = null;

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
}
