<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422093532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company_info_collection (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, token VARCHAR(255) NOT NULL, is_completed TINYINT(1) DEFAULT 0 NOT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, expires_at DATETIME DEFAULT NULL, contact_name VARCHAR(255) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, internship_start_date DATE DEFAULT NULL, internship_end_date DATE DEFAULT NULL, company_name VARCHAR(255) NOT NULL, address LONGTEXT DEFAULT NULL, address_complement LONGTEXT DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, responsible_last_name VARCHAR(255) DEFAULT NULL, responsible_first_name VARCHAR(255) DEFAULT NULL, responsible_function VARCHAR(255) DEFAULT NULL, landline_phone VARCHAR(20) DEFAULT NULL, mobile_phone VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, siret VARCHAR(255) DEFAULT NULL, insurer_name VARCHAR(255) DEFAULT NULL, insurer_reference VARCHAR(255) DEFAULT NULL, internship_address LONGTEXT DEFAULT NULL, internship_postal_code VARCHAR(10) DEFAULT NULL, internship_city VARCHAR(255) DEFAULT NULL, internship_country VARCHAR(255) DEFAULT NULL, internship_phone VARCHAR(20) DEFAULT NULL, supervisor_last_name VARCHAR(255) DEFAULT NULL, supervisor_first_name VARCHAR(255) DEFAULT NULL, supervisor_function VARCHAR(255) DEFAULT NULL, supervisor_phone VARCHAR(20) DEFAULT NULL, supervisor_email VARCHAR(255) DEFAULT NULL, has_travel TINYINT(1) DEFAULT 0 NOT NULL, covers_transport_costs TINYINT(1) DEFAULT 0 NOT NULL, transport_costs_details LONGTEXT DEFAULT NULL, covers_meal_costs TINYINT(1) DEFAULT 0 NOT NULL, meal_costs_details LONGTEXT DEFAULT NULL, covers_accommodation_costs TINYINT(1) DEFAULT 0 NOT NULL, accommodation_costs_details LONGTEXT DEFAULT NULL, provides_gratification TINYINT(1) DEFAULT 0 NOT NULL, gratification_details LONGTEXT DEFAULT NULL, work_schedule JSON NOT NULL COMMENT \'(DC2Type:json)\', planned_activities LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_EB7963E35F37A13B (token), INDEX IDX_EB7963E3CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE convention (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, referent_professor_id INT DEFAULT NULL, company_info_id INT DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, validated_at DATETIME DEFAULT NULL, signed_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, document_path VARCHAR(255) DEFAULT NULL, document_hash VARCHAR(255) DEFAULT NULL, student_signature_token VARCHAR(255) DEFAULT NULL, student_signed_at DATETIME DEFAULT NULL, company_signature_token VARCHAR(255) DEFAULT NULL, company_signed_at DATETIME DEFAULT NULL, school_signature_token VARCHAR(255) DEFAULT NULL, school_signed_at DATETIME DEFAULT NULL, validation_notes LONGTEXT DEFAULT NULL, rejection_reason LONGTEXT DEFAULT NULL, yousign_request_id VARCHAR(255) DEFAULT NULL, yousign_document_id VARCHAR(255) DEFAULT NULL, yousign_status VARCHAR(50) DEFAULT NULL, INDEX IDX_8556657ECB944F1A (student_id), INDEX IDX_8556657EFBFE69BA (referent_professor_id), UNIQUE INDEX UNIQ_8556657E7DD9DB2F (company_info_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, id_level INT NOT NULL, level_code VARCHAR(255) NOT NULL, level_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE login_attempts (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, ip_address VARCHAR(45) NOT NULL, attempted_at DATETIME NOT NULL, successful TINYINT(1) NOT NULL, INDEX idx_email_attempted_at (email, attempted_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE signature (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, civilite_proviseur VARCHAR(500) NOT NULL, nom_proviseur VARCHAR(255) NOT NULL, prenom_proviseur VARCHAR(255) NOT NULL, email_proviseur VARCHAR(255) NOT NULL, civilite_ddf VARCHAR(500) NOT NULL, nom_ddf VARCHAR(255) NOT NULL, prenom_ddf VARCHAR(255) NOT NULL, email_ddf VARCHAR(255) NOT NULL, tel_ddf VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AE880141B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, referent_level_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, is_verified TINYINT(1) DEFAULT 0 NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, google_authenticator_secret VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, contract_start_date DATE DEFAULT NULL, user_type VARCHAR(255) NOT NULL, personal_email VARCHAR(255) DEFAULT NULL, verification_token VARCHAR(255) DEFAULT NULL, tel_mobile VARCHAR(20) DEFAULT NULL, tel_other VARCHAR(20) DEFAULT NULL, INDEX IDX_8D93D64952E4DFAF (referent_level_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student_level (student_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_12DDB58ECB944F1A (student_id), INDEX IDX_12DDB58E5FB14BA7 (level_id), PRIMARY KEY(student_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE professor_taught_levels (professor_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_5D980DE57D2D84D5 (professor_id), INDEX IDX_5D980DE55FB14BA7 (level_id), PRIMARY KEY(professor_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_info_collection ADD CONSTRAINT FK_EB7963E3CB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE convention ADD CONSTRAINT FK_8556657ECB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE convention ADD CONSTRAINT FK_8556657EFBFE69BA FOREIGN KEY (referent_professor_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE convention ADD CONSTRAINT FK_8556657E7DD9DB2F FOREIGN KEY (company_info_id) REFERENCES company_info_collection (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE signature ADD CONSTRAINT FK_AE880141B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D64952E4DFAF FOREIGN KEY (referent_level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE student_level ADD CONSTRAINT FK_12DDB58ECB944F1A FOREIGN KEY (student_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE student_level ADD CONSTRAINT FK_12DDB58E5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professor_taught_levels ADD CONSTRAINT FK_5D980DE57D2D84D5 FOREIGN KEY (professor_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professor_taught_levels ADD CONSTRAINT FK_5D980DE55FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_info_collection DROP FOREIGN KEY FK_EB7963E3CB944F1A');
        $this->addSql('ALTER TABLE convention DROP FOREIGN KEY FK_8556657ECB944F1A');
        $this->addSql('ALTER TABLE convention DROP FOREIGN KEY FK_8556657EFBFE69BA');
        $this->addSql('ALTER TABLE convention DROP FOREIGN KEY FK_8556657E7DD9DB2F');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE signature DROP FOREIGN KEY FK_AE880141B03A8386');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D64952E4DFAF');
        $this->addSql('ALTER TABLE student_level DROP FOREIGN KEY FK_12DDB58ECB944F1A');
        $this->addSql('ALTER TABLE student_level DROP FOREIGN KEY FK_12DDB58E5FB14BA7');
        $this->addSql('ALTER TABLE professor_taught_levels DROP FOREIGN KEY FK_5D980DE57D2D84D5');
        $this->addSql('ALTER TABLE professor_taught_levels DROP FOREIGN KEY FK_5D980DE55FB14BA7');
        $this->addSql('DROP TABLE company_info_collection');
        $this->addSql('DROP TABLE convention');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE login_attempts');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE signature');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE student_level');
        $this->addSql('DROP TABLE professor_taught_levels');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
