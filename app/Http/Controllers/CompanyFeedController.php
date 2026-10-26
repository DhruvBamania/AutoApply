<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyFeedController extends Controller
{
    /**
     * Authenticated company XML feed preview.
     */
    public function indeedFeed(Request $request): Response
    {
        $user = auth()->user();
        return $this->generateIndeedXml($user);
    }

    /**
     * Public XML feed endpoint for Indeed crawlers / ATS syndication.
     */
    public function publicIndeedFeed(string $companyId): Response
    {
        $user = User::where('id', $companyId)
            ->where('role', 'company')
            ->firstOrFail();

        return $this->generateIndeedXml($user);
    }

    /**
     * Build valid Indeed Job XML according to Indeed Job Feed specification.
     */
    protected function generateIndeedXml(User $companyUser): Response
    {
        $companyName = $companyUser->companyProfile->company_name ?? $companyUser->name;
        $jobs = $companyUser->jobPostings()
            ->where('status', 'active')
            ->latest()
            ->get();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><source></source>');
        $xml->addChild('publisher', config('app.name', 'AutoApply Platform'));
        $xml->addChild('publisherurl', url('/'));
        $xml->addChild('lastBuildDate', now()->toRfc822String());

        foreach ($jobs as $job) {
            $jobNode = $xml->addChild('job');
            $jobNode->addChild('title', htmlspecialchars($job->title, ENT_XML1, 'UTF-8'));
            $jobNode->addChild('date', $job->created_at->toRfc822String());
            $jobNode->addChild('referencenumber', 'JOB-' . $job->id);
            $jobNode->addChild('url', route('jobs.public.show', ['id' => $job->id, 'src' => 'indeed']));
            $jobNode->addChild('company', htmlspecialchars($companyName, ENT_XML1, 'UTF-8'));
            $jobNode->addChild('city', htmlspecialchars($job->location ?? 'Remote', ENT_XML1, 'UTF-8'));
            $jobNode->addChild('country', 'IN');
            $jobNode->addChild('description', htmlspecialchars(strip_tags($job->description), ENT_XML1, 'UTF-8'));
            
            if ($job->salary_range) {
                $jobNode->addChild('salary', htmlspecialchars($job->salary_range, ENT_XML1, 'UTF-8'));
            }
            if ($job->job_type) {
                $jobNode->addChild('jobtype', htmlspecialchars($job->job_type, ENT_XML1, 'UTF-8'));
            }
            if ($job->experience_required) {
                $jobNode->addChild('experience', htmlspecialchars($job->experience_required, ENT_XML1, 'UTF-8'));
            }
            if ($job->image_path) {
                $jobNode->addChild('imageurl', url('storage/' . $job->image_path));
            }
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
