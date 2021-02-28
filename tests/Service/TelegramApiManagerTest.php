<?php

namespace App\Tests\Service;

use App\Entity\Member;
use App\Entity\Poll;
use App\Repository\MemberRepository;
use App\Repository\PollRepository;
use App\Service\MasterManager;
use App\Service\MessageManager;
use App\Service\TelegramApiManager;
use App\TelegramCommand\RatingCommand;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Message as MessageObject;

class TelegramApiManagerTest extends TestCase
{

    /**
     * @throws Exception
     * @throws TelegramSDKException
     * @test
     */
    public function sendInitialMessage(): void
    {
        $message = $this->createMock(MessageObject::class);
        $message->method('get')->willReturn(1);

        $telegram = $this->createMock(Api::class);
        $telegram->expects(self::once())
            ->method('sendPoll')
            ->willReturn($message);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('persist');
        $entityManager->expects(self::once())
            ->method('flush');

        $messageManager = $this->createMock(MessageManager::class);
        $messageManager->expects(self::once())
            ->method('deleteAllMessages');

        $logger = $this->createMock(LoggerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $masterManager = $this->createMock(MasterManager::class);
        $ratingCommand = $this->createMock(RatingCommand::class);
        $telegramChatId = '0';
        $telegramWebhookToken = '';

        $telegramApiManager = new TelegramApiManager(
            $telegram,
            $entityManager,
            $messageManager,
            $logger,
            $router,
            $masterManager,
            $ratingCommand,
            $telegramChatId,
            $telegramWebhookToken
        );

        $telegramApiManager->sendInitialMessage();
    }

    /**
     * @throws TelegramSDKException
     * @test
     */
    public function sendNotificationMessage(): void
    {
        $member = $this->createMock(Member::class);
        $member->expects(self::once())
            ->method('getUsername')
            ->willReturn('john-dow');

        $memberRepository = $this->createMock(MemberRepository::class);
        $memberRepository
            ->expects(self::once())
            ->method('getNotAnswered')
            ->willReturn([$member]);

        $telegram = $this->createMock(Api::class);
        $telegram->expects(self::once())
            ->method('sendMessage');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Member::class)
            ->willReturn($memberRepository);

        $messageManager = $this->createMock(MessageManager::class);

        $logger = $this->createMock(LoggerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $masterManager = $this->createMock(MasterManager::class);
        $ratingCommand = $this->createMock(RatingCommand::class);
        $telegramChatId = '0';
        $telegramWebhookToken = '';

        $telegramApiManager = new TelegramApiManager(
            $telegram,
            $entityManager,
            $messageManager,
            $logger,
            $router,
            $masterManager,
            $ratingCommand,
            $telegramChatId,
            $telegramWebhookToken
        );

        $telegramApiManager->sendNotificationMessage();
    }

    /**
     * @throws TelegramSDKException
     * @test
     */
    public function sendResumeMessage(): void
    {
        $member = $this->createMock(Member::class);
        $member->expects(self::once())
            ->method('getFullName')
            ->willReturn('John Dow');

        $memberRepository = $this->createMock(MemberRepository::class);
        $memberRepository
            ->expects(self::once())
            ->method('getPresent')
            ->willReturn([$member]);

        $poll = $this->createMock(Poll::class);
        $poll->expects(self::once())
            ->method('setFinishedAt');

        $pollRepository = $this->createMock(PollRepository::class);
        $pollRepository
            ->expects(self::once())
            ->method('findOpened')
            ->willReturn($poll);

        $telegram = $this->createMock(Api::class);
        $telegram->expects(self::once())
            ->method('sendMessage');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $map = [
            [Poll::class,$pollRepository],
            [Member::class,$memberRepository],
        ];

        $entityManager
            ->method('getRepository')
            ->willReturnMap($map);

        $messageManager = $this->createMock(MessageManager::class);

        $logger = $this->createMock(LoggerInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $masterManager = $this->createMock(MasterManager::class);
        $ratingCommand = $this->createMock(RatingCommand::class);
        $telegramChatId = '0';
        $telegramWebhookToken = '';

        $telegramApiManager = new TelegramApiManager(
            $telegram,
            $entityManager,
            $messageManager,
            $logger,
            $router,
            $masterManager,
            $ratingCommand,
            $telegramChatId,
            $telegramWebhookToken
        );

        $telegramApiManager->sendResumeMessage();
    }
}
