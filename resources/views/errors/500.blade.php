@extends('errors.layout')

@section('title', '500 — Server Encountered an Issue')

@section('content')
    <div class="illustration-container" style="background:#fef2f2;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
    </div>

    <span class="error-code-badge" style="background:#fee2e2;color:#b91c1c;">
        Error 500 • Internal System Error
    </span>

    <h1 class="error-title">Server Encountered an Issue</h1>
    <div class="error-gujarati-title">સિસ્ટમમાં ક્ષણિક ખામી આવી છે</div>

    <p class="error-description">
        An unexpected error occurred while processing this request. Our engineering team has been notified. Your data is safe; please refresh the page or return to the dashboard.
    </p>

    <div class="btn-group">
        <button onclick="window.location.reload()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
            Try Again (Refresh)
        </button>
        <a href="{{ url('/admin') }}" class="btn btn-secondary">
            Return to Dashboard
        </a>
    </div>
@endsection
