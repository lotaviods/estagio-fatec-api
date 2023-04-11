<?php

namespace App\Entity;

use App\Repository\MasterAdminCreationInviteRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;

#[ORM\Entity(repositoryClass: MasterAdminCreationInviteRepository::class)]
class MasterAdminCreationInvite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private ?\DateTime $expiresAt = null;

    #[ORM\ManyToOne(inversedBy: 'administratorInvites')]
    private ?Login $invitedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }
    public function isExpired(): bool
    {
        $now = new \DateTimeImmutable();
        return $now > $this->expiresAt;
    }

    public function setExpiresAt(\DateTime $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getInvitedBy(): ?Login
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?Login $invitedBy): self
    {
        $this->invitedBy = $invitedBy;

        return $this;
    }

    public static function fromRequest(Request $request): self
    {
        $invitation = new self();
        $invitation->setInvitedBy($request->get('invited_by'));
        $invitation->setToken($request->get('token'));
        $timestamp = $request->get('timestamp');
        $dateTime = new DateTime("@$timestamp");
        $invitation->setExpiresAt($dateTime);
        return $invitation;
    }
}
