<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE appointments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                booking_number VARCHAR(30) NOT NULL,
                service_id INT UNSIGNED NOT NULL,
                staff_id INT UNSIGNED NOT NULL,
                slot_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NULL,
                pet_id INT UNSIGNED NULL,
                guest_name VARCHAR(150) NULL,
                guest_email VARCHAR(191) NULL,
                guest_phone VARCHAR(20) NULL,
                status ENUM('booked','confirmed','completed','cancelled','no_show') NOT NULL DEFAULT 'booked',
                payment_status ENUM('not_required','pending','deposit_paid','paid') NOT NULL DEFAULT 'not_required',
                deposit_paise INT UNSIGNED NULL,
                customer_notes VARCHAR(500) NULL,
                internal_notes VARCHAR(500) NULL,
                cancelled_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY appointments_booking_number_unique (booking_number),
                KEY appointments_user_id_index (user_id),
                KEY appointments_staff_id_index (staff_id),
                KEY appointments_slot_id_index (slot_id),
                CONSTRAINT appointments_service_fk FOREIGN KEY (service_id) REFERENCES services(id),
                CONSTRAINT appointments_staff_fk FOREIGN KEY (staff_id) REFERENCES staff_members(id),
                CONSTRAINT appointments_slot_fk FOREIGN KEY (slot_id) REFERENCES service_slots(id),
                CONSTRAINT appointments_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                CONSTRAINT appointments_pet_fk FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS appointments');
    }
};
