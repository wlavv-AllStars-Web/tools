<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\modules\tasks\Task;
use App\Models\modules\team\team;

use Illuminate\Support\Facades\Auth;

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
    
    public static function getTokens($id_user){
        
        $encripted_request = base64_encode( openssl_encrypt('all', "AES-256-CBC", hash('sha256', '!allStars@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16)) );
        
        $start = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,5);
        $end   = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,6);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://www.all-stars-motorsport.com/custom/api/getAdminToken.php?section=' . $start . $encripted_request . $end . '&id_user=' . $id_user);

        $token_encrypted = substr($response->getBody(), 5);
        $token_encrypted = substr($token_encrypted, 0, -6);
        
        $token_encrypted_1 = openssl_decrypt( base64_decode($token_encrypted), "AES-256-CBC", hash('sha256', '!all@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16) );
        $token_encrypted_2 = openssl_decrypt( base64_decode($token_encrypted_1), "AES-256-CBC", hash('sha256', '!2024@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16) );
        $tokens =  openssl_decrypt( base64_decode($token_encrypted_2), "AES-256-CBC", hash('sha256', '!allStars@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16) );
        
        return json_decode($tokens);
    }
    
    public static function getTokensASD($id_user){
        
        $encripted_request = base64_encode( openssl_encrypt('all', "AES-256-CBC", hash('sha256', '!allStars@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16)) );
        
        $start = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,5);
        $end   = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,6);

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://www.all-stars-distribution.com/custom/api/getAdminToken.php?section=' . $start . $encripted_request . $end . '&id_user=' . $id_user);

        $token_encrypted = substr($response->getBody(), 5);
        $token_encrypted = substr($token_encrypted, 0, -6);
        
        $token_encrypted_1 = openssl_decrypt( base64_decode($token_encrypted), "AES-256-CBC", hash('sha256', '!all@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16) );
        $token_encrypted_2 = openssl_decrypt( base64_decode($token_encrypted_1), "AES-256-CBC", hash('sha256', '!2024@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16) );
        $tokens =  openssl_decrypt( base64_decode($token_encrypted_2), "AES-256-CBC", hash('sha256', '!allStars@323'), 0, substr(hash('sha256', '1234567891011121'), 0, 16) );
        
        return json_decode($tokens);
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
