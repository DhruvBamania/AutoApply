<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockInterviewSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'interview_preparation_id',
        'role_title',
        'experience_level',
        'tech_stack',
        'status',
        'current_question_index',
        'total_questions',
        'conversation_history',
        'evaluation_report',
    ];

    protected $casts = [
        'conversation_history' => 'array',
        'evaluation_report' => 'array',
        'current_question_index' => 'integer',
        'total_questions' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interviewPreparation()
    {
        return $this->belongsTo(InterviewPreparation::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Append a message to the conversation history.
     */
    public function addMessage(string $role, string $content, ?int $questionNumber = null): void
    {
        $history = $this->conversation_history ?? [];
        $history[] = [
            'role' => $role, // 'interviewer' or 'candidate'
            'content' => $content,
            'question_number' => $questionNumber ?? $this->current_question_index,
            'timestamp' => now()->toISOString(),
        ];

        $this->conversation_history = $history;
    }
}

