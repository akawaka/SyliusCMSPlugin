<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261016144734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE akawaka_cms_page (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_D47FB41B77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('CREATE TABLE akawaka_cms_page_channels (page_id INT NOT NULL, channel_id INT NOT NULL, INDEX IDX_32725DDCC4663E4 (page_id), INDEX IDX_32725DDC72F5A1AA (channel_id), PRIMARY KEY (page_id, channel_id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('CREATE TABLE akawaka_cms_page_translation (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, blocks JSON NOT NULL, metaTitle VARCHAR(255) DEFAULT NULL, metaDescription LONGTEXT DEFAULT NULL, ogImage VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, translatable_id INT NOT NULL, INDEX IDX_36A57D422C2AC5D3 (translatable_id), UNIQUE INDEX akawaka_cms_page_slug_locale (locale, slug), UNIQUE INDEX akawaka_cms_page_translation_unique (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('ALTER TABLE akawaka_cms_page_channels ADD CONSTRAINT FK_32725DDCC4663E4 FOREIGN KEY (page_id) REFERENCES akawaka_cms_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE akawaka_cms_page_channels ADD CONSTRAINT FK_32725DDC72F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE akawaka_cms_page_translation ADD CONSTRAINT FK_36A57D422C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES akawaka_cms_page (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE akawaka_cms_page_channels DROP FOREIGN KEY FK_32725DDCC4663E4');
        $this->addSql('ALTER TABLE akawaka_cms_page_channels DROP FOREIGN KEY FK_32725DDC72F5A1AA');
        $this->addSql('ALTER TABLE akawaka_cms_page_translation DROP FOREIGN KEY FK_36A57D422C2AC5D3');
        $this->addSql('DROP TABLE akawaka_cms_page');
        $this->addSql('DROP TABLE akawaka_cms_page_channels');
        $this->addSql('DROP TABLE akawaka_cms_page_translation');
    }
}
