<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130133156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, id_level INT NOT NULL, level_code INT NOT NULL, level_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student_level (student_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_12DDB58ECB944F1A (student_id), INDEX IDX_12DDB58E5FB14BA7 (level_id), PRIMARY KEY(student_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE student_level ADD CONSTRAINT FK_12DDB58ECB944F1A FOREIGN KEY (student_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_level ADD CONSTRAINT FK_12DDB58E5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(255) NOT NULL, ADD last_name VARCHAR(255) NOT NULL, ADD user_type VARCHAR(255) NOT NULL, ADD personal_email VARCHAR(255) DEFAULT NULL, ADD is_verified TINYINT(1) DEFAULT 0, ADD verification_token VARCHAR(255) DEFAULT NULL, ADD tel_mobile VARCHAR(20) DEFAULT NULL, ADD tel_other VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE student_level DROP FOREIGN KEY FK_12DDB58ECB944F1A');
        $this->addSql('ALTER TABLE student_level DROP FOREIGN KEY FK_12DDB58E5FB14BA7');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE student_level');
        $this->addSql('ALTER TABLE user DROP first_name, DROP last_name, DROP user_type, DROP personal_email, DROP is_verified, DROP verification_token, DROP tel_mobile, DROP tel_other');
    }
}
