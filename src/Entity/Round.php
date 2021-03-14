<?php

namespace App\Entity;

use App\Repository\RoundRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoundRepository::class)
 */
class Round
{
    public const STATUS_STARTED = 100;
    public const STATUS_FINISHED = 200;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="create")
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finishedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Master::class, inversedBy="roundsWon")
     */
    private $winner;

    /**
     * @ORM\Column(type="integer", options={"default": App\Entity\Round::STATUS_STARTED})
     */
    private $status = self::STATUS_STARTED;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getFinishedAt(): ?DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?DateTimeInterface $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getWinner(): ?Master
    {
        return $this->winner;
    }

    public function setWinner(?Master $winner): self
    {
        $this->winner = $winner;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
