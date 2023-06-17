<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $name = null;
    #[ORM\OneToMany(mappedBy: 'targetCourse', targetEntity: JobOffer::class, cascade: ['persist', 'remove'])]
    private Collection $jobOffer;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Student::class)]
    private Collection $students;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->jobOffer = new ArrayCollection();
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
        return $this->jobOffer;
    }

    public function setJobs($jobs): self
    {
        $this->jobOffer = $jobs;
        return $this;
    }

    public function addJob(JobOffer $job): void
    {
        if ($this->jobOffer->contains($job)) {
            return;
        }
        $this->jobOffer->add($job);
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
            "description" => $this->description,
            "job_offers" => count($this->jobOffer)
        ];
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
