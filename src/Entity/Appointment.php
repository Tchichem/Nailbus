<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Services;

#[ORM\Entity]
#[ORM\Table(name: "appointments")]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    private ?float $duration = null; 

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private ?string $clientName = null;

    #[ORM\Column(type: "string", length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $clientEmail = null;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: "/^\+?[0-9]{7,15}$/", message: "Numéro de téléphone invalide")]
    private ?string $clientPhone = null;

    #[ORM\ManyToOne(targetEntity: Services::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Services $service = null;

    #[ORM\Column(type: "string", length: 20)]
    #[Assert\Choice(choices: ["Scheduled", "Completed", "Cancelled"], message: "Statut invalide.")]
    private ?string $status = "Scheduled";

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }
    
    public function setDuration(float $duration): static
    {
        $this->duration = $duration;
        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): static
    {
        $this->clientName = $clientName;
        return $this;
    }

    public function getClientEmail(): ?string
    {
        return $this->clientEmail;
    }

    public function setClientEmail(?string $clientEmail): static
    {
        $this->clientEmail = $clientEmail;
        return $this;
    }

    public function getClientPhone(): ?string
    {
        return $this->clientPhone;
    }

    public function setClientPhone(string $clientPhone): static
    {
        $this->clientPhone = $clientPhone;
        return $this;
    }

    public function getService(): ?Services
    {
        return $this->service;
    }

    public function setService(?Services $service): static
    {
        $this->service = $service;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }


}

