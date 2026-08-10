<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE campaigns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            segment_id INT NULL,
            channel TEXT NOT NULL DEFAULT 'email' CHECK (channel IN ('email','whatsapp','sms','banner')),
            template_subject VARCHAR(200) NULL,
            template_body TEXT NOT NULL,
            coupon_id INT NULL,
            status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','scheduled','sending','sent')),
            scheduled_at DATETIME NULL,
            sent_at DATETIME NULL,
            created_by_user_id INT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT campaigns_segment_fk FOREIGN KEY (segment_id) REFERENCES segments(id) ON DELETE SET NULL,
            CONSTRAINT campaigns_coupon_fk FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL,
            CONSTRAINT campaigns_user_fk FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX campaigns_segment_id_index ON campaigns(segment_id)');
        $pdo->exec('CREATE INDEX campaigns_status_index ON campaigns(status)');
        $pdo->exec(<<<SQL
            CREATE TABLE campaign_recipients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            campaign_id INT NOT NULL,
            user_id INT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','sent','opened','clicked','converted','failed')),
            sent_at DATETIME NULL,
            opened_at DATETIME NULL,
            clicked_at DATETIME NULL,
            converted_at DATETIME NULL,
            order_id INT NULL,
            revenue_attributed_paise INT NOT NULL DEFAULT 0,
            tracking_token VARCHAR(64) NOT NULL,
            CONSTRAINT campaign_recipients_campaign_fk FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
            CONSTRAINT campaign_recipients_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT campaign_recipients_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX campaign_recipients_token_unique ON campaign_recipients(tracking_token)');
        $pdo->exec('CREATE UNIQUE INDEX campaign_recipients_campaign_user_unique ON campaign_recipients(campaign_id, user_id)');
        $pdo->exec('CREATE INDEX campaign_recipients_user_id_index ON campaign_recipients(user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS campaign_recipients');
        $pdo->exec('DROP TABLE IF EXISTS campaigns');
    }
};
