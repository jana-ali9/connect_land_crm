<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'groub_by',
    ];

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'permission_roles', 'permission_id', 'role_id');
    }

}
