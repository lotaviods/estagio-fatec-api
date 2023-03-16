<?php


namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ra = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\JoinTable(name: 'student_applied_jobs')]
    #[ORM\JoinColumn('student_id', referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: 'job_id', referencedColumnName: "id")]
    #[ORM\ManyToMany(targetEntity: JobOffer::class)]
    private Collection $appliedJobs;

    #[ORM\JoinTable(name: 'student_liked_jobs')]
    #[ORM\JoinColumn('student_id', referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: 'job_id', referencedColumnName: "id")]
    #[ORM\ManyToMany(targetEntity: JobOffer::class)]
    private Collection $likedJobs;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?Semester $semester = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?login $login = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function likeJobOffer(JobOffer $jobOffer): Student
    {
        if ($this->likedJobs->contains($jobOffer)) return $this;

        $this->likedJobs->add($jobOffer);

        return $this;
    }

    public function dislikeJobOffer(JobOffer $jobOffer): Student
    {
        if (!$this->likedJobs->contains($jobOffer)) return $this;

        $this->likedJobs->removeElement($jobOffer);
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
            "ra" => $this->ra,
            "email" => $this->email,
            "applied_jobs" => $jobArray
        ];
    }

    public function getSemester(): ?semester
    {
        return $this->semester;
    }

    public function setSemester(?semester $semester): self
    {
        $this->semester = $semester;

        return $this;
    }

    public function getCourse(): ?course
    {
        return $this->course;
    }

    public function setCourse(?course $course): self
    {
        $this->course = $course;

        return $this;
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

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}