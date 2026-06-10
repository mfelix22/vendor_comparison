<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('creator','supervisor','manager','admin','procurement','viewer') NOT NULL DEFAULT 'creator'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('creator','supervisor','manager','admin') NOT NULL DEFAULT 'creator'");
    }
};
