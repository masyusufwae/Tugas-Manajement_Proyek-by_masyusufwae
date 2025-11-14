<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $primaryKey = 'board_id';
    public $timestamps = false;

    protected $fillable = ['project_id','board_name','description','position'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    // ✅ Tambahkan ini
    public function cards()
    {
        return $this->hasMany(Card::class, 'board_id', 'board_id');
    }
}
