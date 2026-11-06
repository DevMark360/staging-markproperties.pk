<x-error-page code="429" title="Too many requests"
    message="We received several requests from you in a short time. Please wait a minute and try again.">
    <a href="{{ route('home') }}" class="btn btn-primary">Home</a>
</x-error-page>
