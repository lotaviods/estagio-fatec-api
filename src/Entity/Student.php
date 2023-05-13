<?php


namespace App\Entity;

use App\Helper\ProfilePictureHelper;
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

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Login $login = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'students')]
    #[ORM\JoinColumn(name: "course_id", referencedColumnName: "id", onDelete: "SET NULL")]
    private ?Course $course;

    #[ORM\OneToOne(mappedBy: 'student', cascade: ['persist', 'remove'])]
    private ?StudentResume $resume = null;

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
            "id" => $this->id,
            "profile_picture" => $this->login?->getProfilePicture(),
            "ra" => $this->ra,
            "name" => $this->getName(),
            "course_id" => $this->getCourse()?->getId() ?? null,
            "course_name" => $this->getCourse()?->getName(),
            "email" => $this->login->getEmail() ?? "",
            "applied_jobs" => $jobArray
        ];
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
        return $this->login->getName();
    }

    public function getResume(): ?StudentResume
    {
        return $this->resume;
    }

    public function setResume(StudentResume $resume): self
    {
        $this->resume = $resume;

        return $this;
    }

    public function setProfilePicture(?string $profilePicture): void
    {
        $this->login->setProfilePicture($profilePicture);
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): void
    {
        $this->course = $course;
    }
}