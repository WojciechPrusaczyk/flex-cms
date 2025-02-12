<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250207192859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, account_created DATETIME NOT NULL, last_login_date DATETIME DEFAULT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_880E0D76F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE colors (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, value VARCHAR(63) DEFAULT NULL, type VARCHAR(31) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dashboard_settings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, english_name VARCHAR(255) NOT NULL, icon_file_name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE photos (id INT AUTO_INCREMENT NOT NULL, added_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, safe_file_name VARCHAR(255) NOT NULL, file_type VARCHAR(15) NOT NULL, added_datetime DATETIME NOT NULL, INDEX IDX_876E0D955B127A4 (added_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scripts (id INT AUTO_INCREMENT NOT NULL, added_by_id INT DEFAULT NULL, edited_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, value LONGTEXT DEFAULT NULL, start_being_active DATETIME DEFAULT NULL, stop_being_active DATETIME DEFAULT NULL, INDEX IDX_DD1E973E55B127A4 (added_by_id), INDEX IDX_DD1E973EDD7B2EBC (edited_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sections (id INT AUTO_INCREMENT NOT NULL, added_by_id INT DEFAULT NULL, edited_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, start_being_active DATETIME DEFAULT NULL, stop_being_active DATETIME DEFAULT NULL, value JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', position INT DEFAULT NULL, is_wide TINYINT(1) NOT NULL, is_title_visible TINYINT(1) NOT NULL, INDEX IDX_2B96439855B127A4 (added_by_id), INDEX IDX_2B964398DD7B2EBC (edited_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, type VARCHAR(31) NOT NULL, is_editable TINYINT(1) NOT NULL, is_public TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stylesheets (id INT AUTO_INCREMENT NOT NULL, added_by_id INT DEFAULT NULL, edited_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, value LONGTEXT DEFAULT NULL, start_being_active DATETIME DEFAULT NULL, stop_being_active DATETIME DEFAULT NULL, INDEX IDX_1560D2A955B127A4 (added_by_id), INDEX IDX_1560D2A9DD7B2EBC (edited_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE photos ADD CONSTRAINT FK_876E0D955B127A4 FOREIGN KEY (added_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE scripts ADD CONSTRAINT FK_DD1E973E55B127A4 FOREIGN KEY (added_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE scripts ADD CONSTRAINT FK_DD1E973EDD7B2EBC FOREIGN KEY (edited_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE sections ADD CONSTRAINT FK_2B96439855B127A4 FOREIGN KEY (added_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE sections ADD CONSTRAINT FK_2B964398DD7B2EBC FOREIGN KEY (edited_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE stylesheets ADD CONSTRAINT FK_1560D2A955B127A4 FOREIGN KEY (added_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE stylesheets ADD CONSTRAINT FK_1560D2A9DD7B2EBC FOREIGN KEY (edited_by_id) REFERENCES admin (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photos DROP FOREIGN KEY FK_876E0D955B127A4');
        $this->addSql('ALTER TABLE scripts DROP FOREIGN KEY FK_DD1E973E55B127A4');
        $this->addSql('ALTER TABLE scripts DROP FOREIGN KEY FK_DD1E973EDD7B2EBC');
        $this->addSql('ALTER TABLE sections DROP FOREIGN KEY FK_2B96439855B127A4');
        $this->addSql('ALTER TABLE sections DROP FOREIGN KEY FK_2B964398DD7B2EBC');
        $this->addSql('ALTER TABLE stylesheets DROP FOREIGN KEY FK_1560D2A955B127A4');
        $this->addSql('ALTER TABLE stylesheets DROP FOREIGN KEY FK_1560D2A9DD7B2EBC');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE colors');
        $this->addSql('DROP TABLE dashboard_settings');
        $this->addSql('DROP TABLE photos');
        $this->addSql('DROP TABLE scripts');
        $this->addSql('DROP TABLE sections');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE stylesheets');
    }
}
