<?php

namespace App\Services;

use App\Models\MultilingualResume;
use App\Models\User;
use App\Services\ResumeTemplates\Template1Minimal;
use App\Services\ResumeTemplates\Template2Modern;
use App\Services\ResumeTemplates\Template3Executive;
use App\Services\ResumeTemplates\Template4Creative;
use App\Services\ResumeTemplates\Template5Tech;
use App\Services\ResumeTemplates\Template6Classic;
use App\Services\ResumeTemplates\Template7Elegant;
use App\Services\ResumeTemplates\Template8Clean;
use App\Services\ResumeTemplates\Template9Bold;
use App\Services\ResumeTemplates\Template10Startup;
use App\Services\ResumeTemplates\Template11Corporate;
use App\Services\ResumeTemplates\Template12Academic;
use App\Services\ResumeTemplates\Template13Modern;
use App\Services\ResumeTemplates\Template14Simple;
use App\Services\ResumeTemplates\Template15Dynamic;
use Exception;
use Illuminate\Support\Facades\Log;

class MultilingualResumeService
{
    protected GroqAiService $groq;
    protected GeminiAiService $gemini;

    public function __construct(GroqAiService $groq, GeminiAiService $gemini)
    {
        $this->groq = $groq;
        $this->gemini = $gemini;
    }

