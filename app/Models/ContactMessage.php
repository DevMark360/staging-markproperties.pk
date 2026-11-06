<?php

namespace App\Models;

use App\Http\Requests\StoreContactRequest;
use App\Support\ContactLocations;
use App\Support\Projects;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A message sent from the Contact page. */
#[Fillable(['name', 'phone', 'email', 'topic', 'city', 'area', 'message'])]
class ContactMessage extends Model
{
    public function topicLabel(): string
    {
        return StoreContactRequest::TOPICS[$this->topic] ?? $this->topic;
    }

    public function cityLabel(): ?string
    {
        return $this->city ? Projects::cityName($this->city) : null;
    }

    public function areaLabel(): ?string
    {
        return ContactLocations::areaLabel($this->area);
    }
}
