<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_title',
        'company_name',
        'location',
        'salary',
        'job_type',
        'job_url',
        'platform',
        'job_description',
        'match_score',
        'match_analysis',
        'status',
        'applied_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'match_analysis' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

