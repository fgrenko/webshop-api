<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240309210555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, buyer_id INT NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, total_price DOUBLE PRECISION NOT NULL, INDEX IDX_F52993986C755722 (buyer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_price_modificator (order_id INT NOT NULL, price_modificator_id INT NOT NULL, INDEX IDX_98B945198D9F6D38 (order_id), INDEX IDX_98B94519F4C1BB17 (price_modificator_id), PRIMARY KEY(order_id, price_modificator_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_product (id INT AUTO_INCREMENT NOT NULL, product_id VARCHAR(64) NOT NULL, order_item_id INT NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_2530ADE64584665A (product_id), INDEX IDX_2530ADE6E415FB15 (order_item_id), UNIQUE INDEX unique_order_product (product_id, order_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price_modificator (id INT AUTO_INCREMENT NOT NULL, type INT NOT NULL, name VARCHAR(255) NOT NULL, percentage DOUBLE PRECISION NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX unique_product_category (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993986C755722 FOREIGN KEY (buyer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_price_modificator ADD CONSTRAINT FK_98B945198D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_price_modificator ADD CONSTRAINT FK_98B94519F4C1BB17 FOREIGN KEY (price_modificator_id) REFERENCES price_modificator (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE64584665A FOREIGN KEY (product_id) REFERENCES product (sku)');
        $this->addSql('ALTER TABLE order_product ADD CONSTRAINT FK_2530ADE6E415FB15 FOREIGN KEY (order_item_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993986C755722');
        $this->addSql('ALTER TABLE order_price_modificator DROP FOREIGN KEY FK_98B945198D9F6D38');
        $this->addSql('ALTER TABLE order_price_modificator DROP FOREIGN KEY FK_98B94519F4C1BB17');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE64584665A');
        $this->addSql('ALTER TABLE order_product DROP FOREIGN KEY FK_2530ADE6E415FB15');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_price_modificator');
        $this->addSql('DROP TABLE order_product');
        $this->addSql('DROP TABLE price_modificator');
    }
}
