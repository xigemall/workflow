<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','description','form_type_id','sort'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function fields()
    {
        return $this->hasMany(Field::class)->whereNull('form_grid_id')->orderBy('sort','asc');
    }

    public function grid()
    {
        return $this->hasMany(FormGrid::class);
    }

    public function grids()
    {
        return $this->hasMany(FormGrid::class);
    }
}
