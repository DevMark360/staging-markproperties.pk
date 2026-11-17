<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Someone who answered the home page property finder and left their details. */
#[Fillable(['name', 'phone', 'email', 'answers'])]
class FinderLead extends Model
{
    protected function casts(): array
    {
        return ['answers' => 'array'];
    }

    /** @return array<string, string> question => its answers, joined */
    public function answerLines(): array
    {
        return collect($this->answers ?? [])
            ->mapWithKeys(fn ($a) => [(string) ($a['question'] ?? '') => implode(', ', (array) ($a['answers'] ?? []))])
            ->filter()
            ->all();
    }

    /** Every answer on one line, for the table and the CSV */
    public function answersSummary(): string
    {
        return implode('; ', array_values($this->answerLines()));
    }
}
