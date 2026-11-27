@extends('errors.layout')

@section('title', '419 — Session Expired')

@section('content')
    <div class="illustration-container" style="background:#fffbeb;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
    </div>

    <span class="error-code-badge" style="background:#fef3c7;color:#b45309;">
        Error 419 • Session Inactive
    </span>

    <h1 class="error-title">Admin Session Expired</h1>
    <div class="error-gujarati-title">તમારું સુરક્ષિત સત્ર સમાપ્ત થઈ ગયું છે</div>

    <p class="error-description">
        For your security, your session has timed out after a period of inactivity. Please refresh the page or log in again to continue your session.
    </p>

    <div class="btn-group">
        <a href="{{ url('/admin/login') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            Sign In Again
        </a>
        <button onclick="window.location.reload()" class="btn btn-secondary">
            Refresh Page
        </button>
    </div>
@endsection
