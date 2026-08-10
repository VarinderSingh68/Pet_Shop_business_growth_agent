<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE appointments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            booking_number VARCHAR(30) NOT NULL,
            service_id INT NOT NULL,
            staff_id INT NOT NULL,
            slot_id INT NOT NULL,
            user_id INT NULL,
            pet_id INT NULL,
            guest_name VARCHAR(150) NULL,
            guest_email VARCHAR(191) NULL,
            guest_phone VARCHAR(20) NULL,
            status TEXT NOT NULL DEFAULT 'booked' CHECK (status IN ('booked','confirmed','completed','cancelled','no_show')),
            payment_status TEXT NOT NULL DEFAULT 'not_required' CHECK (payment_status IN ('not_required','pending','deposit_paid','paid')),
            deposit_paise INT NULL,
            customer_notes VARCHAR(500) NULL,
            internal_notes VARCHAR(500) NULL,
            cancelled_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT appointments_service_fk FOREIGN KEY (service_id) REFERENCES services(id),
            CONSTRAINT appointments_staff_fk FOREIGN KEY (staff_id) REFERENCES staff_members(id),
            CONSTRAINT appointments_slot_fk FOREIGN KEY (slot_id) REFERENCES service_slots(id),
            CONSTRAINT appointments_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            CONSTRAINT appointments_pet_fk FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX appointments_booking_number_unique ON appointments(booking_number)');
        $pdo->exec('CREATE INDEX appointments_user_id_index ON appointments(user_id)');
        $pdo->exec('CREATE INDEX appointments_staff_id_index ON appointments(staff_id)');
        $pdo->exec('CREATE INDEX appointments_slot_id_index ON appointments(slot_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS appointments');
    }
};
