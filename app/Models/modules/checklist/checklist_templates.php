<?php

namespace App\Models\modules\checklist;


use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\modules\checklist\daily_checklist;

class checklist_templates extends Model {
	protected $table = 'checklist_templates';
	protected $fillable = ['admin_id','employee_id','department_id','title','description','active','deleted'];
	
	
	public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
	public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
	public function dailyTasks() { return $this->hasMany(daily_checklist::class, 'template_id'); }
	public function statusChanges()
	{
		return $this->hasMany(daily_checklist::class, 'template_id');
	}

}
