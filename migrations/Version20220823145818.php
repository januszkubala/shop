<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220823145818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, alt VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(30) NOT NULL, source VARCHAR(30) NOT NULL, file_name VARCHAR(255) NOT NULL, extension VARCHAR(10) NOT NULL, file_size INT NOT NULL, cdn_host VARCHAR(255) DEFAULT NULL, cdn_account VARCHAR(255) DEFAULT NULL, cdn_server VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, allocation_id INT NOT NULL, amount NUMERIC(14, 2) NOT NULL, ref VARCHAR(36) NOT NULL, system_ref VARCHAR(255) DEFAULT NULL, card_brand VARCHAR(255) DEFAULT NULL, card_expiry_date DATE DEFAULT NULL, card_number VARCHAR(255) DEFAULT NULL, method VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL, is_automatic TINYINT(1) NOT NULL, date DATETIME NOT NULL, date_completed DATETIME DEFAULT NULL, INDEX IDX_6D28840DA76ED395 (user_id), INDEX IDX_6D28840D9C83F4B2 (allocation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT DEFAULT NULL, tax_id INT NOT NULL, price NUMERIC(12, 2) NOT NULL, previous_price NUMERIC(12, 2) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, date_valid_from DATETIME DEFAULT NULL, date_valid_to DATETIME DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_CAC822D9A76ED395 (user_id), INDEX IDX_CAC822D94584665A (product_id), INDEX IDX_CAC822D9B2A824D8 (tax_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, parent_id INT DEFAULT NULL, category_id INT DEFAULT NULL, stock INT NOT NULL, date DATETIME NOT NULL, name VARCHAR(80) NOT NULL, sku VARCHAR(30) DEFAULT NULL, ean VARCHAR(13) DEFAULT NULL, gtin VARCHAR(14) DEFAULT NULL, isbn VARCHAR(14) DEFAULT NULL, manufacturer_code VARCHAR(30) DEFAULT NULL, model_number VARCHAR(30) DEFAULT NULL, additional_properties LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', highlights LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_D34A04ADA76ED395 (user_id), INDEX IDX_D34A04AD727ACA70 (parent_id), INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_file (product_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_17714B14584665A (product_id), INDEX IDX_17714B193CB796C (file_id), PRIMARY KEY(product_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property_value (id INT AUTO_INCREMENT NOT NULL, property_id INT NOT NULL, product_id INT NOT NULL, number BIGINT DEFAULT NULL, text VARCHAR(255) DEFAULT NULL, floating_point_number NUMERIC(14, 2) DEFAULT NULL, unit VARCHAR(20) DEFAULT NULL, INDEX IDX_DB649939549213EC (property_id), INDEX IDX_DB6499394584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, stock_change INT NOT NULL, date DATETIME NOT NULL, changes_description VARCHAR(255) DEFAULT NULL, INDEX IDX_4B365660A76ED395 (user_id), INDEX IDX_4B3656604584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9C83F4B2 FOREIGN KEY (allocation_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D94584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D9B2A824D8 FOREIGN KEY (tax_id) REFERENCES tax (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD727ACA70 FOREIGN KEY (parent_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE product_file ADD CONSTRAINT FK_17714B14584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_file ADD CONSTRAINT FK_17714B193CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_value ADD CONSTRAINT FK_DB649939549213EC FOREIGN KEY (property_id) REFERENCES property (id)');
        $this->addSql('ALTER TABLE property_value ADD CONSTRAINT FK_DB6499394584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_file DROP FOREIGN KEY FK_17714B193CB796C');
        $this->addSql('ALTER TABLE order_component DROP FOREIGN KEY FK_F579DAAF4584665A');
        $this->addSql('ALTER TABLE price DROP FOREIGN KEY FK_CAC822D94584665A');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD727ACA70');
        $this->addSql('ALTER TABLE product_file DROP FOREIGN KEY FK_17714B14584665A');
        $this->addSql('ALTER TABLE property_value DROP FOREIGN KEY FK_DB6499394584665A');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604584665A');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE price');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_file');
        $this->addSql('DROP TABLE property_value');
        $this->addSql('DROP TABLE stock');
    }
}
