<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewPreparation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role_title',
        'experience_level',
        'tech_stack',
        'topics_data',
        'total_questions',
        'bookmarked_ids',
    ];

    protected $casts = [
        'topics_data' => 'array',
        'bookmarked_ids' => 'array',
        'total_questions' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a specific question ID is bookmarked.
     */
    public function isQuestionBookmarked(string $questionId): bool
    {
        $bookmarks = $this->bookmarked_ids ?? [];
        return in_array($questionId, $bookmarks);
    }

    /**
     * Toggle bookmark state for a given question ID.
     */
    public function toggleBookmark(string $questionId): bool
    {
        $bookmarks = $this->bookmarked_ids ?? [];
        if (in_array($questionId, $bookmarks)) {
            $bookmarks = array_values(array_diff($bookmarks, [$questionId]));
            $isBookmarked = false;
        } else {
            $bookmarks[] = $questionId;
            $isBookmarked = true;
        }

        $this->bookmarked_ids = $bookmarks;
        $this->save();

        return $isBookmarked;
    }
}
