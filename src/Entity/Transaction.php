<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $Date = null;

    #[ORM\Column(length: 255)]
    private ?string $Type = null;

    #[ORM\Column]
    private ?float $Amount = null;

    #[ORM\Column]
    private ?bool $Cancel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Label = null;

    #[ORM\ManyToOne(inversedBy: 'outgoingTransactions')]
    private ?BankAccount $FromAccount = null;

    #[ORM\ManyToOne(inversedBy: 'IncomingTransactions')]
    private ?BankAccount $ToAccount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): static
    {
        $this->Date = $Date;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->Amount;
    }

    public function setAmount(float $Amount): static
    {
        $this->Amount = $Amount;

        return $this;
    }

    public function isCancel(): ?bool
    {
        return $this->Cancel;
    }

    public function setCancel(bool $Cancel): static
    {
        $this->Cancel = $Cancel;

        return $this;
    }

    public function getFromAccount(): ?BankAccount
    {
        return $this->FromAccount;
    }

    public function setFromAccount(?BankAccount $FromAccount): static
    {
        $this->FromAccount = $FromAccount;

        return $this;
    }

    public function getToAccount(): ?BankAccount
    {
        return $this->ToAccount;
    }

    public function setToAccount(?BankAccount $ToAccount): static
    {
        $this->ToAccount = $ToAccount;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->Label;
    }

    public function setLabel(?string $Label): static
    {
        $this->Label = $Label;

        return $this;
    }
}
