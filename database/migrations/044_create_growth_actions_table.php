<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE growth_actions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            action_type VARCHAR(50) NOT NULL,
            target_type VARCHAR(50) NULL,
            target_id INT NULL,
            explanation VARCHAR(500) NOT NULL,
            affected_count INT NULL,
            estimated_impact_paise INT NULL,
            status TEXT NOT NULL DEFAULT 'executed' CHECK (status IN ('suggested','executed','dismissed')),
            executed_at DATETIME NULL,
            created_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX growth_actions_action_type_index ON growth_actions(action_type)');
        $pdo->exec('CREATE INDEX growth_actions_created_at_index ON growth_actions(created_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS growth_actions');
    }
};
