<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, RoleSkill>
     */
    #[ORM\OneToMany(targetEntity: RoleSkill::class, mappedBy: 'role', cascade: ['remove'])]
    private Collection $roleSkills;

    public function __construct()
    {
        $this->roleSkills = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    /** @return Collection<int, RoleSkill> */
    public function getRoleSkills(): Collection { return $this->roleSkills; }

    public function addRoleSkill(RoleSkill $roleSkill): static
    {
        if (!$this->roleSkills->contains($roleSkill)) {
            $this->roleSkills->add($roleSkill);
            $roleSkill->setRole($this);
        }
        return $this;
    }

    public function removeRoleSkill(RoleSkill $roleSkill): static
    {
        if ($this->roleSkills->removeElement($roleSkill)) {
            if ($roleSkill->getRole() === $this) {
                $roleSkill->setRole(null);
            }
        }
        return $this;
    }
}
