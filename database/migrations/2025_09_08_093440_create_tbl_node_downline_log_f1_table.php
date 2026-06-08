<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('tbl_node_downline_log_f1', function (Blueprint $table) {
			$table->bigIncrements('ID');
			$table->unsignedBigInteger('UserID')->index();
			$table->unsignedBigInteger('FUserID')->index();
			$table->timestamp('DateCreate')->useCurrent();
			$table->unsignedBigInteger('IndirectID')->nullable()->index();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tbl_node_downline_log_f1');
	}
};