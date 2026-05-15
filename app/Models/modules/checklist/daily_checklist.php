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

	public static function ensureTasksForDate(array $departmentIds, $date = null): int
	{
		$date = $date ?: today();
		$created = 0;

		$templates = checklist_templates::query()
			->whereIn('department_id', $departmentIds)
			->where('active', 1)
			->where(function ($query) {
				$query->where('deleted', '!=', 1)
					->orWhereNull('deleted');
			})
			->get();

		foreach ($templates as $template) {
			$exists = self::where('template_id', $template->id)
				->where('department_id', $template->department_id)
				->whereDate('for_date', $date)
				->exists();

			if ($exists) {
				continue;
			}

			self::create([
				'for_date' => $date,
				'template_id' => $template->id,
				'admin_id' => $template->admin_id,
				'employee_id' => $template->employee_id,
				'department_id' => $template->department_id,
				'status' => 'pending',
				'state_priority' => 3,
				'main_task' => 1,
				'created_at' => now(),
				'updated_at' => now(),
			]);

			$created++;
		}

		return $created;
	}
}
