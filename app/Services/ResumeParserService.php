<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;

class ResumeParserService
{
    /**
     * Parses a PDF resume and saves the extracted text to the user.
     *
     * @param UploadedFile $file
     * @param User $user
     * @return string
     */
    public function parseAndSave(UploadedFile $file, User $user): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($file->getRealPath());
        $text = $pdf->getText();
        
        // Parse the text into structured JSON using AI
        $groq = new GroqAiService();
        $prompt = "You are an expert AI resume parser. I will provide raw resume text. You MUST extract and structure the data into valid JSON. Do NOT summarize or shorten descriptions; extract EVERYTHING exactly as written.\n"
                . "Use exactly this JSON schema:\n"
                . "{\n"
                . "  \"name\": \"Full Name\",\n"
                . "  \"job_title\": \"Job title (e.g. Full-Stack Developer...)\",\n"
                . "  \"contact\": \"Email | Phone | Location | Links\",\n"
                . "  \"summary\": \"Full professional summary paragraph\",\n"
                . "  \"skills\": [\"skill1\", \"skill2\"],\n"
                . "  \"experience\": [{\"title\": \"\", \"company\": \"\", \"location\": \"\", \"date\": \"\", \"description\": \"Extract all bullet points word-for-word here. Use newlines for each bullet.\" }],\n"
                . "  \"projects\": [{\"title\": \"\", \"link\": \"\", \"tech_stack\": \"\", \"date\": \"\", \"description\": \"Extract all details here.\" }],\n"
                . "  \"education\": [{\"degree\": \"\", \"institution\": \"\", \"location\": \"\", \"gpa\": \"\", \"date\": \"\"}],\n"
                . "  \"certifications\": [\"cert1\", \"cert2\"]\n"
                . "}\n"
                . "Respond ONLY with the raw JSON object. Do not include markdown formatting.\n\n"
                . "Resume Text:\n" . $text;
        
        $jsonStr = $groq->generate($prompt);
        
        // Use regex to extract ONLY the JSON part in case the AI added chatty text around it
        $jsonStr = trim($jsonStr);
        if (preg_match('/\[[\s\S]*\]/', $jsonStr, $matches)) {
            $jsonStr = $matches[0];
            $arr = json_decode($jsonStr, true);
            if (is_array($arr) && isset($arr[0])) {
                $jsonStr = json_encode($arr[0]);
            }
        } elseif (preg_match('/\{[\s\S]*\}/', $jsonStr, $matches)) {
            $jsonStr = $matches[0];
        }
        
        // Validate if it's actually JSON before saving
        $jsonDecoded = json_decode($jsonStr, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $user->update([
                'resume_text' => $text,
                'resume_json' => $jsonStr
            ]);
        } else {
            // Fallback if parsing failed
            $user->update([
                'resume_text' => $text
            ]);
            \Illuminate\Support\Facades\Log::error('Resume JSON parsing failed: ' . json_last_error_msg());
        }
        
        return $text;
    }
}
