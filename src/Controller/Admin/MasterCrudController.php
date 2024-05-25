<?php

namespace App\Controller\Admin;

use App\Entity\Master;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\FieldProvider;

class MasterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Master::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ведущий')
            ->setEntityLabelInPlural('Ведущие')
            ->setPageTitle(Crud::PAGE_EDIT, 'Редактирование ведущего #<b>%entity_id%</b>')
            ->setDefaultSort(['ordering' => 'ASC'])
            ->setSearchFields([]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        $member         = AssociationField::new('member', 'Участник');
        $default = $this->get(FieldProvider::class)->getDefaultFields($pageName);

        return [
            $member,
            ...$default,
        ];
    }
}
