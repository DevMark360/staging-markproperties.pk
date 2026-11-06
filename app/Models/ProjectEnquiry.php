<?php

namespace App\Models;

use App\Http\Requests\StoreEnquiryRequest;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** An enquiry sent from a project page. */
#[Fillable(['project_slug', 'project_name', 'name', 'phone', 'email', 'interest', 'message'])]
class ProjectEnquiry extends Model
{
    /** Human label for the interest key, e.g. "site-visit" → "Site visit". */
    public function interestLabel(): string
    {
        // "pricing" was offered until 2026-11-03; older rows keep a readable label
        return StoreEnquiryRequest::INTERESTS[$this->interest] ?? ['pricing' => 'Price (old option)'][$this->interest] ?? $this->interest;
    }
}
