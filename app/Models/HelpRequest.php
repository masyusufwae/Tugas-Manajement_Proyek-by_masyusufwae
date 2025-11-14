<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpRequest extends Model
{
    protected $primaryKey = 'help_request_id';
    public $timestamps = true;

    protected $fillable = [
        'subtask_id',
        'requester_id',
        'team_lead_id',
        'message',
        'status',
        'response'
    ];

    public function subtask()
    {
        return $this->belongsTo(Subtask::class, 'subtask_id', 'subtask_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id');
    }

    public function teamLead()
    {
        return $this->belongsTo(User::class, 'team_lead_id', 'user_id');
    }
}
