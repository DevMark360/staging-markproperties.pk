<?php

namespace App\Http\Controllers;

use App\Filament\Resources\PartnershipRequests\PartnershipRequestResource;
use App\Http\Requests\StorePartnerRequest;
use App\Models\PartnershipRequest;
use App\Notifications\NewLead;
use App\Support\BuilderStrip;
use App\Support\Projects;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function show(): View
    {
        return view('pages.partner', [
            'stages' => Projects::stages(),
            'types' => Projects::types(),
            'partners' => BuilderStrip::for('partner'), // different builders from the Home and About strips
        ]);
    }

    public function store(StorePartnerRequest $request): JsonResponse|RedirectResponse
    {
        $partner = PartnershipRequest::create($request->safe()->except('website'));

        NewLead::send('partnership request', [
            'Company' => $partner->company,
            'Project' => $partner->project,
            'Stage' => $partner->stageLabel(),
            'Type' => $partner->typeLabel(),
            'City' => $partner->cityLabel(),
            'Name' => $partner->name,
            'Phone' => $partner->phone,
            'Email' => $partner->email,
            'Message' => $partner->message,
        ], PartnershipRequestResource::getUrl('edit', ['record' => $partner], panel: 'admin'));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Received']);
        }

        return back()->with('partner_sent', true)->withFragment('partner-form');
    }
}
