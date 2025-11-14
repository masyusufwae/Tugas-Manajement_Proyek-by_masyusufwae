<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'card_id','subtask_id','user_id',
        'start_time','end_time','duration_minutes','description'
    ];

    public function subtask()
    {
        return $this->belongsTo(Subtask::class, 'subtask_id', 'subtask_id');
    }

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
