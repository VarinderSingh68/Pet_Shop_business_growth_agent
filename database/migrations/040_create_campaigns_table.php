<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE campaigns (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                segment_id INT UNSIGNED NULL,
                channel ENUM('email','whatsapp','sms','banner') NOT NULL DEFAULT 'email',
                template_subject VARCHAR(200) NULL,
                template_body TEXT NOT NULL,
                coupon_id INT UNSIGNED NULL,
                status ENUM('draft','scheduled','sending','sent') NOT NULL DEFAULT 'draft',
                scheduled_at DATETIME NULL,
                sent_at DATETIME NULL,
                created_by_user_id INT UNSIGNED NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY campaigns_segment_id_index (segment_id),
                KEY campaigns_status_index (status),
                CONSTRAINT campaigns_segment_fk FOREIGN KEY (segment_id) REFERENCES segments(id) ON DELETE SET NULL,
                CONSTRAINT campaigns_coupon_fk FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
                CONSTRAINT campaigns_user_fk FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $pdo->exec(<<<SQL
            CREATE TABLE campaign_recipients (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                campaign_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                status ENUM('pending','sent','opened','clicked','converted','failed') NOT NULL DEFAULT 'pending',
                sent_at DATETIME NULL,
                opened_at DATETIME NULL,
                clicked_at DATETIME NULL,
                converted_at DATETIME NULL,
                order_id INT UNSIGNED NULL,
                revenue_attributed_paise INT UNSIGNED NOT NULL DEFAULT 0,
                tracking_token VARCHAR(64) NOT NULL,
                UNIQUE KEY campaign_recipients_token_unique (tracking_token),
                UNIQUE KEY campaign_recipients_campaign_user_unique (campaign_id, user_id),
                KEY campaign_recipients_user_id_index (user_id),
                CONSTRAINT campaign_recipients_campaign_fk FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
                CONSTRAINT campaign_recipients_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT campaign_recipients_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS campaign_recipients');
        $pdo->exec('DROP TABLE IF EXISTS campaigns');
    }
};
