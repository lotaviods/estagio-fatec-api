<?php

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessTokenRepository::class)]
class AccessToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    private string $accessToken;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $expiresAt;

    #[ORM\ManyToOne(targetEntity: Login::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false, onDelete: 'CASCADE')]
    private ?Login $user = null;

    public function isValid(): bool
    {
        $expiresAt = $this->getExpiresAt();

        if (!$expiresAt instanceof DateTime) {
            return false;
        }

        return $expiresAt > new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTimeInterface|null $expiresAt
     */
    public function setExpiresAt(?\DateTimeInterface $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getUser(): ?Login
    {
        return $this->user;
    }

    public function setUser(?Login $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserId(): string
    {
        return $this->getUser()?->getUsername();
    }
}
