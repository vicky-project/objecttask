<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create("object_categories", function (Blueprint $table) {
			$table->id();
<<<<<<< HEAD
			$table->string("code")->unique();
=======
			$table->string("code");
>>>>>>> 7e8d77d (updates)
			$table->string("name");
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists("object_categories");
	}
};
