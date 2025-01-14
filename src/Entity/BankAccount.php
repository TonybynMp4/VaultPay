<?php

namespace App\Entity;

use App\Repository\BankAccountRepository;
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
}
