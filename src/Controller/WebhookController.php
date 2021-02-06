<?php

namespace App\Controller;

use App\Service\TelegramApiManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    /**
     * @Route("/webhook/{token}", name="webhook")
     */
    public function index(string $token, string $telegramWebhookToken, TelegramApiManager $manager): Response
    {
        if ($token !== $telegramWebhookToken) {
            throw new AccessDeniedHttpException();
        }
        $manager->handleUpdate();
        return new Response();
    }
}
