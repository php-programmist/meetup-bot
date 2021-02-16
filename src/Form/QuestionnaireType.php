<?php

namespace App\Form;

use App\Entity\Master;
use App\Entity\Member;
use App\Model\Questionnaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionnaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('master',EntityType::class,[
                'class' => Master::class,
                'required' => true,
                'placeholder' => 'Не выбран',
            ])
            ->add('evaluator',EntityType::class,[
                'class' => Member::class,
                'required' => true,
                'placeholder' => 'Не выбран',
            ])
            ->add('inTime',ChoiceType::class,[
                'required' => true,
                'choices' => Questionnaire::SIMPLE_ANSWERS,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('askedQuestions',ChoiceType::class,[
                'required' => true,
                'choices' => Questionnaire::SIMPLE_ANSWERS,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('timeControl',ChoiceType::class,[
                'required' => true,
                'choices' => Questionnaire::SIMPLE_ANSWERS,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('activeModeration',ChoiceType::class,[
                'required' => true,
                'choices' => ['Активная модерация, все чётко (1 балл)' => 1, 'Пассивное поведение (0 баллов)' => 0],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('impression',ChoiceType::class,[
                'required' => true,
                'choices' => ['Скорее понравилось (2 балла)' => 1, 'Скорее не понравилось (0 баллов)' => 0],
                'expanded' => true,
                'multiple' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
            'label_format' => 'form.%name%.label',
            'translation_domain' => 'questionnaire',
        ]);
    }
}
