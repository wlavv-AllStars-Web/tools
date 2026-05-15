<?php

namespace App\Models\modules\checklist;


use Illuminate\Database\Eloquent\Model;


class checklist_statusChange extends Model {
	public $timestamps = false;
	protected $fillable = ['daily_checklist_id','changed_by','from_status','to_status','changed_at'];
	protected $casts = [ 'changed_at' => 'datetime' ];
	
	
	public function task() { return $this->belongsTo(daily_checklist::class, 'daily_checklist_id'); }
	public function user() { return $this->belongsTo(User::class, 'changed_by'); }
}