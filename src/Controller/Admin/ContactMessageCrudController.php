<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class ContactMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Message de contact')
            ->setEntityLabelInPlural('Messages de contact')
            ->setPageTitle('index', 'Messages reçus')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', '#')->onlyOnIndex();
        yield TextField::new('nom', 'Nom');
        yield EmailField::new('email', 'Email');

        yield ChoiceField::new('motif', 'Soin demandé')
            ->setChoices([
                'Première consultation' => 'consultation',
                'Greffe FUE'            => 'greffe-fue',
                'Greffe DHI'            => 'greffe-dhi',
                'Greffe de barbe'       => 'greffe-barbe',
                'Dentisterie'           => 'dentisterie',
                'Autre'                 => 'autre',
            ]);

        yield TextareaField::new('message', 'Message')->hideOnIndex();

        yield ChoiceField::new('statut', 'Statut')
            ->setChoices([
                'Nouveau'  => 'nouveau',
                'Traité'   => 'traite',
                'Archivé'  => 'archive',
            ])
            ->renderAsBadges([
                'nouveau' => 'warning',
                'traite'  => 'success',
                'archive' => 'secondary',
            ]);

        yield DateTimeField::new('createdAt', 'Reçu le')
            ->setFormat('d/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $a) => $a->setIcon('fa fa-eye')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $a) => $a->setIcon('fa fa-pen')->setLabel(''))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $a) => $a->setIcon('fa fa-trash')->setLabel(''));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DateTimeFilter::new('createdAt', 'Reçu le'))
            ->add(ChoiceFilter::new('motif', 'Soin')->setChoices([
                'Première consultation' => 'consultation',
                'Greffe FUE'            => 'greffe-fue',
                'Greffe DHI'            => 'greffe-dhi',
                'Greffe de barbe'       => 'greffe-barbe',
                'Dentisterie'           => 'dentisterie',
                'Autre'                 => 'autre',
            ]))
            ->add(ChoiceFilter::new('statut', 'Statut')->setChoices([
                'Nouveau' => 'nouveau',
                'Traité'  => 'traite',
                'Archivé' => 'archive',
            ]));
    }
}
