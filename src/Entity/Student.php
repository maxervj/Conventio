<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends User
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $personalEmail = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $verificationToken = null;

    /**
     * @var Collection<int, Level>
     */
    #[ORM\ManyToMany(targetEntity: Level::class, inversedBy: 'students')]
    #[ORM\JoinTable(name: 'student_level')]
    private Collection $levels;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Level $Level = null;

    public function __construct()
    {
        $this->levels = new ArrayCollection();
    }

    public function getPersonalEmail(): ?string
    {
        return $this->personalEmail;
    }

    public function setPersonalEmail(?string $personalEmail): static
    {
        $this->personalEmail = $personalEmail;

        return $this;
    }

    /**
     * @return Collection<int, Level>
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): static
    {
        if (!$this->levels->contains($level)) {
            $this->levels->add($level);
        }

        return $this;
    }

    public function removeLevel(Level $level): static
    {
        $this->levels->removeElement($level);

        return $this;
    }



    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->Level;
    }

    public function setLevel(?Level $Level): static
    {
        $this->Level = $Level;

        return $this;
    }
}
