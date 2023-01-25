<?php


namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $ra = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\JoinTable(name: 'student_applied_jobs')]
    #[ORM\JoinColumn('student_id', referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: 'job_id', referencedColumnName: "id")]
    #[ORM\ManyToMany(targetEntity: JobOffer::class)]
    private Collection $appliedJobs;

    #[ORM\ManyToOne(targetEntity: Section::class, inversedBy: "students")]
    private Section $section;

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

    public function getAppliedJobOffers(): Collection
    {
        return $this->appliedJobs;
    }

    public function applyToJobOffer(JobOffer $jobOffer): Student
    {
        if ($this->appliedJobs->contains($jobOffer)) return $this;

        $this->appliedJobs->add($jobOffer);
        $jobOffer->subscribeStudent($this);

        return $this;
    }

    public function unSubscribeToJobOffer(JobOffer $jobOffer): Student
    {
        if (!$this->appliedJobs->contains($jobOffer)) return $this;

        $jobOffer->unsubscribeStudent($this);
        $this->appliedJobs->removeElement($jobOffer);

        return $this;
    }

    public function getRa(): ?string
    {
        return $this->ra;
    }

    public function setRa(string $ra): self
    {
        $this->ra = $ra;

        return $this;
    }

    public function toArray(): array
    {
        $jobArray = [];

        foreach ($this->appliedJobs as $jobs) {
            $jobArray[] = $jobs->toArray();
        }

        return [
            "name" => $this->name,
            "ra" => $this->ra,
            "course" => $this->section->getCourse()->getName(),
            "email" => $this->email,
            "applied_jobs" => $jobArray
        ];
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }
}