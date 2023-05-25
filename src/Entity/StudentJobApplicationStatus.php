<?php

namespace App\Entity;

use App\Repository\StudentJobApplicationStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentJobApplicationStatusRepository::class)]
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
}
