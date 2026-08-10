<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE growth_actions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                action_type VARCHAR(50) NOT NULL COMMENT 'e.g. abandoned_cart, replenishment, winback, birthday, copilot_suggestion',
                target_type VARCHAR(50) NULL,
                target_id INT UNSIGNED NULL,
                explanation VARCHAR(500) NOT NULL COMMENT 'Plain-English reason, shown in admin',
                affected_count INT UNSIGNED NULL,
                estimated_impact_paise INT UNSIGNED NULL,
                status ENUM('suggested','executed','dismissed') NOT NULL DEFAULT 'executed',
                executed_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                KEY growth_actions_action_type_index (action_type),
                KEY growth_actions_created_at_index (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS growth_actions');
    }
};
