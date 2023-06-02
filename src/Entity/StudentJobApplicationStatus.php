<?php

namespace App\Entity;

use App\Repository\StudentJobApplicationStatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentJobApplicationStatusRepository::class)]
#[ORM\HasLifecycleCallbacks]
class StudentJobApplicationStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: "Student")]
    #[ORM\JoinColumn(name: "student_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Student $student;

    #[ORM\ManyToOne(targetEntity: "JobOffer")]
    #[ORM\JoinColumn(name: "job_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?JobOffer $jobOffer;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): self
    {
        $this->jobOffer = $jobOffer;

        return $this;
    }


    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    #[ORM\PrePersist]
    public function setUpdatedAtNow(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }
}
