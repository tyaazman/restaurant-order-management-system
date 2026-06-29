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

    // 3. Floating cart icon click handler
    const cartIcon = document.querySelector('.floating-cart');
    if (cartIcon) {
        cartIcon.addEventListener('click', () => {
            window.location.href = 'cart.html';
        });
    }

    // 4. Use EVENT DELEGATION for cart additions since cards load dynamically
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('add-btn')) {
            const button = e.target;
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
        }
    });

    // 5. DYNAMIC MENU FETCHING & RENDERING
    const activeLink = document.querySelector('.category-nav a.active');
    const activeText = activeLink ? activeLink.textContent.trim().toLowerCase() : '';

    const categoryMap = {
        'sup zz': 'Signature Sup',
        'mee rebus zz': 'Mee Rebus ZZ',
        'sarapan': 'Sarapan',
        'roti canai': 'Roti Canai',
        'set tengah hari': 'Set Tengah Hari',
        'menu ikan': 'Menu Ikan',
        'ala carte menu': 'Ala Carte Menu',
        'western food': 'Western Food',
        'goreng-goreng': 'Goreng-Goreng',
        'drinks': 'Drinks'
    };

    const category = categoryMap[activeText] || 'Signature Sup';
    console.log("ROS DEBUG: Active Tab Text = '" + activeText + "', Mapped Category = '" + category + "'");

    if (category) {
        const gridLayout = document.querySelector('.grid-layout');
        if (gridLayout) {
            // Show loading skeleton
            gridLayout.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--header-maroon); font-weight: 600; padding: 40px; font-family: sans-serif;">⏳ Loading menu items...</div>';
            
            fetch('process/get_menu_by_category.php?category=' + encodeURIComponent(category))
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        gridLayout.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: var(--header-maroon); padding: 40px; font-family: sans-serif;">⚠ Error loading menu: ${res.error}</div>`;
                        return;
                    }
                    
                    if (res.items.length === 0) {
                        gridLayout.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #888; padding: 40px; font-family: sans-serif;">No items currently available in this category.</div>';
                        return;
                    }
                    
                    gridLayout.innerHTML = ''; // Clear loading
                    
                    res.items.forEach(item => {
                        const card = document.createElement('div');
                        card.className = 'menu-card';
                        
                        // Parse options
                        let optionSectionHtml = '';
                        if (item.options && item.options.length > 0) {
                            // Group options by option_group
                            const groups = {};
                            item.options.forEach(opt => {
                                if (!groups[opt.option_group]) {
                                    groups[opt.option_group] = [];
                                }
                                groups[opt.option_group].push(opt);
                            });
                            
                            optionSectionHtml = '<div class="add-on-section" style="margin-top: 10px; text-align: left; padding: 10px; background: rgba(0,0,0,0.02); border-radius: 6px;">';
                            
                            Object.keys(groups).forEach(groupName => {
                                optionSectionHtml += `<h4 style="margin: 0 0 6px 0; font-size: 0.82rem; color: var(--header-maroon); font-weight: 700; font-family: sans-serif;">${escHtml(groupName)}</h4>`;
                                const isRadio = !groupName.toLowerCase().includes('add') && !groupName.toLowerCase().includes('extra') && !groupName.toLowerCase().includes('topping');
                                
                                groups[groupName].forEach((opt, idx) => {
                                    const optId = `opt_${item.menu_item_id}_${opt.option_id}`;
                                    const nameAttr = `group_${item.menu_item_id}_${groupName.replace(/[^a-zA-Z0-9]/g, '_')}`;
                                    const inputType = isRadio ? 'radio' : 'checkbox';
                                    const checkedAttr = (isRadio && idx === 0) ? 'checked' : '';
                                    const priceVal = parseFloat(opt.additional_price);
                                    
                                    let priceLabel = '';
                                    if (inputType === 'radio') {
                                        priceLabel = `(RM ${priceVal.toFixed(2)})`;
                                    } else {
                                        priceLabel = priceVal > 0 ? `(+RM ${priceVal.toFixed(2)})` : '';
                                    }
                                    
                                    optionSectionHtml += `
                                        <label style="display: block; font-size: 0.78rem; font-weight: 500; color: #555; margin-bottom: 4px; cursor: pointer; user-select: none; font-family: sans-serif;">
                                            <input type="${inputType}" name="${nameAttr}" id="${optId}" value="${opt.option_id}" ${checkedAttr} style="margin-right: 5px;">
                                            ${escHtml(opt.option_name)} ${priceLabel}
                                        </label>
                                    `;
                                });
                            });
                            
                            optionSectionHtml += '</div>';
                        }
                        
                        card.innerHTML = `
                            <div class="card-content">
                                <h3>${escHtml(item.item_name)}</h3>
                                <p class="price">RM ${parseFloat(item.price).toFixed(2)}</p>
                                ${optionSectionHtml}
                                <button class="add-btn">+ Add to Cart</button>
                            </div>
                        `;
                        
                        gridLayout.appendChild(card);
                    });
                })
                .catch(err => {
                    gridLayout.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: var(--header-maroon); padding: 40px; font-family: sans-serif;">⚠ Failed to connect to server.</div>';
                });
        }
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});