<?php

namespace App\Entity;

use App\Repository\AdministratorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdministratorRepository::class)]
class Administrator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?login $login = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?login
    {
        return $this->login;
    }

    public function setLogin(login $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->getLogin()?->getName() ?? "";
    }
}
