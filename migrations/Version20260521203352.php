<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Formatted for PostgreSQL
 */
final class Version20260521203352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add delivery fields to order table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD delivery_method VARCHAR(50) DEFAULT NULL, ADD delivery_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP delivery_method, DROP delivery_address');
    }
}