<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240307225213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE price_list (id INT AUTO_INCREMENT NOT NULL, product_id VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, type INT NOT NULL, INDEX IDX_399A0AA24584665A (product_id), UNIQUE INDEX unique_price_list (product_id, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone_number VARCHAR(25) NOT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, type INT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE price_list ADD CONSTRAINT FK_399A0AA24584665A FOREIGN KEY (product_id) REFERENCES product (sku)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE price_list DROP FOREIGN KEY FK_399A0AA24584665A');
        $this->addSql('DROP TABLE price_list');
        $this->addSql('DROP TABLE user');
    }
}
