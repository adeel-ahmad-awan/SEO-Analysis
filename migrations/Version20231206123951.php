<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231206123951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__meta_tag AS SELECT id, name, content FROM meta_tag');
        $this->addSql('DROP TABLE meta_tag');
        $this->addSql('CREATE TABLE meta_tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, page_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, content VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_AC05690EC4663E4 FOREIGN KEY (page_id) REFERENCES page (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO meta_tag (id, name, content) SELECT id, name, content FROM __temp__meta_tag');
        $this->addSql('DROP TABLE __temp__meta_tag');
        $this->addSql('CREATE INDEX IDX_AC05690EC4663E4 ON meta_tag (page_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__meta_tag AS SELECT id, name, content FROM meta_tag');
        $this->addSql('DROP TABLE meta_tag');
        $this->addSql('CREATE TABLE meta_tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, content VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO meta_tag (id, name, content) SELECT id, name, content FROM __temp__meta_tag');
        $this->addSql('DROP TABLE __temp__meta_tag');
    }
}
