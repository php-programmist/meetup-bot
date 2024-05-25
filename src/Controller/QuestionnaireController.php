<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\QuestionnaireType;
use App\Model\Questionnaire;
use App\Service\MasterManager;
use App\Service\RatingManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class QuestionnaireController extends AbstractController
{
    const EVALUATOR_KEY = 'evaluator';

    /**
     * @Route("/", name="questionnaire")
     * @param Request $request
     * @param RatingManager $ratingManager
     * @param MasterManager $masterManager
     * @return Response
     * @throws ExceptionInterface
     */
    public function index(Request $request, RatingManager $ratingManager, MasterManager $masterManager): Response
    {
        $questionnaire = (new Questionnaire())
            ->setMaster($masterManager->getActiveMaster());

        $this->setEvaluator($request,$questionnaire);

        $form = $this->createForm(QuestionnaireType::class, $questionnaire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ratingManager->saveRating($form->getData());
            $response = $this->redirectToRoute('questionnaire_finish');
            $this->saveEvaluator($response, $questionnaire);
            return $response;
        }
        return $this->render('questionnaire/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/questionnaire/finish", name="questionnaire_finish")
     * @return Response
     */
    public function finish(): Response
    {
        return $this->render('questionnaire/finish.html.twig');
    }

    private function saveEvaluator(Response $response, Questionnaire $questionnaire): void
    {
        if (null === $questionnaire->getEvaluator()) {
            return;
        }
        $cookie = Cookie::create(self::EVALUATOR_KEY)
            ->withValue($questionnaire->getEvaluator()->getId())
            ->withExpires(strtotime('+20 years'));

        $response->headers->setCookie($cookie);
    }

    private function setEvaluator(Request $request, Questionnaire $questionnaire): void
    {
        $evaluatorId = (int)$request->cookies->get( self::EVALUATOR_KEY);
        if ($evaluatorId === 0) {
            return;
        }
        $evaluator = $this->getDoctrine()->getRepository(Member::class)->find($evaluatorId);
        if (null !== $evaluator) {
            $questionnaire->setEvaluator($evaluator);
        }
    }
}
