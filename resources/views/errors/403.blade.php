@extends('errors.layout')

@section('title', '403 — Access Restricted')

@section('content')
    <div class="illustration-container" style="background:#fff1f2;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
    </div>

    <span class="error-code-badge" style="background:#ffe4e6;color:#be123c;">
        Error 403 • Access Restricted
    </span>

    <h1 class="error-title">Access Permission Required</h1>
    <div class="error-gujarati-title">તમને આ મોડ્યુલ ઍક્સેસ કરવાની પરવાનગી નથી</div>

    <p class="error-description">
        {{ $exception->getMessage() ?: 'Your employee staff account does not have authorization to view or manage this section. Please contact the System Owner to request access.' }}
    </p>

    <div class="btn-group">
        <a href="{{ url('/admin') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Back to Allowed Dashboard
        </a>
        <button onclick="window.history.back()" class="btn btn-secondary">
            Go Back
        </button>
    </div>
@endsection
