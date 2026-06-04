<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table contact_message';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(150) NOT NULL, email VARCHAR(180) NOT NULL, motif VARCHAR(50) NOT NULL, message LONGTEXT DEFAULT NULL, statut VARCHAR(20) DEFAULT \'nouveau\' NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_message');
    }
}
