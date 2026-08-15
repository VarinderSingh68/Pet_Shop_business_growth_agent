<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE gift_cards (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code VARCHAR(20) NOT NULL,
            initial_balance_paise INT NOT NULL,
            balance_paise INT NOT NULL,
            recipient_name VARCHAR(150) NULL,
            recipient_email VARCHAR(191) NULL,
            note VARCHAR(255) NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            expires_at DATE NULL,
            issued_by_user_id INT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT gift_cards_issuer_fk FOREIGN KEY (issued_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX gift_cards_code_unique ON gift_cards(code)');

        $pdo->exec(<<<SQL
            CREATE TABLE gift_card_redemptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gift_card_id INT NOT NULL,
            amount_paise INT NOT NULL,
            order_number VARCHAR(30) NULL,
            note VARCHAR(255) NULL,
            redeemed_by_user_id INT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT gift_card_redemptions_card_fk FOREIGN KEY (gift_card_id) REFERENCES gift_cards(id) ON DELETE CASCADE,
            CONSTRAINT gift_card_redemptions_user_fk FOREIGN KEY (redeemed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX gift_card_redemptions_card_id_index ON gift_card_redemptions(gift_card_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS gift_card_redemptions');
        $pdo->exec('DROP TABLE IF EXISTS gift_cards');
    }
};
