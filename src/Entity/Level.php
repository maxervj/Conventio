<?php

namespace App\Entity;

use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_level = null;

    #[ORM\Column]
    private ?int $LevelCode = null;

    #[ORM\Column(length: 255)]
    private ?string $LevelName = null;

    /**
     * @var Collection<int, Student>
     */
    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: 'levels')]
    private Collection $students;

    /**
     * @var Collection<int, Professor>
     */
    #[ORM\ManyToMany(targetEntity: Professor::class, mappedBy: 'taughtLevels')]
    private Collection $teachers;

    /**
     * @var Collection<int, Professor>
     */
    #[ORM\OneToMany(targetEntity: Professor::class, mappedBy: 'referentLevel')]
    private Collection $referentProfessors;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->teachers = new ArrayCollection();
        $this->referentProfessors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdLevel(): ?int
    {
        return $this->id_level;
    }

    public function setIdLevel(int $id_level): static
    {
        $this->id_level = $id_level;

        return $this;
    }

    public function getLevelCode(): ?int
    {
        return $this->LevelCode;
    }

    public function setLevelCode(int $LevelCode): static
    {
        $this->LevelCode = $LevelCode;

        return $this;
    }

    public function getLevelName(): ?string
    {
        return $this->LevelName;
    }

    public function setLevelName(string $LevelName): static
    {
        $this->LevelName = $LevelName;

        return $this;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->addLevel($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): static
    {
        if ($this->students->removeElement($student)) {
            $student->removeLevel($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Professor>
     */
    public function getTeachers(): Collection
    {
        return $this->teachers;
    }

    public function addTeacher(Professor $professor): static
    {
        if (!$this->teachers->contains($professor)) {
            $this->teachers->add($professor);
            $professor->addTaughtLevel($this);
        }

        return $this;
    }

    public function removeTeacher(Professor $professor): static
    {
        if ($this->teachers->removeElement($professor)) {
            $professor->removeTaughtLevel($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Professor>
     */
    public function getReferentProfessors(): Collection
    {
        return $this->referentProfessors;
    }

    public function addReferentProfessor(Professor $professor): static
    {
        if (!$this->referentProfessors->contains($professor)) {
            $this->referentProfessors->add($professor);
            $professor->setReferentLevel($this);
        }

        return $this;
    }

    public function removeReferentProfessor(Professor $professor): static
    {
        if ($this->referentProfessors->removeElement($professor)) {
            if ($professor->getReferentLevel() === $this) {
                $professor->setReferentLevel(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->LevelName ?? '';
    }
}
