<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index unique sur (date_rdv, heure_rdv) pour éviter les doubles réservations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX uniq_reservation_date_heure ON reservation (date_rdv, heure_rdv)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_reservation_date_heure ON reservation');
    }
}
