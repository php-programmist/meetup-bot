<?php


namespace App\Model;


use App\Entity\Member;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class Questionnaire
{
    public const ANSWER_YES = 'Да (1 балл)';
    public const ANSWER_NO = 'Нет (0 баллов)';
    public const SIMPLE_ANSWERS = [
        self::ANSWER_YES => 1,
        self::ANSWER_NO => 0,
    ];
    /**
     * @var Member
     * @Assert\NotNull()
     */
    private $evaluator;

    /**
     * @var bool
     * @Assert\NotNull()
     * @Groups({"details"})
     */
    private $inTime;

    /**
     * @var bool
     * @Assert\NotNull()
     * @Groups({"details"})
     */
    private $askedQuestions;

    /**
     * @var bool
     * @Assert\NotNull()
     * @Groups({"details"})
     */
    private $timeControl;

    /**
     * @var bool
     * @Assert\NotNull()
     * @Groups({"details"})
     */
    private $activeModeration;

    /**
     * @var bool
     * @Assert\NotNull()
     * @Groups({"details"})
     */
    private $impression;

    /**
     * @return Member
     */
    public function getEvaluator(): ?Member
    {
        return $this->evaluator;
    }

    /**
     * @param Member $evaluator
     * @return $this
     */
    public function setEvaluator(Member $evaluator): self
    {
        $this->evaluator = $evaluator;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInTime(): bool
    {
        return $this->inTime;
    }

    /**
     * @param bool $inTime
     * @return $this
     */
    public function setInTime(bool $inTime): self
    {
        $this->inTime = $inTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAskedQuestions(): bool
    {
        return $this->askedQuestions;
    }

    /**
     * @param bool $askedQuestions
     * @return $this
     */
    public function setAskedQuestions(bool $askedQuestions): self
    {
        $this->askedQuestions = $askedQuestions;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTimeControl(): bool
    {
        return $this->timeControl;
    }

    /**
     * @param bool $timeControl
     * @return $this
     */
    public function setTimeControl(bool $timeControl): self
    {
        $this->timeControl = $timeControl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActiveModeration(): bool
    {
        return $this->activeModeration;
    }

    /**
     * @param bool $activeModeration
     * @return $this
     */
    public function setActiveModeration(bool $activeModeration): self
    {
        $this->activeModeration = $activeModeration;
        return $this;
    }

    /**
     * @return bool
     */
    public function isImpression(): bool
    {
        return $this->impression;
    }

    /**
     * @param bool $impression
     * @return $this
     */
    public function setImpression(bool $impression): self
    {
        $this->impression = $impression;
        return $this;
    }

    public function getScore(): int
    {
        return
            (int)$this->isInTime()
            + (int)$this->isAskedQuestions()
            + (int)$this->isTimeControl()
            + (int)$this->isActiveModeration()
            + ((int)$this->isImpression() * 2);

    }

}