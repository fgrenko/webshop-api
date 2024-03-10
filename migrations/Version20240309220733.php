<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240309220733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract_list CHANGE price price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE total_price total_price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE order_product CHANGE price price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE price_list CHANGE price price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE price_modificator CHANGE percentage percentage NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE price price NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product CHANGE price price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE price_modificator CHANGE percentage percentage DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE price_list CHANGE price price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE order_product CHANGE price price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE total_price total_price DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE contract_list CHANGE price price DOUBLE PRECISION NOT NULL');
    }
}
