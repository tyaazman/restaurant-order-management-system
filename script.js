// Wait for the HTML content to fully load before running the script
document.addEventListener('DOMContentLoaded', () => {

    // 1. Initialize cart from localStorage or start with an empty array
    let cart = JSON.parse(localStorage.getItem('myCart')) || [];

    // 2. Find the specific HTML element that holds the cart number
    const cartCountElement = document.getElementById('cart-count');

    // Update the cart icon number immediately on page load
    if (cartCountElement) {
        cartCountElement.innerText = cart.length;
    }

    // 3. Find all the "Add to Cart" buttons on the page
    const addButtons = document.querySelectorAll('.add-btn');

    // 4. Loop through every button and tell it what to do when clicked
    addButtons.forEach(button => {
        button.addEventListener('click', () => {

            // Get the card element that the button belongs to
            const card = button.closest('.menu-card');

            // Extract the name and price from that specific card
            const name = card.querySelector('h3').innerText;
            const price = card.querySelector('.price').innerText;

            // Add the item to our cart array
            cart.push({ name, price });

            // Save the updated cart to localStorage (persists across pages)
            localStorage.setItem('myCart', JSON.stringify(cart));

            // Update the text on the screen to show the new number
            if (cartCountElement) {
                cartCountElement.innerText = cart.length;
            }

            // Visual feedback: Change button to "Added!"
            const originalText = button.innerText;
            const originalColor = button.style.backgroundColor;

            button.innerText = "Added!";
            button.style.backgroundColor = "var(--accent-pine)";

            // Change the button back after 1 second
            setTimeout(() => {
                button.innerText = originalText;
                button.style.backgroundColor = originalColor;
            }, 1000);
        });
    });

    // 5. Make the floating cart icon clickable to go to cart.html
    const cartIcon = document.querySelector('.floating-cart');
    if (cartIcon) {
        cartIcon.addEventListener('click', () => {
            window.location.href = 'cart.html';
        });
    }
});