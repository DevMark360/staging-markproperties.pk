<?php

namespace App\Http\Controllers;

use App\Filament\Resources\LaunchAlerts\LaunchAlertResource;
use App\Http\Requests\StoreLaunchAlertRequest;
use App\Models\LaunchAlert;
use App\Notifications\NewLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LaunchAlertController extends Controller
{
    public function __invoke(StoreLaunchAlertRequest $request): JsonResponse|RedirectResponse
    {
        // Repeat sign-ups (same email or phone) update the existing subscriber instead of adding a row
        $alert = LaunchAlert::subscribe(
            $request->validated('email'),
            $request->validated('phone'),
            $request->validated('city'),
        );

        // Only new subscribers: a repeat sign-up just refreshes the date
        if ($alert->wasRecentlyCreated) {
            NewLead::send('launch-alert sign-up', [
                'Email' => $alert->email,
                'Phone' => $alert->phone,
                'City' => $alert->cityLabel() ?? 'Any city',
            ], LaunchAlertResource::getUrl('edit', ['record' => $alert], panel: 'admin'));
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Subscribed']);
        }

        return back()->with('alert_subscribed', true)->withFragment('alerts');
    }
}
