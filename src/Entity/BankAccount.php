<?php

namespace App\Entity;

use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $balance = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $Close = 0;

    #[ORM\ManyToOne(inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $Users = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'FromAccount')]
    private Collection $outgoingTransactions;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'ToAccount')]
    private Collection $IncomingTransactions;

    #[ORM\Column(length: 255)]
    private ?string $Name = null;

    // 0: Principal, 1: Courant, 2: Epargne,
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $type = 0;

    public function __construct()
    {
        $this->outgoingTransactions = new ArrayCollection();
        $this->IncomingTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function isClose(): ?bool
    {
        return $this->Close;
    }

    public function setClose(bool $Close): static
    {
        $this->Close = $Close;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->Users;
    }

    public function setUser(?Users $UserId): static
    {
        $this->Users = $UserId;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getOutgoingTransactions(): Collection
    {
        return $this->outgoingTransactions;
    }

    public function addOutgoingTransaction(Transaction $outgoingTransaction): static
    {
        if (!$this->outgoingTransactions->contains($outgoingTransaction)) {
            $this->outgoingTransactions->add($outgoingTransaction);
            $outgoingTransaction->setFromAccount($this);
        }

        return $this;
    }

    public function removeOutgoingTransaction(Transaction $outgoingTransaction): static
    {
        if ($this->outgoingTransactions->removeElement($outgoingTransaction)) {
            // set the owning side to null (unless already changed)
            if ($outgoingTransaction->getFromAccount() === $this) {
                $outgoingTransaction->setFromAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getIncomingTransactions(): Collection
    {
        return $this->IncomingTransactions;
    }

    public function addIncomingTransaction(Transaction $incomingTransaction): static
    {
        if (!$this->IncomingTransactions->contains($incomingTransaction)) {
            $this->IncomingTransactions->add($incomingTransaction);
            $incomingTransaction->setToAccount($this);
        }

        return $this;
    }

    public function removeIncomingTransaction(Transaction $incomingTransaction): static
    {
        if ($this->IncomingTransactions->removeElement($incomingTransaction)) {
            // set the owning side to null (unless already changed)
            if ($incomingTransaction->getToAccount() === $this) {
                $incomingTransaction->setToAccount(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }
}
