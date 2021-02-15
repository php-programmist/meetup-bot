<?php


namespace App\Service;


use App\Entity\Rating;
use App\Model\Questionnaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RatingManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var NormalizerInterface
     */
    private $normalizer;
    /**
     * @var MasterManager
     */
    private $masterManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param NormalizerInterface $normalizer
     * @param MasterManager $masterManager
     */
    public function __construct(EntityManagerInterface $entityManager, NormalizerInterface $normalizer,MasterManager $masterManager)
    {
        $this->entityManager = $entityManager;
        $this->normalizer = $normalizer;
        $this->masterManager = $masterManager;
    }

    /**
     * @param Questionnaire $questionnaire
     * @throws ExceptionInterface
     */
    public function saveRating(Questionnaire $questionnaire): void
    {
        $details = $this->normalizer->normalize($questionnaire,null,[
            AbstractNormalizer::GROUPS => ['details']
        ]);
        $activeMaster = $this->masterManager->getActiveMaster();

        $rating = (new Rating())
            ->setMember($questionnaire->getEvaluator())
            ->setScore($questionnaire->getScore())
            ->setDetails($details)
            ->setMaster($activeMaster);

        $this->entityManager->persist($rating);
        $this->entityManager->flush();
    }
}