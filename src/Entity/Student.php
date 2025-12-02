<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends User
{
    #[ORM\Column(length: 255)]
    private ?string $personalMail = null;

    public function getPersonalMail(): ?string
    {
        return $this->personalMail;
    }

    public function setPersonalMail(string $personalMail): static
    {
        $this->personalMail = $personalMail;

        return $this;
    }
}
