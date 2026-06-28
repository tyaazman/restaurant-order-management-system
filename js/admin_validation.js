/**
 * Restaurant Order System — Admin Validation & Auth Guard
 * --------------------------------------------------------
 * Provides:
 *   requireAuth()   — session guard for all protected staff pages
 *   logoutStaff()   — clears session and redirects to login.php immediately
 *   Form validation — real-time, inline (no alert() calls)
 */

// ─────────────────────────────────────────────────────────────
//  SESSION AUTH HELPERS
// ─────────────────────────────────────────────────────────────

/**
 * Call at the top of every protected staff page's inline <script>.
 * Redirects to login.php if ros_auth is not set in sessionStorage.
 */
function requireAuth() {
    if (sessionStorage.getItem('ros_auth') !== '1') {
        window.location.replace('login.php');
    }
}

/**
 * Immediately clears session and redirects to login.php.
 * No confirm dialog — direct logout.
 */
function logoutStaff() {
    sessionStorage.removeItem('ros_auth');
    window.location.href = 'login.php';
}

// ─────────────────────────────────────────────────────────────
//  FORM VALIDATION HELPERS
// ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {

    // Real-time: clear error highlight when user starts typing in menu fields
    ['food_name', 'price', 'modal_food_name', 'modal_price'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function () {
                el.style.borderColor = '';
                el.style.boxShadow   = '';
            });
        }
    });

    // Order status form validation (legacy .statusForm support)
    var statusForms = document.querySelectorAll('.statusForm');
    statusForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var statusSelect = form.querySelector('.orderStatus');
            if (statusSelect && statusSelect.value === '') {
                e.preventDefault();
                if (typeof showToast === 'function') {
                    showToast('⚠ Please select an order status to update.');
                }
            }
        });
    });

});