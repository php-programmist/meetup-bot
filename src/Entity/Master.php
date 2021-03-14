<?php

namespace App\Entity;

use App\Repository\MasterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MasterRepository::class)
 */
class Master
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Member::class, inversedBy="master", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $member;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $active = false;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $ordering = 0;

    /**
     * @ORM\OneToMany(targetEntity=Rating::class, mappedBy="master", orphanRemoval=true)
     */
    private $ratings;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $firstInRound = false;

    /**
     * @ORM\OneToMany(targetEntity=Round::class, mappedBy="winner")
     */
    private $roundsWon;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->roundsWon = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getOrdering(): ?int
    {
        return $this->ordering;
    }

    public function setOrdering(int $ordering): self
    {
        $this->ordering = $ordering;

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
            $rating->setMaster($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getMaster() === $this) {
                $rating->setMaster(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->getMember();
    }

    /**
     * @return bool
     */
    public function isFirstInRound(): bool
    {
        return $this->firstInRound;
    }

    /**
     * @param bool $firstInRound
     * @return $this
     */
    public function setFirstInRound(bool $firstInRound): self
    {
        $this->firstInRound = $firstInRound;
        return $this;
    }

    /**
     * @return Collection|Round[]
     */
    public function getRoundsWon(): Collection
    {
        return $this->roundsWon;
    }

    public function addRoundsWon(Round $roundsWon): self
    {
        if (!$this->roundsWon->contains($roundsWon)) {
            $this->roundsWon[] = $roundsWon;
            $roundsWon->setWinner($this);
        }

        return $this;
    }

    public function removeRoundsWon(Round $roundsWon): self
    {
        if ($this->roundsWon->removeElement($roundsWon)) {
            // set the owning side to null (unless already changed)
            if ($roundsWon->getWinner() === $this) {
                $roundsWon->setWinner(null);
            }
        }

        return $this;
    }
}
