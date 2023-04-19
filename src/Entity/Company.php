<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: JobOffer::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $job_offer;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?login $login = null;

    public function __construct()
    {
        $this->job_offer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, JobOffer>
     */
    public function getJobOffer(): Collection
    {
        return $this->job_offer;
    }

    public function addJobOffer(JobOffer $jobOffer): self
    {
        if (!$this->job_offer->contains($jobOffer)) {
            $this->job_offer->add($jobOffer);
            $jobOffer->setCompany($this);
        }

        return $this;
    }

    public function removeJobOffer(JobOffer $jobOffer): self
    {
        if ($this->job_offer->removeElement($jobOffer)) {
            // set the owning side to null (unless already changed)
            if ($jobOffer->getCompany() === $this) {
                $jobOffer->setCompany(null);
            }
        }

        return $this;
    }

    public function getEmail(): string
    {
        return $this->login->getEmail();
    }

    public function setEmail(string $email): self
    {
        $this->login->setEmail($email);
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getProfilePicture(): ?string
    {
        return $this->login?->getProfilePicture();
    }

    /**
     * @param string|null $profilePicture
     */
    public function setProfilePicture(?string $profilePicture): void
    {
        $this->login?->setProfilePicture($profilePicture);
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
        return $this->login?->getName();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'email' => $this->login?->getEmail(),
            'active' => $this->isActive()
        ];
    }

}
