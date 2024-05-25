<?php

namespace App\Controller\Admin;

use App\Entity\Holiday;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class HolidayCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Holiday::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Выходной')
            ->setEntityLabelInPlural('Выходные')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование выходного #<b>%entity_id%</b>')
            ->setDefaultSort(['date' => 'DESC'])
            ->setSearchFields([]);
    }
}
