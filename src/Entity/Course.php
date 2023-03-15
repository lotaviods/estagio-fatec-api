<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;
    #[ORM\OneToMany(mappedBy: 'targetCourse', targetEntity: JobOffer::class)]
    private Collection $jobOffer;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Semester::class)]
    private Collection $semesters;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Student::class)]
    private Collection $students;

    public function __construct()
    {
        $this->jobOffer = new ArrayCollection();
        $this->semesters = new ArrayCollection();
        $this->students = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getJobs()
    {
        return $this->jobs;
    }

    public function setJobs($jobs): self
    {
        $this->jobs = $jobs;
        return $this;
    }

    public function addJob(JobOffer $job): void
    {
        if ($this->jobs->contains($job)) {
            return;
        }
        $this->jobs->add($job);
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
            $student->setCourse($this);
        }

        return $this;
    }

    public function removeStudent(Student $student): self
    {
        if ($this->students->removeElement($student)) {
            // set the owning side to null (unless already changed)
            if ($student->getCourse() === $this) {
                $student->setCourse(null);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "job_offers" => count($this->jobOffer)
        ];
    }

    /**
     * @return Collection<int, Semester>
     */
    public function getSemesters(): Collection
    {
        return $this->semesters;
    }

    public function addSemester(Semester $semester): self
    {
        if (!$this->semesters->contains($semester)) {
            $this->semesters->add($semester);
            $semester->setCourse($this);
        }

        return $this;
    }

    public function removeSemester(Semester $semester): self
    {
        if ($this->semesters->removeElement($semester)) {
            // set the owning side to null (unless already changed)
            if ($semester->getCourse() === $this) {
                $semester->setCourse(null);
            }
        }

        return $this;
    }
}
