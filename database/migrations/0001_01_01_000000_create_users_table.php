<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique(); // Unique pour servir d'identifiant de connexion
            $table->string('password');        // Stockera le mot de passe haché (Bcrypt)
            $table->string('role')->default('agriculteur'); // 'agriculteur' ou 'admin'
            $table->string('telephone')->nullable();
            $table->timestamps();              // created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};