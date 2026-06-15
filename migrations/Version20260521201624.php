<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Formatted for PostgreSQL
 */
final class Version20260521201624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make user_id nullable in order table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ALTER user_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ALTER user_id SET NOT NULL');
    }
}