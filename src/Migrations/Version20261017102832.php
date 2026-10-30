<?php

declare(strict_types=1);

namespace AKAWAKA\SyliusCMSPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017102832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the content blocks attached to twig hooks.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE akawaka_cms_content_block (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, placements JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_29217BBA77153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('CREATE TABLE akawaka_cms_content_block_channels (content_block_id INT NOT NULL, channel_id INT NOT NULL, INDEX IDX_B8CD06C5BB5A68E3 (content_block_id), INDEX IDX_B8CD06C572F5A1AA (channel_id), PRIMARY KEY (content_block_id, channel_id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('CREATE TABLE akawaka_cms_content_block_translation (id INT AUTO_INCREMENT NOT NULL, blocks JSON NOT NULL, locale VARCHAR(255) NOT NULL, translatable_id INT NOT NULL, INDEX IDX_27C3CFB52C2AC5D3 (translatable_id), UNIQUE INDEX akawaka_cms_content_block_translation_unique (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('ALTER TABLE akawaka_cms_content_block_channels ADD CONSTRAINT FK_B8CD06C5BB5A68E3 FOREIGN KEY (content_block_id) REFERENCES akawaka_cms_content_block (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE akawaka_cms_content_block_channels ADD CONSTRAINT FK_B8CD06C572F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE akawaka_cms_content_block_translation ADD CONSTRAINT FK_27C3CFB52C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES akawaka_cms_content_block (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE akawaka_cms_content_block_channels DROP FOREIGN KEY FK_B8CD06C5BB5A68E3');
        $this->addSql('ALTER TABLE akawaka_cms_content_block_channels DROP FOREIGN KEY FK_B8CD06C572F5A1AA');
        $this->addSql('ALTER TABLE akawaka_cms_content_block_translation DROP FOREIGN KEY FK_27C3CFB52C2AC5D3');
        $this->addSql('DROP TABLE akawaka_cms_content_block');
        $this->addSql('DROP TABLE akawaka_cms_content_block_channels');
        $this->addSql('DROP TABLE akawaka_cms_content_block_translation');
    }
}
