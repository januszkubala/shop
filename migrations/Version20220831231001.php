<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220831231001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, tree_root INT DEFAULT NULL, parent_id INT DEFAULT NULL, name VARCHAR(80) NOT NULL, lft INT NOT NULL, lvl INT NOT NULL, rgt INT NOT NULL, INDEX IDX_64C19C1A977936C (tree_root), INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, alt VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(30) NOT NULL, source VARCHAR(30) NOT NULL, file_name VARCHAR(255) NOT NULL, extension VARCHAR(10) NOT NULL, file_size INT NOT NULL, cdn_host VARCHAR(255) DEFAULT NULL, cdn_account VARCHAR(255) DEFAULT NULL, cdn_server VARCHAR(255) DEFAULT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, allocation_id INT NOT NULL, amount NUMERIC(14, 2) NOT NULL, ref VARCHAR(36) NOT NULL, system_ref VARCHAR(255) DEFAULT NULL, card_brand VARCHAR(255) DEFAULT NULL, card_expiry_date DATE DEFAULT NULL, card_number VARCHAR(255) DEFAULT NULL, method VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL, is_automatic TINYINT(1) NOT NULL, date DATETIME NOT NULL, date_completed DATETIME DEFAULT NULL, INDEX IDX_6D28840DA76ED395 (user_id), INDEX IDX_6D28840D9C83F4B2 (allocation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT DEFAULT NULL, tax_id INT NOT NULL, price NUMERIC(12, 2) NOT NULL, previous_price NUMERIC(12, 2) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, date_valid_from DATETIME DEFAULT NULL, date_valid_to DATETIME DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_CAC822D9A76ED395 (user_id), INDEX IDX_CAC822D94584665A (product_id), INDEX IDX_CAC822D9B2A824D8 (tax_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, parent_id INT DEFAULT NULL, category_id INT DEFAULT NULL, default_image_id INT DEFAULT NULL, stock INT NOT NULL, normal_level INT DEFAULT NULL, warning_level INT DEFAULT NULL, critical_level INT DEFAULT NULL, date DATETIME NOT NULL, name VARCHAR(80) NOT NULL, sku VARCHAR(30) DEFAULT NULL, ean VARCHAR(13) DEFAULT NULL, gtin VARCHAR(14) DEFAULT NULL, isbn VARCHAR(14) DEFAULT NULL, manufacturer_code VARCHAR(30) DEFAULT NULL, model_number VARCHAR(30) DEFAULT NULL, additional_properties LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', highlights LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_D34A04ADA76ED395 (user_id), INDEX IDX_D34A04AD727ACA70 (parent_id), INDEX IDX_D34A04AD12469DE2 (category_id), INDEX IDX_D34A04AD3BE11523 (default_image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_file (product_id INT NOT NULL, file_id INT NOT NULL, INDEX IDX_17714B14584665A (product_id), INDEX IDX_17714B193CB796C (file_id), PRIMARY KEY(product_id, file_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(80) NOT NULL, type VARCHAR(20) NOT NULL, units LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property_category (property_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_58CB2D85549213EC (property_id), INDEX IDX_58CB2D8512469DE2 (category_id), PRIMARY KEY(property_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property_value (id INT AUTO_INCREMENT NOT NULL, property_id INT NOT NULL, product_id INT NOT NULL, number BIGINT DEFAULT NULL, text VARCHAR(255) DEFAULT NULL, floating_point_number NUMERIC(14, 2) DEFAULT NULL, unit VARCHAR(20) DEFAULT NULL, INDEX IDX_DB649939549213EC (property_id), INDEX IDX_DB6499394584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sale (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, ref VARCHAR(255) NOT NULL, date DATETIME NOT NULL, net_amount NUMERIC(14, 2) NOT NULL, tax_amount NUMERIC(14, 2) NOT NULL, amount NUMERIC(14, 2) NOT NULL, quantity INT NOT NULL, status VARCHAR(16) NOT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, street VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, region VARCHAR(50) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_E54BC005A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sale_component (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, product_id INT NOT NULL, parent_id INT NOT NULL, net_price NUMERIC(14, 2) NOT NULL, price NUMERIC(14, 2) NOT NULL, quantity INT NOT NULL, tax_amount NUMERIC(14, 2) NOT NULL, tax_rate NUMERIC(5, 2) NOT NULL, tax_name VARCHAR(30) DEFAULT NULL, net_amount NUMERIC(14, 2) NOT NULL, amount NUMERIC(14, 2) NOT NULL, name VARCHAR(80) NOT NULL, sku VARCHAR(30) DEFAULT NULL, ean VARCHAR(13) DEFAULT NULL, gtin VARCHAR(14) DEFAULT NULL, isbn VARCHAR(14) DEFAULT NULL, INDEX IDX_7FE39F4DA76ED395 (user_id), INDEX IDX_7FE39F4D4584665A (product_id), INDEX IDX_7FE39F4D727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, stock_change INT NOT NULL, date DATETIME NOT NULL, changes_description VARCHAR(255) DEFAULT NULL, INDEX IDX_4B365660A76ED395 (user_id), INDEX IDX_4B3656604584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tax (id INT AUTO_INCREMENT NOT NULL, rate NUMERIC(5, 2) NOT NULL, name VARCHAR(30) DEFAULT NULL, is_default TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(50) DEFAULT NULL, last_name VARCHAR(50) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, street VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, region VARCHAR(50) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, date_of_registration DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1A977936C FOREIGN KEY (tree_root) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9C83F4B2 FOREIGN KEY (allocation_id) REFERENCES sale (id)');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D94584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE price ADD CONSTRAINT FK_CAC822D9B2A824D8 FOREIGN KEY (tax_id) REFERENCES tax (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD727ACA70 FOREIGN KEY (parent_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3BE11523 FOREIGN KEY (default_image_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE product_file ADD CONSTRAINT FK_17714B14584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_file ADD CONSTRAINT FK_17714B193CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_category ADD CONSTRAINT FK_58CB2D85549213EC FOREIGN KEY (property_id) REFERENCES property (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_category ADD CONSTRAINT FK_58CB2D8512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE property_value ADD CONSTRAINT FK_DB649939549213EC FOREIGN KEY (property_id) REFERENCES property (id)');
        $this->addSql('ALTER TABLE property_value ADD CONSTRAINT FK_DB6499394584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE sale ADD CONSTRAINT FK_E54BC005A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale_component ADD CONSTRAINT FK_7FE39F4DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sale_component ADD CONSTRAINT FK_7FE39F4D4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE sale_component ADD CONSTRAINT FK_7FE39F4D727ACA70 FOREIGN KEY (parent_id) REFERENCES sale (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1A977936C');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA76ED395');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9C83F4B2');
        $this->addSql('ALTER TABLE price DROP FOREIGN KEY FK_CAC822D9A76ED395');
        $this->addSql('ALTER TABLE price DROP FOREIGN KEY FK_CAC822D94584665A');
        $this->addSql('ALTER TABLE price DROP FOREIGN KEY FK_CAC822D9B2A824D8');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA76ED395');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD727ACA70');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD3BE11523');
        $this->addSql('ALTER TABLE product_file DROP FOREIGN KEY FK_17714B14584665A');
        $this->addSql('ALTER TABLE product_file DROP FOREIGN KEY FK_17714B193CB796C');
        $this->addSql('ALTER TABLE property_category DROP FOREIGN KEY FK_58CB2D85549213EC');
        $this->addSql('ALTER TABLE property_category DROP FOREIGN KEY FK_58CB2D8512469DE2');
        $this->addSql('ALTER TABLE property_value DROP FOREIGN KEY FK_DB649939549213EC');
        $this->addSql('ALTER TABLE property_value DROP FOREIGN KEY FK_DB6499394584665A');
        $this->addSql('ALTER TABLE sale DROP FOREIGN KEY FK_E54BC005A76ED395');
        $this->addSql('ALTER TABLE sale_component DROP FOREIGN KEY FK_7FE39F4DA76ED395');
        $this->addSql('ALTER TABLE sale_component DROP FOREIGN KEY FK_7FE39F4D4584665A');
        $this->addSql('ALTER TABLE sale_component DROP FOREIGN KEY FK_7FE39F4D727ACA70');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660A76ED395');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604584665A');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE price');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_file');
        $this->addSql('DROP TABLE property');
        $this->addSql('DROP TABLE property_category');
        $this->addSql('DROP TABLE property_value');
        $this->addSql('DROP TABLE sale');
        $this->addSql('DROP TABLE sale_component');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE tax');
        $this->addSql('DROP TABLE user');
    }
}
