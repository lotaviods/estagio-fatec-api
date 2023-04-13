<?php

namespace App\Entity;

use App\Repository\StudentResumeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentResumeRepository::class)]
class StudentResume
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'resume', targetEntity: Student::class)]
    #[ORM\JoinColumn(name: "student_id", referencedColumnName: "id", nullable: true)]
    private ?Student $student = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uri = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return Student|null
     */
    public function getStudent(): ?Student
    {
        return $this->student;
    }

    /**
     * @param Student|null $student
     */
    public function setStudent(?Student $student): void
    {
        $this->student = $student;
    }

}
