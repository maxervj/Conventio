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

    public function __construct()
    {
        $this->students = new ArrayCollection();
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
}
