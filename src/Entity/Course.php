<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 * @UniqueEntity("code")
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $info;

    /**
     * @ORM\OneToMany(targetEntity=Lesson::class, mappedBy="course", cascade={"persist"})
     * @ORM\OrderBy({"number" = "ASC"})
     */
    private $Includes;

    public function __construct()
    {
        $this->Includes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getIncludes(): Collection
    {
        return $this->Includes;
    }

    public function addInclude(Lesson $include): self
    {
        if (!$this->Includes->contains($include)) {
            $this->Includes[] = $include;
            $include->setCourse($this);
        }

        return $this;
    }

    public function removeInclude(Lesson $include): self
    {
        if ($this->Includes->removeElement($include)) {
            // set the owning side to null (unless already changed)
            if ($include->getCourse() === $this) {
                $include->setCourse(null);
            }
        }

        return $this;
    }
}
