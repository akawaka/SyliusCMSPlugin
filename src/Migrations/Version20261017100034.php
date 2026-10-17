<?php

declare(strict_types=1);

namespace Akawaka\SyliusCMSPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017100034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the CMS homepage (one per channel).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE akawaka_cms_homepage (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, channel_id INT NOT NULL, UNIQUE INDEX UNIQ_26A6312372F5A1AA (channel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('CREATE TABLE akawaka_cms_homepage_translation (id INT AUTO_INCREMENT NOT NULL, blocks JSON NOT NULL, metaTitle VARCHAR(255) DEFAULT NULL, metaDescription LONGTEXT DEFAULT NULL, ogImage VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) NOT NULL, translatable_id INT NOT NULL, INDEX IDX_3932EBFA2C2AC5D3 (translatable_id), UNIQUE INDEX akawaka_cms_homepage_translation_unique (translatable_id, locale), PRIMARY KEY (id)) DEFAULT CHARACTER SET UTF8');
        $this->addSql('ALTER TABLE akawaka_cms_homepage ADD CONSTRAINT FK_26A6312372F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE akawaka_cms_homepage_translation ADD CONSTRAINT FK_3932EBFA2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES akawaka_cms_homepage (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE akawaka_cms_homepage DROP FOREIGN KEY FK_26A6312372F5A1AA');
        $this->addSql('ALTER TABLE akawaka_cms_homepage_translation DROP FOREIGN KEY FK_3932EBFA2C2AC5D3');
        $this->addSql('DROP TABLE akawaka_cms_homepage');
        $this->addSql('DROP TABLE akawaka_cms_homepage_translation');
    }
}
