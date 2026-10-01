<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Products $product = null;

    #[ORM\Column(length: 255)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cardBrand = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $cardLast4 = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isSuccessful = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: 'float')]
    private ?float $amount = null;

    #[ORM\Column(length: 10)]
    private ?string $currency = 'eur';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invoiceFilename = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\Column(type: 'string', length: 20)]
    private string $type = 'article';

    /**
     * @var Collection<int, PaymentDetail>
     */
    #[ORM\OneToMany(targetEntity: PaymentDetail::class, mappedBy: 'payment')]
    private Collection $paymentDetails;

    public function __construct()
    {
        $this->paymentDetails = new ArrayCollection();
    } // ou 'subscription'

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;
        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): self
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
        return $this;
    }

    public function getCardBrand(): ?string
    {
        return $this->cardBrand;
    }

    public function setCardBrand(?string $cardBrand): self
    {
        $this->cardBrand = $cardBrand;
        return $this;
    }

    public function getCardLast4(): ?string
    {
        return $this->cardLast4;
    }

    public function setCardLast4(?string $cardLast4): self
    {
        $this->cardLast4 = $cardLast4;
        return $this;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function setIsSuccessful(bool $isSuccessful): self
    {
        $this->isSuccessful = $isSuccessful;
        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeImmutable $paidAt): self
    {
        $this->paidAt = $paidAt;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getInvoiceFilename(): ?string
    {
        return $this->invoiceFilename;
    }

    public function setInvoiceFilename(?string $invoiceFilename): self
    {
        $this->invoiceFilename = $invoiceFilename;
        return $this;
    }

    public function __toString(): string
    {
        return ""; // Retourne le name de l'utilisateur comme chaîne, nécessaire dans CourseCrudController
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Collection<int, PaymentDetail>
     */
    public function getPaymentDetails(): Collection
    {
        return $this->paymentDetails;
    }

    public function addPaymentDetail(PaymentDetail $paymentDetail): static
    {
        if (!$this->paymentDetails->contains($paymentDetail)) {
            $this->paymentDetails->add($paymentDetail);
            $paymentDetail->setPayment($this);
        }

        return $this;
    }

    public function removePaymentDetail(PaymentDetail $paymentDetail): static
    {
        if ($this->paymentDetails->removeElement($paymentDetail)) {
            // set the owning side to null (unless already changed)
            if ($paymentDetail->getPayment() === $this) {
                $paymentDetail->setPayment(null);
            }
        }

        return $this;
    }

        
}
