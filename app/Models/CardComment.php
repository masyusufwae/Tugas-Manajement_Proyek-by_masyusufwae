<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardComment extends Model
{
    protected $primaryKey = 'comment_id';
    public $timestamps = true;

    protected $fillable = [
        'card_id',
        'user_id',
        'comment',
        'parent_comment_id',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_comment_id', 'comment_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_comment_id', 'comment_id')
            ->with(['user', 'replies'])
            ->orderBy('created_at');
    }
}
