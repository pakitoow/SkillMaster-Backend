<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215214947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE role_skill (id SERIAL NOT NULL, role_id INT NOT NULL, skill_id INT NOT NULL, required_level SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A9E10C58D60322AC ON role_skill (role_id)');
        $this->addSql('CREATE INDEX IDX_A9E10C585585C142 ON role_skill (skill_id)');
        $this->addSql('ALTER TABLE role_skill ADD CONSTRAINT FK_A9E10C58D60322AC FOREIGN KEY (role_id) REFERENCES role (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE role_skill ADD CONSTRAINT FK_A9E10C585585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD target_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E91D6B9BC7 FOREIGN KEY (target_role_id) REFERENCES role (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1483A5E91D6B9BC7 ON users (target_role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE role_skill DROP CONSTRAINT FK_A9E10C58D60322AC');
        $this->addSql('ALTER TABLE role_skill DROP CONSTRAINT FK_A9E10C585585C142');
        $this->addSql('DROP TABLE role_skill');
        $this->addSql('ALTER TABLE "users" DROP CONSTRAINT FK_1483A5E91D6B9BC7');
        $this->addSql('DROP INDEX IDX_1483A5E91D6B9BC7');
        $this->addSql('ALTER TABLE "users" DROP target_role_id');
    }
}
