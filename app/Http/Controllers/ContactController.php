<?php

namespace App\Http\Controllers;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Http\Requests\StoreContactRequest;
use App\Models\ContactMessage;
use App\Notifications\NewLead;
use App\Support\ContactLocations;
use App\Support\Projects;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(Request $request): View
    {
        $topic = $request->query('topic');

        return view('pages.contact', [
            'topics' => StoreContactRequest::TOPICS,
            'topic' => array_key_exists($topic, StoreContactRequest::TOPICS) ? $topic : 'buying',
            'locations' => ContactLocations::all(),
        ]);
    }

    public function store(StoreContactRequest $request): JsonResponse|RedirectResponse
    {
        $message = ContactMessage::create($request->safe()->only(['name', 'phone', 'email', 'topic', 'city', 'area', 'message']));

        NewLead::send('contact message', [
            'About' => $message->topicLabel(),
            'Name' => $message->name,
            'Phone' => $message->phone,
            'Email' => $message->email,
            'City' => $message->cityLabel(),
            'Area' => $message->areaLabel(),
            'Message' => $message->message,
        ], ContactMessageResource::getUrl('edit', ['record' => $message], panel: 'admin'));

        // After sending: a link to the projects in the area they chose (or their city, if that area has none listed)
        $projects = $this->projectsLink($message->city, $message->area);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Received', 'projects' => $projects]);
        }

        return back()->with('contact_sent', true)->with('contact_projects', $projects)->withFragment('message');
    }

    /**
     * The projects page for the chosen area, falling back to the city, then to every project, so the link never
     * leads to an empty list. "Not sure yet" goes straight to the city.
     *
     * @return array{url: string, label: string, title: string, note: string}
     */
    private function projectsLink(?string $city, ?string $area): array
    {
        $cityName = $city ? Projects::cityName($city) : null;
        $areaName = ContactLocations::all()[$city]['areas'][$area] ?? null;
        $has = fn (array $query) => Projects::search(Projects::filters($query))->isNotEmpty();

        if ($areaName && $area !== ContactLocations::NOT_SURE && $has(['city' => $city, 'area' => $area])) {
            return ['url' => route('projects.index', ['city' => $city, 'area' => $area]), 'label' => "See projects in {$areaName}",
                'title' => "Projects in {$areaName}", 'note' => "New launches in {$areaName}, {$cityName}."];
        }
        if ($city && $has(['city' => $city])) {
            return ['url' => route('projects.index', ['city' => $city]), 'label' => "See projects in {$cityName}",
                'title' => "Projects in {$cityName}", 'note' => "New launches across {$cityName}."];
        }

        return ['url' => route('projects.index'), 'label' => 'See all new projects',
            'title' => 'New projects across Pakistan', 'note' => 'Every new launch we list.'];
    }
}
