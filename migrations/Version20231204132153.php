<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231204132153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__meta_tag AS SELECT id, page_id, name, content FROM meta_tag');
        $this->addSql('DROP TABLE meta_tag');
        $this->addSql('CREATE TABLE meta_tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, page_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, content VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_AC05690EC4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO meta_tag (id, page_id, name, content) SELECT id, page_id, name, content FROM __temp__meta_tag');
        $this->addSql('DROP TABLE __temp__meta_tag');
        $this->addSql('CREATE INDEX IDX_AC05690EC4663E4 ON meta_tag (page_id)');
        $this->addSql('ALTER TABLE page ADD COLUMN issues CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meta_tag ADD COLUMN details CLOB DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, url, title, description FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, description CLOB DEFAULT NULL)');
        $this->addSql('INSERT INTO page (id, url, title, description) SELECT id, url, title, description FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
    }
}
