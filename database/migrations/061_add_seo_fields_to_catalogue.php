<?php

declare(strict_types=1);

/**
 * products.meta_title/meta_description already exist (from 012); this only
 * needed to add the same pair to categories for parity.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE categories ADD COLUMN meta_title VARCHAR(200) NULL');
        $pdo->exec('ALTER TABLE categories ADD COLUMN meta_description VARCHAR(300) NULL');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE categories DROP COLUMN meta_title');
        $pdo->exec('ALTER TABLE categories DROP COLUMN meta_description');
    }
};
