<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'TBL_MS_USUARIO'; // Custom table name
    protected $primaryKey = 'ID_USUARIO'; // Custom primary key
    public $timestamps = false;
    protected $fillable = [
        'ID_ROL',
        'NOMBRE_USUARIO',
        'ID_ESTADO_USUARIO',
        'EMAIL',
        'CONTRASEÑA',
        'PRIMER_INGRESO',
        'FECHA_ULTIMA_CONEXION',
        'FECHA_VENCIMIENTO',
        'google_id',
        'google2fa_secret',
      
       
    ];

    protected $hidden = [
        'CONTRASEÑA',
        'remember_token',
    ];

    protected $casts = [
        'PRIMER_INGRESO' => 'datetime',
        'FECHA_ULTIMA_CONEXION' => 'datetime',
        'FECHA_VENCIMIENTO' => 'datetime',
        
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['CONTRASEÑA'] = bcrypt($password);
    }

    public function getAuthPassword()
    {
        return $this->CONTRASEÑA;
    }


public function adminlte_profile_url()
    {
        return null;
    }

    public function adminlte_desc()
    {
        return null;
    }
    public function getEmailForPasswordReset()
    {
        return $this->EMAIL;
    }

    public function sendPasswordResetNotification($token)
{
    $this->notify(new \App\Notifications\CustomResetPasswordNotification($token));
}
}