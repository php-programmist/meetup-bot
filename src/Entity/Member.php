<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MemberRepository::class)
 */
class Member
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullName;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="member", orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\OneToOne(targetEntity=Master::class, mappedBy="member", cascade={"persist", "remove"})
     */
    private $master;

    /**
     * @ORM\OneToMany(targetEntity=Rating::class, mappedBy="member", orphanRemoval=true)
     */
    private $ratings;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->master = (new Master())->setMember($this);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setMember($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getMember() === $this) {
                $message->setMember(null);
            }
        }

        return $this;
    }

    public function getMaster(): ?Master
    {
        return $this->master;
    }

    public function setMaster(Master $master): self
    {
        // set the owning side of the relation if necessary
        if ($master->getMember() !== $this) {
            $master->setMember($this);
        }

        $this->master = $master;

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setMember($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getMember() === $this) {
                $rating->setMember(null);
            }
        }

        return $this;
    }

    public function __toString():string
    {
        return (string)$this->fullName;
    }
}
