<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250426092156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE signature (id INT AUTO_INCREMENT NOT NULL, contrat_id INT NOT NULL, base64 LONGTEXT NOT NULL, signed_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ip VARCHAR(45) NOT NULL, INDEX IDX_AE8801411823061F (contrat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signature ADD CONSTRAINT FK_AE8801411823061F FOREIGN KEY (contrat_id) REFERENCES contrat (idContrat)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE signature DROP FOREIGN KEY FK_AE8801411823061F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE signature
        SQL);
    }
}
