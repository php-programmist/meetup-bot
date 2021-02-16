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
     * @param EntityManagerInterface $entityManager
     * @param NormalizerInterface $normalizer
     */
    public function __construct(EntityManagerInterface $entityManager, NormalizerInterface $normalizer)
    {
        $this->entityManager = $entityManager;
        $this->normalizer = $normalizer;
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

        $rating = (new Rating())
            ->setMaster($questionnaire->getMaster())
            ->setMember($questionnaire->getEvaluator())
            ->setScore($questionnaire->getScore())
            ->setDetails($details);

        $this->entityManager->persist($rating);
        $this->entityManager->flush();
    }
}