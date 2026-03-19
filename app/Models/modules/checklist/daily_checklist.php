<?php

namespace App\Models\modules\checklist;


use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\modules\checklist\checklist_statusChange;
use App\Models\modules\checklist\checklist_templates;

class daily_checklist extends Model {
	protected $table = 'daily_checklist';
	
	protected $fillable = ['for_date','template_id','admin_id','employee_id','department_id' ,'status','notes','state_priority','created_at','updated_at','main_task'];
	protected $casts = [ 'for_date' => 'date' ];
	
	
	public function template() { return $this->belongsTo(checklist_templates::class, 'template_id'); }
	public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
	public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
	public function statusChanges() { return $this->hasMany(checklist_statusChange::class); }
	public function changedByUser()
	{
		return $this->belongsTo(User::class, 'changed_by');
	}
}