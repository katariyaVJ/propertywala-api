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
        font-size: 0.75rem !important;
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
    .fi-sidebar-item-active .fi-sidebar-item-button span {
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
    .fi-section, .fi-ta-ctn, .fi-widget {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.02) !important;
        overflow: hidden;
    }

    /* Table Fix - Eliminate unnecessary horizontal overflow on standard screens */
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

    /* Buttons - Universal Filament v3 Button Styling */
    .fi-header .fi-ac a.fi-btn,
    .fi-header .fi-ac button.fi-btn,
    .fi-header a.fi-btn,
    .fi-header button.fi-btn,
    a.fi-btn[href*="/create"],
    button[type="submit"].fi-btn,
    .fi-btn-primary,
    .fi-btn.fi-color-primary,
    .fi-btn.fi-color-custom,
    a.fi-btn.fi-color-primary,
    button.fi-btn.fi-color-primary,
    .fi-ac-action.fi-color-primary,
    .fi-header-actions .fi-btn {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
        background-color: #6d28d9 !important;
        border: 1px solid #5b21b6 !important;
        border-radius: 10px !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 12px rgba(109, 40, 217, 0.35) !important;
        transition: all 0.2s ease !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: inline-flex !important;
        align-items: center !important;
        text-decoration: none !important;
    }

    .fi-header .fi-ac a.fi-btn *,
    .fi-header .fi-ac button.fi-btn *,
    .fi-header a.fi-btn *,
    .fi-header button.fi-btn *,
    a.fi-btn[href*="/create"] *,
    button[type="submit"].fi-btn *,
    .fi-btn-primary span,
    .fi-btn.fi-color-primary span,
    .fi-btn.fi-color-custom span,
    a.fi-btn.fi-color-primary span,
    button.fi-btn.fi-color-primary span,
    .fi-header-actions .fi-btn span,
    .fi-btn-primary svg,
    .fi-btn.fi-color-primary svg,
    .fi-btn.fi-color-custom svg,
    a.fi-btn.fi-color-primary svg,
    button.fi-btn.fi-color-primary svg,
    .fi-header-actions .fi-btn svg,
    .fi-btn-label {
        color: #ffffff !important;
        fill: #ffffff !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .fi-header .fi-ac a.fi-btn:hover,
    .fi-header .fi-ac button.fi-btn:hover,
    .fi-header a.fi-btn:hover,
    .fi-header button.fi-btn:hover,
    a.fi-btn[href*="/create"]:hover,
    button[type="submit"].fi-btn:hover,
    .fi-btn-primary:hover,
    .fi-btn.fi-color-primary:hover,
    .fi-btn.fi-color-custom:hover,
    a.fi-btn.fi-color-primary:hover,
    button.fi-btn.fi-color-primary:hover,
    .fi-header-actions .fi-btn:hover {
        background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%) !important;
        background-color: #5b21b6 !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(109, 40, 217, 0.45) !important;
    }

    /* Inputs & Form Fields */
    .fi-input, .fi-select-input {
        background-color: #fcfcfd !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 10px !important;
        font-size: 0.925rem !important;
        padding: 0.6rem 0.85rem !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }

    .fi-input:focus, .fi-select-input:focus {
        border-color: #7c3aed !important;
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

    /* Custom Responsive tweaks */
    @media (max-width: 768px) {
        .fi-sidebar {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .fi-section {
            border-radius: 12px !important;
        }
    }
</style>
