<?php

namespace App\Http\Controllers;

use App\Filament\Resources\FinderLeads\FinderLeadResource;
use App\Http\Requests\StoreFinderLeadRequest;
use App\Models\FinderLead;
use App\Models\FinderQuestion;
use App\Notifications\NewLead;
use Illuminate\Http\JsonResponse;

/** The home page property finder pop-up: saves the visitor's answers and details as a lead. */
class FinderController extends Controller
{
    public function __invoke(StoreFinderLeadRequest $request): JsonResponse
    {
        // Keep only answers that are options of a live question, saved with the question as the visitor saw it
        $sent = (array) $request->validated('answers', []);
        $answers = collect(FinderQuestion::forPopup())
            ->map(function (array $q) use ($sent) {
                $picked = array_values(array_intersect(array_column($q['choices'], 'label'), (array) ($sent[$q['id']] ?? [])));

                return ['question' => $q['title'], 'answers' => $q['multiple'] ? $picked : array_slice($picked, 0, 1)];
            })
            ->filter(fn ($a) => $a['answers'])
            ->values()->all();

        $lead = FinderLead::create($request->safe()->only(['name', 'phone', 'email']) + ['answers' => $answers]);

        NewLead::send('property finder lead', [
            'Name' => $lead->name,
            'Phone' => $lead->phone,
            'Email' => $lead->email,
            ...$lead->answerLines(),
        ], FinderLeadResource::getUrl('view', ['record' => $lead], panel: 'admin'));

        return response()->json(['message' => 'Received']);
    }
}
