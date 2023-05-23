<?php

namespace App\Entity;

use App\Repository\JobOfferRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
#[ORM\HasLifecycleCallbacks]
class JobOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /** JobExperience */
    #[ORM\Column(nullable: true, options: ['default' => 1])]
    private ?int $job_experience = 1;

    #[ORM\Column(options: ['default' => 1])]
    private bool $is_active = true;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'jobOffer')]
    #[ORM\JoinColumn(name: 'target_course_id', referencedColumnName: 'id')]
    private ?Course $targetCourse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $promotionalImageUrl = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'job_offer')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: "appliedJobs", cascade: ['persist'])]
    private Collection $subscribedStudents;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToMany(targetEntity: Student::class, mappedBy: "likedJobs", cascade: ['persist'])]
    private Collection $studentLikes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    public function __construct()
    {
        $this->subscribedStudents = new ArrayCollection();
        $this->studentLikes = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->created_at = new \DateTime();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function setPromotionalUrl(?string $url): self
    {
        $this->promotionalImageUrl = $url;

        return $this;
    }

    public function getJobExperience(): ?string
    {
        return $this->job_experience;
    }

    public function setJobExperience(?string $job_experience): self
    {
        if ($job_experience !== null)
            $this->job_experience = $job_experience;

        return $this;
    }

    public function getTargetCourse(): ?Course
    {
        return $this->targetCourse;
    }

    public function setTargetCourse(Course $course): self
    {
        $this->targetCourse = $course;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function toArray(): array
    {
        $newArray = [
            "id" => $this->id,
            "created_at" => $this->getCreatedAt()->format(DateTimeInterface::ATOM),
            "title" => $this->getTitle(),
            "description" => $this->description,
            "role" => $this->role,
            "job_experience" => $this->job_experience,
            "company_id" => $this->company->getId(),
            "company_name" => $this->company->getName() ?? "",
            "company_profile_picture" => $this->company->getProfilePicture(),
            "is_active" => $this->is_active,
            "applied_students_count" => count($this->subscribedStudents),
            "like_count" => $this->studentLikes->count()
        ];
        if ($this->promotionalImageUrl != null)
            $newArray += ["promotional_image_url" => $this->promotionalImageUrl];

        if ($this->targetCourse != null) {
            $newArray += ["target_course" => $this->targetCourse->getName()];
            $newArray += ["target_course_id" => $this->targetCourse->getId()];
        }

        $studentLikeIdArray = [];
        /** @var Student $student */

        foreach ($this->studentLikes as $student) {
            $studentLikeIdArray[] = $student->getId();
        }

        $studentSubscribedIdArray = [];

        foreach ($this->subscribedStudents as $student) {
            $studentSubscribedIdArray[] = $student->getId();
        }

        $newArray += ["liked_by" => $studentLikeIdArray];

        $newArray += ["subscribed_by" => $studentSubscribedIdArray];

        $address = $this->getCompany()?->getAddress();

        if(!is_null($address))
            $newArray["address"] = "{$address->getCity()}, {$address->getState()}, {$address->getCountry()}";
        return $newArray;
    }

    public function subscribeStudent(Student $student): self
    {
        if ($this->subscribedStudents->contains($student)) return $this;

        $this->subscribedStudents->add($student);

        return $this;
    }

    public function unsubscribeStudent(Student $student): self
    {
        if (!$this->subscribedStudents->contains($student)) return $this;

        $this->subscribedStudents->removeElement($student);

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPromotionalImageUrl(): ?string
    {
        return $this->promotionalImageUrl;
    }

    /**
     * @return Collection
     */
    public function getSubscribedStudents(): Collection
    {
        return $this->subscribedStudents;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIsActive(): bool
    {
        return $this->is_active;
    }

    /**
     * @param bool $is_active
     */
    public function setIsActive(bool $is_active): void
    {
        $this->is_active = $is_active;
    }
}
