<x-error-page code="419" title="This page was open too long"
    message="For your security, forms expire after a while. Go back, refresh the page and send it again. Your details were not submitted.">
    <a href="{{ url()->previous() }}" class="btn btn-primary">Go back and try again</a>
    <a href="{{ route('home') }}" class="btn btn-outline">Home</a>
</x-error-page>
