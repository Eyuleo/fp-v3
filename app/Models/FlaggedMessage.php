<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlaggedMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'message_id',
        'flagged_reason',
        'reviewed_by_id',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'created_at'  => 'datetime',
        ];
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }
}
