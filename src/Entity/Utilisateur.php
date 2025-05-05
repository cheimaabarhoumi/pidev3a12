<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: "App\Repository\UtilisateurRepository")]
#[ORM\Table(name: "utilisateur")]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\OneToMany(targetEntity: Contrat::class, mappedBy: "utilisateur")]
    private Collection $contrats;

    public function __construct()
    {
        $this->contrats = new ArrayCollection();
    }

    // Getters/Setters
    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getContrats(): Collection { return $this->contrats; }
    public function addContrat(Contrat $contrat): self {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
            $contrat->setUtilisateur($this);
        }
        return $this;
    }
    public function removeContrat(Contrat $contrat): self {
        if ($this->contrats->removeElement($contrat)) {
            $contrat->setUtilisateur(null);
        }
        return $this;
    }
}