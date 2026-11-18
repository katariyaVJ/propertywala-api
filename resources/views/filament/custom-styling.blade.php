<style>
    /* ========================================================
       Propertywala Ultra-Modern & Premium Admin Theme (Light)
       ======================================================== */
    :root {
        --primary-50: 245, 243, 255;
        --primary-100: 237, 233, 254;
        --primary-200: 221, 214, 254;
        --primary-300: 196, 181, 253;
        --primary-400: 167, 139, 250;
        --primary-500: 139, 92, 246;
        --primary-600: 124, 58, 237;
        --primary-700: 109, 40, 217;
        --primary-800: 91, 33, 182;
        --primary-900: 76, 29, 149;
        --surface-bg: #f8fafc;
        --card-bg: #ffffff;
        --border-color: #e2e8f0;
        --text-primary: #0f172a;
        --text-secondary: #475569;
    }

    body {
        background-color: var(--surface-bg) !important;
        font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", Roboto, sans-serif !important;
        color: var(--text-primary) !important;
        letter-spacing: -0.01em;
    }

    /* Main App Background */
    .fi-layout, .fi-main, .fi-body {
        background-color: var(--surface-bg) !important;
    }

    /* Sidebar Styling */
    .fi-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.02) !important;
    }

    .fi-sidebar-header {
        border-bottom: 1px solid #f1f5f9 !important;
        padding-top: 1rem !important;
        padding-bottom: 1rem !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        color: #94a3b8 !important;
        margin-top: 0.75rem !important;
    }

    .fi-sidebar-item-button {
        border-radius: 10px !important;
        transition: all 0.15s ease-in-out !important;
        margin: 2px 8px !important;
        padding: 8px 12px !important;
    }

    .fi-sidebar-item-button:hover {
        background-color: #f3e8ff !important;
        color: #6d28d9 !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(109, 40, 217, 0.25) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button svg,
    .fi-sidebar-item-active .fi-sidebar-item-label {
        color: #ffffff !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button .fi-badge {
        background-color: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button .fi-badge span {
        color: #ffffff !important;
    }

    /* Top Navigation Bar */
    .fi-topbar {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        backdrop-filter: blur(8px);
    }

    /* Avatar */
    .fi-user-avatar, .fi-avatar, [class*="fi-avatar"] {
        border-radius: 9999px !important;
    }

    /* Cards, Widgets & Containers */
    .fi-section, .fi-ta-ctn, .fi-wi-stats-overview-stat {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.02) !important;
    }

    .fi-ta-ctn {
        overflow: hidden;
    }

    /* Table Styling */
    .fi-ta-table {
        table-layout: auto !important;
        width: 100% !important;
    }

    .fi-ta-cell, .fi-ta-header-cell {
        padding-top: 0.85rem !important;
        padding-bottom: 0.85rem !important;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .fi-ta-header-cell {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .fi-ta-row {
        transition: background-color 0.15s ease !important;
    }

    .fi-ta-row:hover {
        background-color: #f8f7ff !important;
    }

    /* ========================================================
       Semantic Button System (Primary, Danger, Success, Warning, Gray)
       ======================================================== */
    .fi-btn {
        border-radius: 10px !important;
        font-weight: 600 !important;
        transition: all 0.18s ease !important;
    }

    /* Primary Brand Purple Buttons (+ New Property, Save Changes, Login) */
    a.fi-btn[href*="/create"],
    .fi-btn.fi-color-primary,
    .fi-btn-color-primary,
    button[type="submit"].fi-btn.fi-color-primary {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
        background-color: #6d28d9 !important;
        border: 1px solid #5b21b6 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(109, 40, 217, 0.3) !important;
    }

    a.fi-btn[href*="/create"] .fi-btn-label,
    a.fi-btn[href*="/create"] svg,
    .fi-btn.fi-color-primary .fi-btn-label,
    .fi-btn.fi-color-primary svg,
    .fi-btn-color-primary .fi-btn-label,
    .fi-btn-color-primary svg {
        color: #ffffff !important;
    }

    a.fi-btn[href*="/create"]:hover,
    .fi-btn.fi-color-primary:hover,
    .fi-btn-color-primary:hover {
        background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(109, 40, 217, 0.42) !important;
    }

    /* Danger / Delete / Reject Buttons */
    .fi-btn.fi-color-danger,
    .fi-btn-color-danger {
        background-color: #e11d48 !important;
        border: 1px solid #be123c !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(225, 29, 72, 0.25) !important;
    }

    .fi-btn.fi-color-danger .fi-btn-label,
    .fi-btn.fi-color-danger svg,
    .fi-btn-color-danger .fi-btn-label,
    .fi-btn-color-danger svg {
        color: #ffffff !important;
    }

    .fi-btn.fi-color-danger:hover,
    .fi-btn-color-danger:hover {
        background-color: #be123c !important;
        transform: translateY(-1px);
    }

    /* Success / Approve Buttons */
    .fi-btn.fi-color-success,
    .fi-btn-color-success {
        background-color: #059669 !important;
        border: 1px solid #047857 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25) !important;
    }

    .fi-btn.fi-color-success .fi-btn-label,
    .fi-btn.fi-color-success svg,
    .fi-btn-color-success .fi-btn-label,
    .fi-btn-color-success svg {
        color: #ffffff !important;
    }

    .fi-btn.fi-color-success:hover,
    .fi-btn-color-success:hover {
        background-color: #047857 !important;
        transform: translateY(-1px);
    }

    /* Warning / Set Code Buttons */
    .fi-btn.fi-color-warning,
    .fi-btn-color-warning {
        background-color: #d97706 !important;
        border: 1px solid #b45309 !important;
        color: #ffffff !important;
    }

    .fi-btn.fi-color-warning .fi-btn-label,
    .fi-btn.fi-color-warning svg,
    .fi-btn-color-warning .fi-btn-label,
    .fi-btn-color-warning svg {
        color: #ffffff !important;
    }

    /* Secondary / Cancel / Gray Buttons - Always Crisp Dark Text */
    .fi-btn.fi-color-gray,
    button.fi-btn.fi-color-gray,
    a.fi-btn.fi-color-gray {
        background: #ffffff !important;
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #1e293b !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
    }

    .fi-btn.fi-color-gray .fi-btn-label,
    .fi-btn.fi-color-gray svg {
        color: #1e293b !important;
    }

    .fi-btn.fi-color-gray:hover {
        background-color: #f8fafc !important;
        border-color: #94a3b8 !important;
    }

    /* Input Wrappers (No Double-Border on Prefixed / Password / Search Inputs) */
    .fi-input-wrp {
        border-radius: 10px !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .fi-input-wrp:focus-within {
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15) !important;
    }

    /* Wizard Step Indicator Styling */
    .fi-fo-wizard-header {
        border-bottom: 1px solid #e2e8f0 !important;
        background-color: #fafafa !important;
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
        padding: 0.5rem 1rem !important;
    }

    .fi-fo-wizard-step {
        padding: 0.75rem 1rem !important;
    }

    /* Badges */
    .fi-badge {
        font-weight: 600 !important;
        border-radius: 9999px !important;
        padding: 0.25rem 0.65rem !important;
        font-size: 0.75rem !important;
    }

    /* Responsive Tweaks */
    @media (max-width: 768px) {
        .fi-sidebar {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .fi-section {
            border-radius: 12px !important;
        }
    }
</style>
