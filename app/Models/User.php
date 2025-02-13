<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LaravelAndVueJS\Traits\LaravelPermissionToVueJS;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles,  LaravelPermissionToVueJS;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'employee_id',
        'category_id',
        'can_login',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     // A user belongs to one store
     public function category()
     {
         return $this->belongsTo(Category::class);
     }

     public function payrolls()
     {
         return $this->hasMany(Payroll::class);
     }

     public function activityLogs(){
        return $this->hasMany(ActivityLog::class);
     }

     public static function generateUserCode()
    {
        $year = date('Y'); // Get current year
        $prefix = "ZVL/{$year}/HR/SS/";

        // Get the last user_code from the database
        $lastUser = User::where('employee_id', 'LIKE', "{$prefix}%")
                        ->orderBy('employee_id', 'desc')
                        ->first();

        if ($lastUser) {
            // Extract the last number and increment it
            $lastNumber = (int) substr($lastUser->user_code, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return $prefix . $newNumber;
    }
     
}
