<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user and order tables and seed two users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("CREATE TABLE `order` (
            id INT AUTO_INCREMENT NOT NULL,
            created_by_id INT NOT NULL,
            service VARCHAR(255) NOT NULL,
            price INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            INDEX IDX_F5299398898A72A6 (created_by_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398898A72A6 FOREIGN KEY (created_by_id) REFERENCES user (id)');

        $seedUsersSql = <<<'SQL'
INSERT INTO user (email, roles, password) VALUES
('user1@example.com', '["ROLE_USER"]', '$2y$10$5SggCzFMHkbwVxFrSPzZKeLqRvJe1NGaH1CBSW50LlPiwXnvRTDu.'),
('user2@example.com', '["ROLE_USER"]', '$2y$10$a/ht7FtmYR.1zHGI/EJ04uWl2ghLux5KCdvVQSl1DkGMAkD6/7MXa')
SQL;

        $this->addSql($seedUsersSql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398898A72A6');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE user');
    }
}
