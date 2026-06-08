<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentMailLog extends Model
{
	protected $fillable = [
		'user_id',
		'type',
		'email',
		'token',
	];

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}
}