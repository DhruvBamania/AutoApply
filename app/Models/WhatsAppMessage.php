<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'job_posting_id',
        'job_inquiry_id',
        'headhunter_match_id',
        'direction',
        'from_number',
        'to_number',
        'sender_name',
        'sender_role',
        'message',
        'twilio_message_sid',
        'status',
        'error_message',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function jobInquiry()
    {
        return $this->belongsTo(JobInquiry::class);
    }

    public function headhunterMatch()
    {
        return $this->belongsTo(HeadhunterMatch::class);
    }
}
