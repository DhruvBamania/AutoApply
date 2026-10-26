<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'category',
        'subject',
        'message',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Formatted Ticket ID for customer reference.
     */
    public function getTicketIdAttribute(): string
    {
        return 'INQ-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Human readable category name.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'candidate_support' => 'Candidate & Job Seeker Support',
            'company_inquiry' => 'Employer & Recruiter Solutions',
            'enterprise_sales' => 'Enterprise & Custom Integrations',
            'bug_report' => 'Technical Support & Bug Report',
            'legal' => 'Legal & Compliance Inquiries',
            default => 'General Inquiry',
        };
    }
}
