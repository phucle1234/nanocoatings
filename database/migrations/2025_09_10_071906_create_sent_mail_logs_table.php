<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('sent_mail_logs', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('type', 50); // ví dụ: verify_email, reset_password, ...
			$table->string('email', 255);
			$table->string('token', 128)->nullable()->index();
			$table->timestamps();

			$table->index(['user_id', 'type']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('sent_mail_logs');
	}
};