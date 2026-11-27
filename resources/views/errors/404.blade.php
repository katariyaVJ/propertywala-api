@extends('errors.layout')

@section('title', '404 — Page Not Found')

@section('content')
    <div class="illustration-container" style="background:#f5f3ff;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            <line x1="8" y1="11" x2="14" y2="11"></line>
        </svg>
    </div>

    <span class="error-code-badge" style="background:#ede9fe;color:#6d28d9;">
        Error 404 • Missing Resource
    </span>

    <h1 class="error-title">Page or Record Not Found</h1>
    <div class="error-gujarati-title">આ પેજ અથવા વિગત ઉપલબ્ધ નથી</div>

    <p class="error-description">
        {{ $exception->getMessage() ?: 'The property, lead, expert profile or admin page you are looking for does not exist, has been removed, or the link has expired.' }}
    </p>

    <div class="btn-group">
        <a href="{{ url('/admin') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Return to Admin Console
        </a>
        <button onclick="window.history.back()" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Go Back
        </button>
    </div>
@endsection
