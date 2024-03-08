<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240308011342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract_list (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id VARCHAR(64) NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_2CBDB670A76ED395 (user_id), INDEX IDX_2CBDB6704584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contract_list ADD CONSTRAINT FK_2CBDB670A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contract_list ADD CONSTRAINT FK_2CBDB6704584665A FOREIGN KEY (product_id) REFERENCES product (sku)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract_list DROP FOREIGN KEY FK_2CBDB670A76ED395');
        $this->addSql('ALTER TABLE contract_list DROP FOREIGN KEY FK_2CBDB6704584665A');
        $this->addSql('DROP TABLE contract_list');
    }
}
