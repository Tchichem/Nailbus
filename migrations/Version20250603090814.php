<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603090814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D591CC992
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6D28840D591CC992 ON payment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment CHANGE course_id product_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D4584665A FOREIGN KEY (product_id) REFERENCES products (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6D28840D4584665A ON payment (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE products ADD stripe_price_id INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE products DROP stripe_price_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D4584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6D28840D4584665A ON payment
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment CHANGE product_id course_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D591CC992 FOREIGN KEY (course_id) REFERENCES products (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6D28840D591CC992 ON payment (course_id)
        SQL);
    }
}
