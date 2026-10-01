<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250702003410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments ADD service_id INT DEFAULT NULL');
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments ADD CONSTRAINT FK_6A41727AED5CA9E6 FOREIGN KEY (service_id) REFERENCES services (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6A41727AED5CA9E6 ON appointments (service_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727AED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6A41727AED5CA9E6 ON appointments
        SQL);
    }
}
