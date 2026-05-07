<?php

namespace App\Entity;

use App\Repository\DDFPTSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DDFPTSettingsRepository::class)]
class DDFPTSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'boolean')]
    private bool $requireYousignApproval = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $approvalEmail = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'ddfptSettings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isRequireYousignApproval(): bool
    {
        return $this->requireYousignApproval;
    }

    public function setRequireYousignApproval(bool $requireYousignApproval): static
    {
        $this->requireYousignApproval = $requireYousignApproval;
        return $this;
    }

    public function getApprovalEmail(): ?string
    {
        return $this->approvalEmail;
    }

    public function setApprovalEmail(?string $approvalEmail): static
    {
        $this->approvalEmail = $approvalEmail;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
