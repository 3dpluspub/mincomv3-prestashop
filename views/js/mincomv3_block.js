document.addEventListener('DOMContentLoaded', function() {
    if (typeof minComV3Blocked !== 'undefined' && minComV3Blocked === true) {
        const checkoutButtons = document.querySelectorAll(
            'button[name="processOrder"], button[name="processCarrier"], .checkout-button, a.standard-checkout, button.btn-primary'
        );
        
        checkoutButtons.forEach(function(button) {
            if (button && button.textContent.toLowerCase().includes('commander')) {
                button.disabled = true;
                button.style.opacity = '0.5';
                button.style.cursor = 'not-allowed';
                button.title = minComV3Message;
            }
        });
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-warning';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = '<i class="icon-warning"></i> ' + minComV3Message;
        
        const cartContainer = document.querySelector('#cart, .cart-container, .cart-summary');
        if (cartContainer) {
            cartContainer.parentNode.insertBefore(alertDiv, cartContainer);
        }
    }
});
