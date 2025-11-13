<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class OmniUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'omni_user';
    protected $primaryKey = 'userid';
    public $timestamps = false;    

  protected $fillable = [
        'username','password','usercode','user_type','user_email',
        'created_date','created_by','modified_date','modified_by','status'
    ];
    protected $hidden = ['password'];

    // Important: if you want Laravel’s Auth to use this column
    public function getAuthPassword() {
        return $this->password;
    }

    public function getAuthIdentifierName()
    {
        return 'userid';
    }

    public function apiKeys()
    {
        return $this->hasMany(UserApiKey::class, 'user_id', 'userid');
    }
}
