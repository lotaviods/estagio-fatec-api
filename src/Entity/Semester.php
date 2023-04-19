<?php

namespace App\Entity;

use App\Repository\SemesterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemesterRepository::class)]
class Semester
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\ManyToOne(cascade: ['persist', 'remove'], inversedBy: 'semesters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\OneToMany(mappedBy: 'semester', targetEntity: CollageClass::class, cascade: ['persist', 'remove'])]
    private Collection $collageClasses;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->collageClasses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function setCourse(?course $course): self
    {
        $this->course = $course;

        return $this;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): self
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
            $student->setSemester($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): self
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getSemester() === $this) {
                $student->setSemester(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CollageClass>
     */
    public function getCollageClasses(): Collection
    {
        return $this->collageClasses;
    }

    public function addCollageClass(CollageClass $collageClass): self
    {
        if (!$this->collageClasses->contains($collageClass)) {
            $this->collageClasses->add($collageClass);
            $collageClass->setSemester($this);
        }

        return $this;
    }

    public function removeCollageClass(CollageClass $collageClass): self
    {
        if ($this->collageClasses->removeElement($collageClass)) {
            // set the owning side to null (unless already changed)
            if ($collageClass->getSemester() === $this) {
                $collageClass->setSemester(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
