document.addEventListener('DOMContentLoaded', function() {
    const resendBtn = document.getElementById('resendBtn');
    if (!resendBtn) return;

    const COOLDOWN_SECONDS = 60;

    function updateButtonState() {
        const cooldownEnd = localStorage.getItem('otp_resend_until');
        if (cooldownEnd) {
            const now = Date.now();
            const remaining = Math.ceil((parseInt(cooldownEnd) - now) / 1000);

            if (remaining > 0) {
                disableButton(remaining);
            } else {
                localStorage.removeItem('otp_resend_until');
                enableButton();
            }
        }
    }

    function disableButton(remainingSeconds) {
        resendBtn.disabled = true;
        
        if (remainingSeconds !== undefined) {
             resendBtn.innerText = `Resend OTP (${remainingSeconds}s)`;
        }
       
        resendBtn.style.opacity = '0.5';
        resendBtn.style.cursor = 'not-allowed';
        resendBtn.style.pointerEvents = 'none'; 
    }

    function enableButton() {
        resendBtn.disabled = false;
        resendBtn.innerText = 'Resend OTP';
        resendBtn.style.opacity = '1';
        resendBtn.style.cursor = 'pointer';
        resendBtn.style.pointerEvents = 'auto';
    }

    updateButtonState();
    setInterval(updateButtonState, 1000);
    resendBtn.addEventListener('click', function(e) {
        
        if (resendBtn.getAttribute('data-clicked') === 'true') {
            e.preventDefault();
            return;
        }
        resendBtn.setAttribute('data-clicked', 'true');
        resendBtn.style.opacity = '0.5';
        resendBtn.style.pointerEvents = 'none';

        const now = Date.now();
        const until = now + (COOLDOWN_SECONDS * 1000);
        localStorage.setItem('otp_resend_until', until);
    });
});
