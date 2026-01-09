// Report Pro - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize app functionality
    initApp();
});

function initApp() {
    // Add any global app initialization here
    console.log('Report Pro App Initialized');
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Export functions for use in views
window.formatDate = formatDate;
window.formatCurrency = formatCurrency;

