<?php

namespace App\Controller;

use App\Form\QuestionnaireType;
use App\Model\Questionnaire;
use App\Service\MasterManager;
use App\Service\RatingManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class QuestionnaireController extends AbstractController
{
    /**
     * @Route("/questionnaire", name="questionnaire")
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
        $form = $this->createForm(QuestionnaireType::class,$questionnaire);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ratingManager->saveRating($form->getData());
            return $this->redirectToRoute('questionnaire_finish');
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
}
