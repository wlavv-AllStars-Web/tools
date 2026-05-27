<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\modules\tasks\Task;
use App\Models\modules\team\team;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [ 'name', 'email', 'password', 'role', 'admin_id', 'id_team' ];
    protected $hidden = [ 'password', 'remember_token' ];
    protected $casts = [ 'email_verified_at' => 'datetime', 'password' => 'hashed' ];
    
   /*
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isEmployee(): bool { return $this->role === 'employee'; }
    */
    public function employees()
    {
        return $this->hasMany(User::class, 'admin_id', 'id')->where('role', 'employee');
    }
    
    // For employees: get all admins assigned
    public function admins()
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }
    
    
    public function taskTemplates() { // como admin
    return $this->hasMany(TaskTemplate::class, 'admin_id');
    }
    
    
    public function myTemplates() { // como employee
    return $this->hasMany(TaskTemplate::class, 'employee_id');
    }
    
    
    public function dailyTasks() {
    return $this->hasMany(DailyTask::class, 'employee_id');
    }
    
    /**
     * Deprecated for PrestaShop 9.
     *
     * PrestaShop BO links are now generated through App\Services\Prestashop\PrestashopAdminLinkService
     * and the asgwebtoolsbridge module, instead of calling external getAdminToken.php endpoints.
     *
     * Kept only to avoid fatal errors in old code paths while the dashboards are migrated.
     */
    public static function getTokens($id_user)
    {
        return (object) [];
    }

    /**
     * Deprecated for PrestaShop 9. See getTokens().
     */
    public static function getTokensASD($id_user)
    {
        return (object) [];
    }

    public function getDepartmentNameAttribute()
    {
        $departments = [
            1 => 'Administração',
            2 => 'Logistica',
            3 => 'Vendas',
            4 => 'Marketing',
            5 => 'TI',
            6 => 'RH',
            7 => 'Financeiro',
    
        ];
    
        return $departments[$this->department_id] ?? 'Permanência';
    }




    /* =====================================================
     | Relationships
     ===================================================== */

    public function team()
    {
        return $this->belongsTo(Team::class, 'id_team');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_user_id');
    }

    /* =====================================================
     | Role helpers
     ===================================================== */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /* =====================================================
     | Scopes (optional but useful)
     ===================================================== */

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }
    
}
