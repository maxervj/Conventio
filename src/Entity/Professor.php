<?php

namespace App\Entity;

use App\Repository\ProfessorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessorRepository::class)]
class Professor extends User
{
    /**
     * @var Collection<int, Level>
     */
    #[ORM\ManyToMany(targetEntity: Level::class, inversedBy: 'teachers')]
    #[ORM\JoinTable(name: 'professor_taught_levels')]
    private Collection $taughtLevels;

    #[ORM\ManyToOne(targetEntity: Level::class, inversedBy: 'referentProfessor')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Level $referentLevel = null;

    public function __construct()
    {
        $this->taughtLevels = new ArrayCollection();
    }

    /**
     * @return Collection<int, Level>
     */
    public function getTaughtLevels(): Collection
    {
        return $this->taughtLevels;
    }

    public function addTaughtLevel(Level $level): static
    {
        if (!$this->taughtLevels->contains($level)) {
            $this->taughtLevels->add($level);
        }

        return $this;
    }

    public function removeTaughtLevel(Level $level): static
    {
        $this->taughtLevels->removeElement($level);

        return $this;
    }

    public function getReferentLevel(): ?Level
    {
        return $this->referentLevel;
    }

    public function setReferentLevel(?Level $referentLevel): static
    {
        $this->referentLevel = $referentLevel;

        return $this;
    }
}
