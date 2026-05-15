<?php

namespace App\Models\modules\checklist;

use Illuminate\Database\Eloquent\Model;

class daily_checklist_notes extends Model
{
	protected $table = 'daily_checklist_notes';

	protected $primaryKey = 'id_note';

	public $incrementing = true;

	public $timestamps = true;

	protected $fillable = [
		'id_department',
		'id_user',
		'created_at',
		'updated_at',
		'note',
	];
	
	
	// Alias for clarity
	public function getLastSavedByAttribute()
	{
		return $this->user;
	}
	
	public function lastSavedBy()
	{
		return $this->belongsTo(User::class, 'id_user');
	}
}