document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Staff Login Validation: username/password required
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const user = document.getElementById('username').value.trim();
            const pass = document.getElementById('password').value.trim();
            if (!user || !pass) {
                alert("Please enter both username and password.");
                e.preventDefault(); 
            }
        });
    }

    // 2. Manage Menu Validation: food name required & price must be a number
    const menuForm = document.getElementById('menuForm');
    if (menuForm) {
        menuForm.addEventListener('submit', function(e) {
            const foodName = document.getElementById('food_name').value.trim();
            const price = document.getElementById('price').value.trim();

            if (!foodName) {
                alert("Food name is required!");
                e.preventDefault();
                return;
            }
            if (!price || isNaN(price) || Number(price) <= 0) {
                alert("Price must be a valid number!");
                e.preventDefault();
            }
        });
    }

    // 3. Update Order Status Validation: order status must be selected
    const statusForms = document.querySelectorAll('.statusForm');
    statusForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const statusSelect = form.querySelector('.orderStatus').value;
            if (statusSelect === "") {
                alert("Please select an order status to update.");
                e.preventDefault();
            }
        });
    });
});