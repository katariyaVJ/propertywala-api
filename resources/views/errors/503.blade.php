@extends('errors.layout')

@section('title', '503 — Platform Maintenance')

@section('content')
    <div class="illustration-container" style="background:#eff6ff;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
        </svg>
    </div>

    <span class="error-code-badge" style="background:#dbeafe;color:#1d4ed8;">
        Maintenance • System Upgrade
    </span>

    <h1 class="error-title">Scheduled System Maintenance</h1>
    <div class="error-gujarati-title">સિસ્ટમ મેન્ટેનન્સ ચાલી રહ્યું છે</div>

    <p class="error-description">
        Propertywala Enterprise platform is currently undergoing a scheduled system upgrade. Service will be restored within minutes. Thank you for your patience.
    </p>

    <div class="btn-group">
        <button onclick="window.location.reload()" class="btn btn-primary">
            Check Status (Refresh)
        </button>
    </div>
@endsection
