<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDraft extends Model
{
    protected $fillable = [
        'lead_id',
        'subject',
        'body',
        'is_approved',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
