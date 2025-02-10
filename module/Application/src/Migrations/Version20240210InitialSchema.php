<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240210InitialSchema extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Criação do esquema inicial do banco de dados';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM("client", "admin", "employee") NOT NULL DEFAULT "client",
                cpf VARCHAR(14) NOT NULL UNIQUE,
                active BOOLEAN NOT NULL DEFAULT TRUE,
                phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                duration INT NOT NULL COMMENT "Duração em minutos",
                price DECIMAL(10,2) NOT NULL,
                active BOOLEAN DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE appointments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                service_id INT NOT NULL,
                appointment_date DATETIME NOT NULL,
                status ENUM("pending", "confirmed", "cancelled", "completed") NOT NULL DEFAULT "pending",
                notes TEXT,
                cancelled_at DATETIME DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (service_id) REFERENCES services(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $this->addSql('
            CREATE TABLE notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                appointment_id INT NOT NULL,
                type ENUM("email", "sms") NOT NULL,
                status ENUM("pending", "sent", "failed") NOT NULL DEFAULT "pending",
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (appointment_id) REFERENCES appointments(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');

        $this->addSql('CREATE INDEX idx_appointments_date ON appointments(appointment_date)');
        $this->addSql('CREATE INDEX idx_appointments_status ON appointments(status)');
        $this->addSql('CREATE INDEX idx_notifications_status ON notifications(status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS notifications');
        $this->addSql('DROP TABLE IF EXISTS appointments');
        $this->addSql('DROP TABLE IF EXISTS services');
        $this->addSql('DROP TABLE IF EXISTS users');
    }
} 