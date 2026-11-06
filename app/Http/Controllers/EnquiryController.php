<?php

namespace App\Http\Controllers;

use App\Filament\Resources\ProjectEnquiries\ProjectEnquiryResource;
use App\Http\Requests\StoreEnquiryRequest;
use App\Models\ProjectEnquiry;
use App\Notifications\NewLead;
use App\Support\Projects;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class EnquiryController extends Controller
{
    public function __invoke(StoreEnquiryRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->only(['project', 'name', 'phone', 'email', 'interest', 'message']);
        $project = Projects::find($data['project']);

        $enquiry = ProjectEnquiry::create([
            'project_slug' => $data['project'],
            'project_name' => $project['name'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'interest' => $data['interest'],
            'message' => $data['message'] ?? null,
        ]);

        NewLead::send('project enquiry', [
            'Project' => $enquiry->project_name,
            'Interested in' => $enquiry->interestLabel(),
            'Name' => $enquiry->name,
            'Phone' => $enquiry->phone,
            'Email' => $enquiry->email,
            'Message' => $enquiry->message,
        ], ProjectEnquiryResource::getUrl('edit', ['record' => $enquiry], panel: 'admin'));

        // A brochure request unlocks the download straight away
        $brochure = $data['interest'] === 'brochure' && $project['brochure']
            ? Projects::fileUrl($project['brochure'], 'brochures')
            : null;

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Received', 'brochure' => $brochure]);
        }

        return back()
            ->with('enquiry_sent', true)
            ->with('brochure_url', $brochure)
            ->withFragment('enquire');
    }
}
