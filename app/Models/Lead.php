<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'campaign_id',
        'company_name',
        'company_website',
        'founder_name',
        'founder_email',
        'job_description',
        'status',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function emailDrafts()
    {
        return $this->hasMany(EmailDraft::class);
    }
}
