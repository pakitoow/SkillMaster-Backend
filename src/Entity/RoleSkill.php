<?php

namespace App\Entity;

use App\Repository\RoleSkillRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleSkillRepository::class)]
class RoleSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'roleSkills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    #[ORM\ManyToOne(targetEntity: Skill::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skill = null;

    // Minimum proficiency level required for this role (1–5)
    #[ORM\Column(type: 'smallint')]
    private int $requiredLevel = 3;

    public function getId(): ?int { return $this->id; }

    public function getRole(): ?Role { return $this->role; }
    public function setRole(?Role $role): static { $this->role = $role; return $this; }

    public function getSkill(): ?Skill { return $this->skill; }
    public function setSkill(?Skill $skill): static { $this->skill = $skill; return $this; }

    public function getRequiredLevel(): int { return $this->requiredLevel; }
    public function setRequiredLevel(int $level): static { $this->requiredLevel = $level; return $this; }
}
