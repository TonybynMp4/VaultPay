<?php

namespace App\Entity;

use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $Solde = null;

    #[ORM\Column]
    private ?bool $Close = null;

    #[ORM\ManyToOne(inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $UserId = null;

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

    public function __construct()
    {
        $this->outgoingTransactions = new ArrayCollection();
        $this->IncomingTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSolde(): ?float
    {
        return $this->Solde;
    }

    public function setSolde(float $Solde): static
    {
        $this->Solde = $Solde;

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

    public function getUserId(): ?User
    {
        return $this->UserId;
    }

    public function setUserId(?User $UserId): static
    {
        $this->UserId = $UserId;

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
}
