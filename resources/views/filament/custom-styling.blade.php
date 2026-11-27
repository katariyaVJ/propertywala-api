<style>
    /* =========================================================================
       PROPERTYWALA ENTERPRISE ADMIN DESIGN SYSTEM v2.0
       Designed for High-Ticket Real Estate & Bank Auction Management
       ========================================================================= */

    :root {
        --pw-primary: #6d28d9;
        --pw-primary-hover: #5b21b6;
        --pw-surface: #f8fafc;
        --pw-card: #ffffff;
        --pw-border: #e2e8f0;
        --pw-text-main: #0f172a;
        --pw-text-muted: #64748b;
        --pw-shadow-sm: 0 1px 2px 0 rgba(15, 23, 42, 0.05);
        --pw-shadow-md: 0 4px 14px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
        --pw-shadow-lg: 0 12px 28px -6px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
        --pw-shadow-hover: 0 14px 30px -8px rgba(109, 40, 217, 0.15), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
    }

    html {
        scroll-behavior: smooth;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    /* =========================================================================
       TYPOGRAPHY SYSTEM - Strict Hierarchical Scales & Alignment
       ========================================================================= */
    body {
        background-color: var(--pw-surface) !important;
        font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", Roboto, sans-serif !important;
        color: var(--pw-text-main) !important;
        letter-spacing: -0.012em !important;
        font-size: 0.90rem !important;
        line-height: 1.5 !important;
    }

    /* Primary Headings (H1 Page Titles) */
    .fi-header-heading {
        font-weight: 800 !important;
        letter-spacing: -0.035em !important;
        color: #0f172a !important;
        font-size: 1.5rem !important;
        line-height: 1.25 !important;
    }

    .fi-header-subheading {
        font-size: 0.85rem !important;
        color: #64748b !important;
        margin-top: 0.25rem !important;
    }

    /* Section & Card Headings (H2 / Section Titles) */
    .fi-section-header-heading {
        font-size: 1.05rem !important;
        font-weight: 700 !important;
        letter-spacing: -0.02em !important;
        color: #1e293b !important;
    }

    .fi-section-header-description {
        font-size: 0.80rem !important;
        color: #64748b !important;
    }

    /* Input & Label Sizes */
    .fi-fo-field-wrp-label span {
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        color: #334155 !important;
    }

    .fi-fo-field-wrp-helper-text {
        font-size: 0.75rem !important;
        color: #64748b !important;
        margin-top: 0.25rem !important;
    }

    /* Table Typography & Uniform Spacing */
    .fi-ta-header-cell-label {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        color: #64748b !important;
    }

    .fi-ta-cell {
        font-size: 0.86rem !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }

    /* High-Contrast Semantic Status Badges & Buttons */
    .fi-badge {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        padding: 3px 9px !important;
        border-radius: 6px !important;
        letter-spacing: 0.02em !important;
    }

    /* Standard Button Sizing & Polish */
    .fi-btn {
        border-radius: 8px !important;
        font-weight: 600 !important;
        font-size: 0.84rem !important;
        transition: all 0.15s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }

    .fi-btn:hover {
        transform: translateY(-1px);
    }

    /* Spacing Optimization - Eliminating awkward empty gaps */
    .fi-main {
        padding-top: 1rem !important;
        padding-bottom: 2rem !important;
        max-width: 1440px !important;
        margin: 0 auto !important;
    }

    .fi-page-header {
        margin-bottom: 1rem !important;
    }

    .fi-section {
        margin-bottom: 1.25rem !important;
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: var(--pw-shadow-sm) !important;
    }

    /* Elegant Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Canvas Layout */
    .fi-layout, .fi-main, .fi-body {
        background-color: var(--pw-surface) !important;
    }

    .fi-main {
        padding-top: 1.25rem !important;
        padding-bottom: 2.5rem !important;
    }

    /* Topbar - Glassmorphism */
    .fi-topbar {
        background-color: rgba(255, 255, 255, 0.94) !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.85) !important;
        backdrop-filter: blur(14px) !important;
        -webkit-backdrop-filter: blur(14px) !important;
        box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.03) !important;
    }

    /* Sidebar - Premium Minimalist Navy/Slate */
    .fi-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: 2px 0 12px rgba(15, 23, 42, 0.02) !important;
    }

    .fi-sidebar-header {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 0.85rem 1rem !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 0.5rem !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.68rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: #94a3b8 !important;
        margin-top: 1rem !important;
        padding-left: 0.75rem !important;
    }

    .fi-sidebar-item-button {
        border-radius: 10px !important;
        transition: all 0.16s ease !important;
        margin: 2px 8px !important;
        padding: 8px 12px !important;
        font-weight: 500 !important;
        font-size: 0.88rem !important;
        color: #475569 !important;
    }

    .fi-sidebar-item-button:hover {
        background-color: #f5f3ff !important;
        color: #6d28d9 !important;
        transform: translateX(2px);
    }

    .fi-sidebar-item-active .fi-sidebar-item-button {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
        color: #ffffff !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 12px rgba(109, 40, 217, 0.3) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button svg,
    .fi-sidebar-item-active .fi-sidebar-item-label {
        color: #ffffff !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-button .fi-badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    /* Page Titles & Headers */
    .fi-header-heading {
        font-weight: 800 !important;
        letter-spacing: -0.03em !important;
        color: #0f172a !important;
        font-size: 1.65rem !important;
    }

    .fi-breadcrumbs-item-label {
        font-weight: 500 !important;
        font-size: 0.78rem !important;
        color: #64748b !important;
    }

    /* KPI Stat Cards (Dashboard Overview) */
    .fi-wi-stats-overview-stat {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: var(--pw-shadow-md) !important;
        padding: 1.25rem !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative;
        overflow: hidden;
    }

    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: var(--pw-shadow-hover) !important;
        border-color: #c4b5fd !important;
    }

    .fi-wi-stats-overview-stat-value {
        font-size: 1.85rem !important;
        font-weight: 900 !important;
        letter-spacing: -0.035em !important;
        color: #0f172a !important;
    }

    .fi-wi-stats-overview-stat-label {
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #64748b !important;
    }

    /* Cards & Containers */
    .fi-section, .fi-ta-ctn, .fi-wi-account .fi-section {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: var(--pw-shadow-md) !important;
    }

    /* Data Tables: Crystal Clear & Spaced */
    .fi-ta-content {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .fi-ta-table {
        width: 100% !important;
    }

    .fi-ta-header-cell {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 800 !important;
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.07em !important;
        padding: 0.85rem 1rem !important;
        border-bottom: 1px solid #e2e8f0 !important;
        white-space: nowrap !important;
    }

    .fi-ta-cell {
        padding: 0.9rem 1rem !important;
        vertical-align: middle !important;
        font-size: 0.88rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .fi-ta-row {
        transition: background-color 0.12s ease !important;
    }

    .fi-ta-row:hover {
        background-color: #faf8ff !important;
    }

    /* Badges */
    .fi-badge {
        font-weight: 700 !important;
        border-radius: 9999px !important;
        padding: 0.2rem 0.65rem !important;
        font-size: 0.72rem !important;
        letter-spacing: 0.02em !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
    }

    /* BUTTON POLISH (Fixing Contrast & Cheap Look) */
    .fi-btn {
        border-radius: 10px !important;
        font-weight: 700 !important;
        letter-spacing: -0.01em !important;
        transition: all 0.16s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Primary Buttons (Purple Gradient) */
    a.fi-btn[href*="/create"],
    .fi-btn.fi-color-primary,
    .fi-btn-color-primary,
    button[type="submit"].fi-btn.fi-color-primary {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
        border: 1px solid #5b21b6 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(109, 40, 217, 0.28) !important;
    }

    a.fi-btn[href*="/create"]:hover,
    .fi-btn.fi-color-primary:hover,
    .fi-btn-color-primary:hover {
        background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(109, 40, 217, 0.38) !important;
    }

    /* Success Buttons (Approve / Confirm) */
    .fi-btn.fi-color-success,
    .fi-btn-color-success {
        background: #059669 !important;
        border: 1px solid #047857 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25) !important;
    }

    .fi-btn.fi-color-success:hover,
    .fi-btn-color-success:hover {
        background: #047857 !important;
        transform: translateY(-1px);
    }

    /* Danger Buttons (Reject / Delete) */
    .fi-btn.fi-color-danger,
    .fi-btn-color-danger {
        background: #e11d48 !important;
        border: 1px solid #be123c !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(225, 29, 72, 0.25) !important;
    }

    .fi-btn.fi-color-danger:hover,
    .fi-btn-color-danger:hover {
        background: #be123c !important;
        transform: translateY(-1px);
    }

    /* Secondary / Gray Buttons */
    .fi-btn.fi-color-gray,
    button.fi-btn.fi-color-gray,
    a.fi-btn.fi-color-gray {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #1e293b !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04) !important;
    }

    .fi-btn.fi-color-gray:hover {
        background: #f8fafc !important;
        border-color: #94a3b8 !important;
        color: #0f172a !important;
    }

    /* Form Inputs */
    .fi-input-wrp {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        transition: all 0.15s ease !important;
    }

    .fi-input-wrp:focus-within {
        border-color: #7c3aed !important;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.14) !important;
    }

    /* Form Section Headers */
    .fi-section-header-heading {
        font-weight: 800 !important;
        letter-spacing: -0.02em !important;
        color: #0f172a !important;
    }

    .fi-section-header-description {
        font-size: 0.8rem !important;
        color: #64748b !important;
    }

    /* Wizard Polish */
    .fi-fo-wizard {
        border-radius: 16px !important;
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
        box-shadow: var(--pw-shadow-md) !important;
    }

    .fi-fo-wizard-header {
        border-bottom: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        padding: 0.6rem 0.85rem !important;
    }

    .fi-fo-wizard-step {
        padding: 1.25rem 1.5rem !important;
    }

    .fi-fo-wizard-footer {
        padding: 1rem 1.5rem !important;
        background: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
    }

    /* Action Dropdown & Modals */
    .fi-dropdown-panel {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 16px 36px -6px rgba(15, 23, 42, 0.16) !important;
        padding: 6px !important;
    }

    .fi-dropdown-list-item {
        border-radius: 8px !important;
        font-weight: 600 !important;
        padding: 8px 12px !important;
    }

    .fi-modal-window {
        border-radius: 20px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 24px 60px -12px rgba(15, 23, 42, 0.25) !important;
    }

    /* Responsive Rules */
    @media (max-width: 1023px) {
        .fi-main {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
    }

    @media (max-width: 767px) {
        .fi-main {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
        .fi-header-heading {
            font-size: 1.35rem !important;
        }
        .fi-header .fi-ac {
            width: 100% !important;
        }
        .fi-header .fi-ac > a.fi-btn {
            width: 100% !important;
        }
        .fi-ta-cell, .fi-ta-header-cell {
            padding: 0.7rem 0.6rem !important;
            font-size: 0.8rem !important;
        }
        .fi-wi-stats-overview-stat {
            padding: 1rem !important;
        }
    }
</style>
