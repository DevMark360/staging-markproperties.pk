<?php

namespace App\Models;

use App\Support\PakistanCities;
use App\Support\Projects;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** A builder asking to market a project with us (Partner With Us page). */
#[Fillable(['company', 'project', 'stage', 'type', 'city', 'name', 'phone', 'email', 'message'])]
class PartnershipRequest extends Model
{
    public function stageLabel(): string
    {
        return Projects::stageName($this->stage) ?? (string) $this->stage;
    }

    public function typeLabel(): string
    {
        return Projects::typeName($this->type) ?? '';
    }

    public function cityLabel(): string
    {
        return PakistanCities::name($this->city) ?? '';
    }
}
