<?php


namespace App\Service;


use App\Entity\Message;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class MessageManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MemberManager
     */
    private $memberManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param MemberManager $memberManager
     */
    public function __construct(EntityManagerInterface $entityManager,MemberManager $memberManager)
    {
        $this->entityManager = $entityManager;
        $this->memberManager = $memberManager;
    }

    /**
     * @param int $updateId
     * @return Message
     */
    public function findOrCreateMessage(int $updateId): Message
    {
        $message = $this->entityManager
            ->getRepository(Message::class)
            ->findOneBy(['updateId'=>$updateId]);

        if (null === $message) {
            $message = (new Message())->setUpdateId($updateId);
            $this->entityManager->persist($message);
        }

        return $message;
    }

    public function saveMessage(string $text, string $username, int $updateId, ?int $date = null): void
    {
        $message = $this->findOrCreateMessage($updateId);
        $message
            ->setText($text)
            ->setMember($this->memberManager->findOrFail($username));
        if (null !== $date) {
            $message->setTelegramDate((new DateTime())->setTimestamp($date));
        }

        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    public function deleteAllMessages():void
    {
        $this->entityManager
            ->getConnection()
            ->executeStatement('truncate table message');
    }
}