    /**
     * Standard curated preset languages with flag emojis and ISO language codes.
     */
    public static function getPresets(): array
    {
        return [
            ['code' => 'de', 'name' => 'German', 'native' => 'Deutsch', 'flag' => '🇩🇪'],
            ['code' => 'es', 'name' => 'Spanish', 'native' => 'Español', 'flag' => '🇪🇸'],
            ['code' => 'fr', 'name' => 'French', 'native' => 'Français', 'flag' => '🇫🇷'],
            ['code' => 'ja', 'name' => 'Japanese', 'native' => '日本語', 'flag' => '🇯🇵'],
            ['code' => 'zh', 'name' => 'Mandarin Chinese', 'native' => '中文', 'flag' => '🇨🇳'],
            ['code' => 'hi', 'name' => 'Hindi', 'native' => 'हिन्दी', 'flag' => '🇮🇳'],
            ['code' => 'ar', 'name' => 'Arabic', 'native' => 'العربية', 'flag' => '🇸🇦'],
            ['code' => 'pt', 'name' => 'Portuguese', 'native' => 'Português', 'flag' => '🇵🇹'],
            ['code' => 'it', 'name' => 'Italian', 'native' => 'Italiano', 'flag' => '🇮🇹'],
            ['code' => 'nl', 'name' => 'Dutch', 'native' => 'Nederlands', 'flag' => '🇳🇱'],
            ['code' => 'ko', 'name' => 'Korean', 'native' => '한국어', 'flag' => '🇰🇷'],
            ['code' => 'ru', 'name' => 'Russian', 'native' => 'Русский', 'flag' => '🇷🇺'],
            ['code' => 'sv', 'name' => 'Swedish', 'native' => 'Svenska', 'flag' => '🇸🇪'],
            ['code' => 'pl', 'name' => 'Polish', 'native' => 'Polski', 'flag' => '🇵🇱'],
            ['code' => 'tr', 'name' => 'Turkish', 'native' => 'Türkçe', 'flag' => '🇹🇷'],
            ['code' => 'id', 'name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'flag' => '🇮🇩'],
        ];
    }

    /**
     * Find preset details for a given language code or name.
     */
    public static function resolveLanguageMeta(string $languageInput): array
    {
        $presets = self::getPresets();
        $inputLower = strtolower(trim($languageInput));

        foreach ($presets as $p) {
            if (strtolower($p['code']) === $inputLower || strtolower($p['name']) === $inputLower) {
                return $p;
            }
        }

        // Custom language fallback
        return [
            'code' => substr(preg_replace('/[^a-z]/', '', $inputLower), 0, 10) ?: 'custom',
            'name' => ucwords(trim($languageInput)),
            'native' => ucwords(trim($languageInput)),
            'flag' => '🌐',
        ];
    }

    /**
     * Translate a candidate's resume into a specified target language.
     *
     * @param User $user
     * @param string $language
     * @param int $templateId
     * @param string|null $targetRole
     * @return MultilingualResume
     * @throws Exception
     */
    public function translateResume(
        User $user,
        string $language,
        int $templateId = 1,
        ?string $targetRole = null
    ): MultilingualResume {
        $meta = self::resolveLanguageMeta($language);
        $langName = $meta['name'];

        // Determine base resume content
        $baseContent = '';
        if (!empty($user->resume_json)) {
            $baseContent = "Structured JSON Resume:\n" . $user->resume_json;
        } elseif (!empty($user->resume_text)) {
            $baseContent = "Raw Text Resume:\n" . $user->resume_text;
        } else {
            throw new Exception("Please upload or build your base resume on the Dashboard first before translating.");
        }

        $roleInstruction = $targetRole ? "Target Role / Specialization: {$targetRole}\n" : "";

        $prompt = <<<EOT
You are an expert multilingual executive resume writer and certified international ATS optimization specialist.
Your mission is to translate and localize the provided resume into {$langName}.

{$roleInstruction}
CRITICAL TRANSLATION & LOCALIZATION GUIDELINES:
1. Fluently and professionally translate all summaries, job titles (where appropriate in {$langName} hiring conventions), role descriptions, responsibilities, bullet points, degrees, and academic terminology into native, natural {$langName}.
2. PRESERVE TECHNICAL INTEGRITY: Universal programming languages, frameworks, developer tools, cloud providers, and industry standard acronyms (e.g., Python, PHP, Laravel, Flutter, Dart, React, AWS, Docker, Kubernetes, SQL, REST APIs, Git, CI/CD, HTML/CSS) MUST be preserved in their standard international technical format.
3. PRESERVE FACTUAL DATA: Retain truthful names, company names, institution names, locations, dates, and metrics (e.g. "increased speed by 40%").
4. Return ONLY valid, well-formed JSON matching the EXACT schema below, with no markdown code fences, no extra text, and no preambles:

{
  "name": "Candidate Full Name",
  "job_title": "Translated Professional Title",
  "contact": "Email | Phone | Location | Links",
  "summary": "Full professional summary paragraph translated into {$langName}",
  "skills": ["Skill 1", "Skill 2", "Skill 3"],
  "experience": [
    {
      "title": "Translated Job Title",
      "company": "Company Name",
      "location": "Location",
      "date": "Employment Period",
      "description": "Translated achievements and bullet points. Use newlines between bullets."
    }
  ],
  "projects": [
    {
      "title": "Project Title",
      "link": "https://...",
      "tech_stack": "Tech Stack Used",
      "date": "Year or Duration",
      "description": "Translated project summary and accomplishments."
    }
  ],
  "education": [
    {
      "degree": "Translated Degree / Qualification",
      "institution": "University / College Name",
      "location": "Location",
      "gpa": "GPA / Grade if applicable",
      "date": "Graduation Year"
    }
  ],
  "certifications": ["Certification 1", "Certification 2"]
}

=== BASE RESUME SOURCE ===
{$baseContent}
EOT;

        $rawResponse = null;

        // Try Groq Llama 3.3 first (fast and high fidelity)
        try {
            $rawResponse = $this->groq->generate($prompt);
        } catch (\Throwable $e) {
            Log::warning("MultilingualResumeService Groq translation failed: " . $e->getMessage());
        }

        // Fallback to Gemini AI if Groq fails or returns empty
        if (empty($rawResponse)) {
            try {
                $rawResponse = $this->gemini->chat($prompt);
            } catch (\Throwable $e) {
                Log::error("MultilingualResumeService Gemini translation failed: " . $e->getMessage());
            }
        }

        if (empty($rawResponse)) {
            throw new Exception("AI translation service could not process your resume into {$langName}. Please try again.");
        }

        $decodedJson = $this->extractAndValidateJson($rawResponse);
        if (!$decodedJson) {
            throw new Exception("AI translation did not return valid JSON for {$langName}. Please retry.");
        }

        // If templateId is outside 1..15, clamp to 1
        $templateId = ($templateId >= 1 && $templateId <= 15) ? $templateId : 1;

        // Persist translated resume
        return MultilingualResume::create([
            'user_id' => $user->id,
            'language_name' => $meta['name'],
            'language_code' => $meta['code'],
            'language_flag' => $meta['flag'],
            'template_id' => $templateId,
            'target_role' => $targetRole,
            'resume_data' => $decodedJson,
            'raw_content' => $rawResponse,
        ]);
    }

    /**
     * Batch translate resume into multiple languages simultaneously.
     *
     * @param User $user
     * @param array $languages Array of language names or codes (e.g. ['German', 'Spanish'])
     * @param int $templateId
     * @param string|null $targetRole
     * @return array Array of created MultilingualResume models
     */
    public function batchTranslate(
        User $user,
        array $languages,
        int $templateId = 1,
        ?string $targetRole = null
    ): array {
        $results = [];
        $errors = [];

        foreach ($languages as $lang) {
            $lang = trim((string)$lang);
            if (empty($lang)) continue;

            try {
                $results[] = $this->translateResume($user, $lang, $templateId, $targetRole);
            } catch (\Throwable $e) {
                $errors[$lang] = $e->getMessage();
                Log::warning("Batch translate failed for {$lang}: " . $e->getMessage());
            }
        }

        if (empty($results) && !empty($errors)) {
            throw new Exception("Failed to translate: " . implode(', ', $errors));
        }

        return $results;
    }

    /**
     * Clean and parse JSON response from LLM output.
     */
    protected function extractAndValidateJson(string $raw): ?array
    {
        $clean = trim($raw);
        // Strip code fences like ```json ... ```
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = trim($clean);

        // Extract JSON block if surrounded by conversational filler
        if (preg_match('/\{[\s\S]*\}/', $clean, $matches)) {
            $clean = $matches[0];
        }

        $decoded = json_decode($clean, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    /**
     * Resolve the template generator instance.
     */
    public function getTemplateService(int $templateId)
    {
        switch ($templateId) {
            case 1:  return new Template1Minimal();
            case 2:  return new Template2Modern();
            case 3:  return new Template3Executive();
            case 4:  return new Template4Creative();
            case 5:  return new Template5Tech();
            case 6:  return new Template6Classic();
            case 7:  return new Template7Elegant();
            case 8:  return new Template8Clean();
            case 9:  return new Template9Bold();
            case 10: return new Template10Startup();
            case 11: return new Template11Corporate();
            case 12: return new Template12Academic();
            case 13: return new Template13Modern();
            case 14: return new Template14Simple();
            case 15: return new Template15Dynamic();
            default: return new Template1Minimal();
        }
    }

    /**
     * Generate HTML preview of the multilingual resume.
     */
    public function generatePreviewHtml(MultilingualResume $resume): string
    {
        $templateService = $this->getTemplateService($resume->template_id);
        $data = $resume->resume_data ?? [];

        $htmlContent = $templateService->generate($data, 'HTML');

        // Extract body inner content for seamless in-page embedding
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $htmlContent, $matches)) {
            return $matches[1];
        }

        return $htmlContent;
    }

    /**
     * Generate Word DOCX binary file for download.
     */
    public function generateDocx(MultilingualResume $resume): string
    {
        $templateService = $this->getTemplateService($resume->template_id);
        $data = $resume->resume_data ?? [];

        return $templateService->generate($data, 'Word2007');
    }
}

