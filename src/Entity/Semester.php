<?php

namespace App\Entity;

use App\Repository\SemesterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemesterRepository::class)]
class Semester
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $initial_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $final_date = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\ManyToOne(inversedBy: 'semesters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?course $course = null;

    #[ORM\OneToMany(mappedBy: 'semester', targetEntity: Student::class)]
    private Collection $students;

    public function __construct()
    {
        $this->course = new ArrayCollection();
        $this->students = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, course>
     */
    public function getCourse(): Collection
    {
        return $this->course;
    }

    public function addCourse(course $course): self
    {
        if (!$this->course->contains($course)) {
            $this->course->add($course);
        }

        return $this;
    }

    public function removeCourse(course $course): self
    {
        $this->course->removeElement($course);

        return $this;
    }

    public function getInitialDate(): ?\DateTimeInterface
    {
        return $this->initial_date;
    }

    public function setInitialDate(\DateTimeInterface $initial_date): self
    {
        $this->initial_date = $initial_date;

        return $this;
    }

    public function getFinalDate(): ?\DateTimeInterface
    {
        return $this->final_date;
    }

    public function setFinalDate(\DateTimeInterface $final_date): self
    {
        $this->final_date = $final_date;

        return $this;
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
}
