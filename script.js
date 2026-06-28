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

            // Extract the name
            let name = card.querySelector('h3').innerText.trim();
            let price = "";

            // Check if there are options (radio buttons) selected
            const checkedRadio = card.querySelector('.add-on-section input[type="radio"]:checked');
            let optionText = "";
            let optionPrice = null;

            if (checkedRadio) {
                optionText = checkedRadio.parentElement.innerText.trim();
            } else if (card.querySelector('.add-on-section input[type="radio"]')) {
                alert("Please select an option before adding to cart.");
                return;
            }

            // Extract price from option text if available
            const priceRegex = /RM\s*(\d+(?:\.\d{1,2})?)/i;
            if (optionText) {
                const match = optionText.match(priceRegex);
                if (match) {
                    optionPrice = parseFloat(match[1]);
                }
            }

            // Get base price from card
            let basePrice = 0;
            if (name === "Mac & Cheese") {
                basePrice = 12.00;
            } else {
                const priceElement = card.querySelector('.price');
                if (priceElement) {
                    const basePriceStr = priceElement.innerText;
                    const match = basePriceStr.match(/RM\s*(\d+(?:\.\d{1,2})?)/i);
                    if (match) {
                        basePrice = parseFloat(match[1]);
                    } else {
                        const rawNum = parseFloat(basePriceStr.replace(/[^\d.]/g, ''));
                        if (!isNaN(rawNum)) basePrice = rawNum;
                    }
                }
            }

            let finalPrice = optionPrice !== null ? optionPrice : basePrice;

            // Handle checkbox add-ons
            const checkedChecks = card.querySelectorAll('.add-on-section input[type="checkbox"]:checked');
            let addonNames = [];
            checkedChecks.forEach(chk => {
                const chkText = chk.parentElement.innerText.trim();
                addonNames.push(chkText.split('(')[0].trim());
                const match = chkText.match(/\+RM\s*(\d+\.\d{2})/i) || chkText.match(/\+RM\s*(\d+\.\d+)/i) || chkText.match(/\+RM\s*(\d+)/i);
                if (match) {
                    finalPrice += parseFloat(match[1]);
                }
            });

            // Format Item Display Name
            let displayName = name;
            if (optionText) {
                const optClean = optionText.split('(')[0].trim();
                displayName += " (" + optClean + ")";
            }
            if (addonNames.length > 0) {
                displayName += " + " + addonNames.join(" + ");
            }

            price = "RM " + finalPrice.toFixed(2);

            // Add the item to our cart array
            cart.push({ name: displayName, price });

            // Save the updated cart to localStorage
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