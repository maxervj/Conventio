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
    #[ORM\ManyToMany(targetEntity: Level::class)]
    private Collection $Level;

    public function __construct()
    {
        $this->Level = new ArrayCollection();
    }

    // TODO: Décommenter quand l'entité Contract sera créée
    // /**
    //  * @var Collection<int, Contract>
    //  */
    // #[ORM\OneToMany(targetEntity: Contract::class, mappedBy: 'coordinator')]
    // private Collection $coordinatedContracts;

    // public function __construct()
    // {
    //     $this->coordinatedContracts = new ArrayCollection();
    // }

    // /**
    //  * @return Collection<int, Contract>
    //  */
    // public function getCoordinatedContracts(): Collection
    // {
    //     return $this->coordinatedContracts;
    // }

    // public function addCoordinatedContract(Contract $contract): static
    // {
    //     if (!$this->coordinatedContracts->contains($contract)) {
    //         $this->coordinatedContracts->add($contract);
    //         $contract->setCoordinator($this);
    //     }

    //     return $this;
    // }

    // public function removeCoordinatedContract(Contract $contract): static
    // {
    //     if ($this->coordinatedContracts->removeElement($contract)) {
    //         // set the owning side to null (unless already changed)
    //         if ($contract->getCoordinator() === $this) {
    //             $contract->setCoordinator(null);
    //         }
    //     }

    //     return $this;
    // }

    /**
     * @return Collection<int, Level>
     */
    public function getLevel(): Collection
    {
        return $this->Level;
    }

    public function setLevel(Collection $Level): self
    {
        $this->Level = $Level;
        return $this;
    }

    public function addLevel(Level $level): static
    {
        if (!$this->Level->contains($level)) {
            $this->Level->add($level);
        }

        return $this;
    }

    public function removeLevel(Level $level): static
    {
        $this->Level->removeElement($level);

        return $this;
    }
}
