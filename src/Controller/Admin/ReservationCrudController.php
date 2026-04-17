<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BadgeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Réservation')
            ->setEntityLabelInPlural('Réservations')
            ->setPageTitle('index', 'Liste des réservations')
            ->setPageTitle('detail', fn (Reservation $r) => $r->getNomComplet())
            ->setDefaultSort(['dateRdv' => 'DESC', 'heureRdv' => 'ASC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', '#')->onlyOnIndex();

        yield TextField::new('prenom', 'Prénom');
        yield TextField::new('nom', 'Nom');
        yield EmailField::new('email', 'Email');
        yield TelephoneField::new('telephone', 'Téléphone')->hideOnIndex();

        yield DateField::new('dateRdv', 'Date RDV')
            ->setFormat('EEEE d MMMM yyyy');

        yield TextField::new('heureRdv', 'Heure');

        yield ChoiceField::new('statut', 'Statut')
            ->setChoices([
                'En attente'  => 'en_attente',
                'Confirmé'    => 'confirme',
                'Annulé'      => 'annule',
            ])
            ->renderAsBadges([
                'en_attente' => 'warning',
                'confirme'   => 'success',
                'annule'     => 'danger',
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
            ->add(DateTimeFilter::new('dateRdv', 'Date RDV'))
            ->add(ChoiceFilter::new('statut', 'Statut')->setChoices([
                'En attente' => 'en_attente',
                'Confirmé'   => 'confirme',
                'Annulé'     => 'annule',
            ]));
    }
}
