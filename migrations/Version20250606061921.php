<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606061921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE order_details (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, related_order_id INT NOT NULL, quantity INT NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, INDEX IDX_845CA2C14584665A (product_id), INDEX IDX_845CA2C12B1C2395 (related_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_detail (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, payment_id INT NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_B3EE4054584665A (product_id), INDEX IDX_B3EE4054C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C14584665A FOREIGN KEY (product_id) REFERENCES products (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C12B1C2395 FOREIGN KEY (related_order_id) REFERENCES orders (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_detail ADD CONSTRAINT FK_B3EE4054584665A FOREIGN KEY (product_id) REFERENCES products (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_detail ADD CONSTRAINT FK_B3EE4054C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD date DATETIME NOT NULL, ADD status VARCHAR(150) NOT NULL, CHANGE name invoice VARCHAR(255) NOT NULL
        SQL);
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
            ALTER TABLE products ADD stripe_price_id VARCHAR(150) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD first_name VARCHAR(150) DEFAULT NULL, ADD last_name VARCHAR(150) DEFAULT NULL, DROP username
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE order_details DROP FOREIGN KEY FK_845CA2C14584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_details DROP FOREIGN KEY FK_845CA2C12B1C2395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_detail DROP FOREIGN KEY FK_B3EE4054584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_detail DROP FOREIGN KEY FK_B3EE4054C3A3BB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_details
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment_detail
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
        $this->addSql(<<<'SQL'
            ALTER TABLE products DROP stripe_price_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP date, DROP status, CHANGE invoice name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD username VARCHAR(25) NOT NULL, DROP first_name, DROP last_name
        SQL);
    }
}
