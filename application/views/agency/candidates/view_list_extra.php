<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>

<style>
/* Fix font size */
.ecms-listing * {
    font-size: 14px !important;
}

/* Instant tooltips - POSITIONED ON TOP */
.general-actions .btn {
    position: relative;
}

.general-actions .btn:hover::after {
    content: attr(title);
    position: absolute;
    top: -35px;
    /* Changed from bottom to top */
    left: 50%;
    transform: translateX(-50%);
    background: #000;
    color: #fff;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 9999;
    opacity: 1;
    transition: none;
}

/* Optional: Add a small arrow pointing down to the button */
.general-actions .btn:hover::before {
    content: '';
    position: absolute;
    top: -8px;
    /* Position above the tooltip */
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-top-color: #000;
    /* Arrow pointing down */
    z-index: 10000;
}
</style>

<script>
// Simple tooltip override
function setupTooltips() {
    document.querySelectorAll('.general-actions .btn').forEach(btn => {
        const href = btn.href || '';
        if (href.includes('candidates_list/view')) {
            btn.title = 'View detailed';
        } else if (href.includes('/edit/')) {
            btn.title = 'Edit';
        } else if (href.includes('/onboarding/')) {
            btn.title = 'onboarding process';
        }
    });
}

// Run immediately and frequently
setupTooltips();
setInterval(setupTooltips, 500);
document.addEventListener('click', setupTooltips);
window.addEventListener('scroll', setupTooltips);
</script>