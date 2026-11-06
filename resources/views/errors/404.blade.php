<x-error-page code="404" title="That page is not here any more"
    message="The link may be out of date, or the project may no longer be listed. Start from one of these instead.">
    <a href="{{ route('projects.index') }}" class="btn btn-primary">New projects</a>
    <a href="{{ route('developers.index') }}" class="btn btn-outline">Developers</a>
    <a href="{{ route('contact') }}" class="btn btn-outline">Contact us</a>
</x-error-page>
