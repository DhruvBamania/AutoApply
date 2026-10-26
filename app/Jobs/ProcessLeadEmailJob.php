<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\GroqAiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ProcessLeadEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Lead $lead
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GroqAiService $groqAi): void
    {
        $this->lead->update(['status' => 'drafted']);

        $user = $this->lead->campaign->user;
        $resumeText = $user->resume_text;

        if (!$resumeText) {
            Log::warning("No resume text found for user {$user->id}. Cannot process lead {$this->lead->id}");
            return;
        }

        // Step 1: The Researcher
        $researchPrompt = "Extract the top 3 core technical skills this company needs based on this job description:\n\n" . 
                          $this->lead->job_description . 
                          "\n\nReturn as a JSON array of strings.";
                          
        $skillsJson = $groqAi->generate($researchPrompt);
        
        // Step 2: The Matchmaker
        $matchPrompt = "Write a 4-sentence cold email to {$this->lead->founder_name}. Explain exactly how the user's experience matches the 3 core skills you identified. Keep it highly specific.\n\n" .
                       "Skills: " . $skillsJson . "\n\n" .
                       "User Resume: " . $resumeText;
                       
        $draft = $groqAi->generate($matchPrompt);
        
        // Step 3: The Humanizer
        $humanizePrompt = "Rewrite this draft to sound casual, confident, and human. Remove all corporate jargon like 'I hope this finds you well' or 'dynamic team player'. Return ONLY the final email body.\n\n" .
                          "Draft: " . $draft;
                          
        $finalEmail = $groqAi->generate($humanizePrompt);

        if (!$finalEmail) {
            Log::error("Failed to generate email for lead {$this->lead->id}");
            return;
        }

        // Save to EmailDraft table
        $emailDraft = $this->lead->emailDrafts()->create([
            'subject' => "Application for " . $this->lead->company_name, // Simplistic static subject, could also be AI-generated
            'body' => $finalEmail,
            'is_approved' => false
        ]);

        // Auto-Send Logic (assuming user has auto-send enabled, simulating flag for MVP)
        $autoSendEnabled = false; // Changed to FALSE so user must review and approve manually

        if ($autoSendEnabled && $this->lead->founder_email) {
            $user = $this->lead->campaign->user;
            
            Mail::raw($finalEmail, function ($message) use ($emailDraft, $user) {
                $message->to($this->lead->founder_email)
                        ->from($user->email, $user->name)
                        ->replyTo($user->email, $user->name)
                        ->subject($emailDraft->subject);
            });

            $this->lead->update(['status' => 'emailed']);
            $emailDraft->update(['is_approved' => true]);
        }
    }
}
