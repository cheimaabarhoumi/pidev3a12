<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_rdv", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Assert\NotBlank(message: "La date du rendez-vous est obligatoire")]
    #[Assert\GreaterThan("now", message: "La date doit être dans le futur")]
    private ?\DateTimeImmutable $dateRdv = null;

    #[ORM\Column(name: "status", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    private ?string $status = null;

    #[ORM\Column(name: "description", type: "string", length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "La description doit faire au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Entretien::class)]
    #[ORM\JoinColumn(name: "id_entretien", referencedColumnName: "id_entretien")]
    #[Assert\NotNull(message: "Veuillez sélectionner un type d'entretien")]
    private ?Entretien $entretien = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateRdv(): ?\DateTimeImmutable
    {
        return $this->dateRdv;
    }

    public function setDateRdv(\DateTimeImmutable $dateRdv): static
    {
        $this->dateRdv = $dateRdv;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getEntretien(): ?Entretien
    {
        return $this->entretien;
    }

    public function setEntretien(?Entretien $entretien): static
    {
        $this->entretien = $entretien;
        return $this;
    }
}
