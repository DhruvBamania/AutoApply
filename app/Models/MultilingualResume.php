<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MultilingualResume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'language_name',
        'language_code',
        'language_flag',
        'template_id',
        'target_role',
        'resume_data',
        'raw_content',
    ];

    protected $casts = [
        'resume_data' => 'array',
        'template_id' => 'integer',
    ];

    /**
     * The candidate user who owns this translated resume.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

