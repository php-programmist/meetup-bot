<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210206112612 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message ADD update_id INT NOT NULL, CHANGE telegram_date telegram_date DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_update_id ON message (update_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_update_id ON message');
        $this->addSql('ALTER TABLE message DROP update_id, CHANGE telegram_date telegram_date DATETIME NOT NULL');
    }
}
