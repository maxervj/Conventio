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
     * @var Collection<int, Contract>
     */
    #[ORM\OneToMany(targetEntity: Contract::class, mappedBy: 'coordinator')]
    private Collection $coordinatedContracts;

    public function __construct()
    {
        $this->coordinatedContracts = new ArrayCollection();
    }

    /**
     * @return Collection<int, Contract>
     */
    public function getCoordinatedContracts(): Collection
    {
        return $this->coordinatedContracts;
    }

    public function addCoordinatedContract(Contract $contract): static
    {
        if (!$this->coordinatedContracts->contains($contract)) {
            $this->coordinatedContracts->add($contract);
            $contract->setCoordinator($this);
        }

        return $this;
    }

    public function removeCoordinatedContract(Contract $contract): static
    {
        if ($this->coordinatedContracts->removeElement($contract)) {
            // set the owning side to null (unless already changed)
            if ($contract->getCoordinator() === $this) {
                $contract->setCoordinator(null);
            }
        }

        return $this;
    }
}
