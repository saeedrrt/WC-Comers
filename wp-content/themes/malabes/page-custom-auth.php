<?php
/**
 * Template Name: Custom Auth
 * Description: صفحة دخول وتسجيل مخصّصة
 */

if (is_user_logged_in()) {
  wp_redirect(home_url('/'));
  exit;
}

defined('ABSPATH') || exit;
get_header();

?>

<section class="flat-spacing">
  <div class="container">
    <div class="s-log">
      <!-- Login Section -->
      <div class="col-left">
        <h1 class="heading">Login</h1>

        <form class="form-login" method="post" action="<?php echo esc_url(wc_get_page_permalink('my-account-2')); ?>">
          <?php wc_print_notices(); ?>
          <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>

          <div class="list-ver">
            <fieldset>
              <input type="text" name="username" placeholder="Enter your email or phone *" required>
            </fieldset>
            <fieldset class="password-wrapper mb-8">
              <input class="password-field" type="password" name="password" placeholder="Password *" required>
              <span class="toggle-pass icon-show-password"></span>
            </fieldset>
            <div class="check-bottom">
              <div class="checkbox-wrap">
                <input id="remember" type="checkbox" class="tf-check" name="rememberme" value="forever">
                <label for="remember" class="h6">Keep me signed in</label>
              </div>
              <h6>
                <a href="<?php echo esc_url(wc_lostpassword_url()); ?>" class="link">
                  Forgot your password?
                </a>
              </h6>
            </div>
          </div>

          <button id="btnLogin2" type="submit" class="tf-btn animate-btn w-100" name="login">
            Login
          </button>
        </form>
      </div>

      <!-- Registration Section -->
      <div class="col-right">
        <h1 class="heading">New Customer</h1>
        <p class="h6 text-sub">For customers who register …</p>

        <form id="wc-otp-register" class="form-register" method="post"
          action="<?php echo esc_url(home_url('/my-account-2/')); ?>">
          <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
          <input type="hidden" name="register" value="1">

          <fieldset class="mb-3">
            <input type="text" name="first_name" placeholder="First Name *" required>
          </fieldset>

          <fieldset class="mb-3">
            <input type="text" name="last_name" placeholder="Last Name *" required>
          </fieldset>

          <fieldset class="mb-3">
            <div class="phone-input-container">
              <select name="country_code" class="country-code-select" id="country-code-select" required>
                <option value="+20" data-flag="🇪🇬">+20</option>
                <option value="+966" data-flag="🇸🇦">+966</option>
                <option value="+971" data-flag="🇦🇪">+971</option>
                <option value="+965" data-flag="🇰🇼">+965</option>
                <option value="+974" data-flag="🇶🇦">+974</option>
                <option value="+973" data-flag="🇧🇭">+973</option>
                <option value="+968" data-flag="🇴🇲">+968</option>
                <option value="+962" data-flag="🇯🇴">+962</option>
                <option value="+961" data-flag="🇱🇧">+961</option>
                <option value="+963" data-flag="🇸🇾">+963</option>
                <option value="+964" data-flag="🇮🇶">+964</option>
                <option value="+967" data-flag="🇾🇪">+967</option>
                <option value="+1" data-flag="🇺🇸">+1</option>
                <option value="+44" data-flag="🇬🇧">+44</option>
                <option value="+33" data-flag="🇫🇷">+33</option>
                <option value="+49" data-flag="🇩🇪">+49</option>
                <option value="+39" data-flag="🇮🇹">+39</option>
                <option value="+34" data-flag="🇪🇸">+34</option>
                <option value="+86" data-flag="🇨🇳">+86</option>
                <option value="+91" data-flag="🇮🇳">+91</option>
              </select>
              <input type="tel" name="billing_phone" class="phone-number-input" placeholder="Phone Number *"
                pattern="[0-9]{8,15}" title="Please enter a valid phone number (8-15 digits)" required>
            </div>
          </fieldset>

          <fieldset class="mb-3">
            <input type="email" name="email" placeholder="Enter your email address (optional)">
          </fieldset>

          <fieldset class="mb-3">
            <input type="password" name="password" placeholder="Password *" required>
          </fieldset>

          <button type="submit" class="tf-btn animate-btn w-100 fw-bold" id="register-submit">
            Register
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- OTP Modal -->
<div id="otp-modal" class="otp-modal" aria-hidden="true">
  <div class="otp-backdrop"></div>
  <div class="otp-dialog">
    <button type="button" class="otp-close" aria-label="Close">×</button>

    <h2 class="otp-title">Verification Code</h2>
    <p class="otp-sub">
      We have sent you a 4-digit verification code to
      <span id="otp-phone-mask" class="otp-phone"></span>
    </p>

    <div class="otp-inputs" role="group" aria-label="OTP">
      <input class="otp-digit" type="text" inputmode="numeric" maxlength="1">
      <input class="otp-digit" type="text" inputmode="numeric" maxlength="1">
      <input class="otp-digit" type="text" inputmode="numeric" maxlength="1">
      <input class="otp-digit" type="text" inputmode="numeric" maxlength="1">
    </div>

    <button id="otp-verify-btn" type="button" class="tf-btn animate-btn w-100 fw-bold otp-submit">Submit</button>

    <div class="otp-foot">
      <span id="otp-timer">0:20</span>
      <span class="otp-resend-muted">Didn't receive the code? It will be resent on retry</span>
    </div>

    <!-- Development hint: show code for testing only -->
    <div id="otp-dev-hint" class="otp-dev-hint"></div>
  </div>
</div>

<style>
  /* Modal basics */
  .otp-modal {
    position: fixed;
    inset: 0;
    display: none;
    z-index: 10000
  }

  .otp-modal.show {
    display: block
  }

  .otp-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, .35)
  }

  .otp-dialog {
    position: relative;
    margin: 5vh auto;
    background: #fff;
    border-radius: 16px;
    max-width: 520px;
    padding: 28px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, .2)
  }

  .otp-close {
    position: absolute;
    left: 12px;
    top: 8px;
    border: 0;
    background: transparent;
    font-size: 28px;
    line-height: 1;
    cursor: pointer
  }

  .otp-title {
    margin: 0 0 6px;
    font-weight: 800
  }

  .otp-sub {
    margin: 0 0 16px;
    color: #555
  }

  .otp-phone {
    background: #e8f7ee;
    color: #2e7d32;
    border-radius: 999px;
    padding: 2px 8px;
    margin-inline: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px
  }

  .otp-inputs {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin: 18px 0
  }

  .otp-digit {
    width: 62px;
    height: 62px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    text-align: center;
    font-size: 26px;
    outline: 0;
    transition: border .15s
  }

  .otp-digit:focus {
    border-color: #22c55e
  }

  .otp-submit {
    background: #22c55e
  }

  .otp-submit:hover {
    filter: brightness(.95)
  }

  .otp-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    color: #666;
    font-size: .9rem
  }

  .otp-dev-hint {
    margin-top: 10px;
    color: #888;
    font-size: .9rem
  }

  @media (max-width:600px) {
    .otp-digit {
      width: 54px;
      height: 54px
    }
  }

  .phone-input-container {
    position: relative;
    display: flex;
    gap: 0;
  }

  .country-code-select {
    flex: 0 0 auto;
    width: 90px;
    padding: 12px 8px;
    border: 1px solid #ddd;
    border-right: none;
    border-radius: 999px 0 0 999px;
    background: #f9f9f9;
    font-size: 14px;
    outline: none;
    cursor: pointer;
  }

  .country-code-select:focus {
    border-color: #007cba;
    box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
  }

  .phone-number-input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 0 999px 999px 0 !important;
    font-size: 14px;
    outline: none;
  }

  .phone-number-input:focus {
    border-color: #007cba;
    box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
  }
</style>

<script>
  (function () {
    // ====== LOGIN via AJAX ======
    const loginForm = document.querySelector('.form-login');
    if (loginForm) {
      loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(loginForm);
        fd.append('action', 'wc_custom_login');
        fd.append('security', (window.WCAuth && WCAuth.loginNonce) || '');

        fetch((window.WCAuth && WCAuth.ajax) || '', {
          method: 'POST',
          credentials: 'same-origin',
          body: fd
        }).then(r => r.json()).then(res => {
          if (res.success) {
            toastr.success(res.data && res.data.message ? res.data.message : 'Login successful');
            window.location.href = res.data.redirect;
            setTimeout(() => {
              toastr.clear();
            }, 5000);
          } else {
            // alert(res.data && res.data.message ? res.data.message : 'Login failed');
            toastr.error(res.data && res.data.message ? res.data.message : 'Login failed');
          }
        }).catch(() => {
          toastr.error('Network error');
        });
      });
    }

    // ====== REGISTRATION with OTP ======
    const form = document.getElementById('wc-otp-register');
    const btnSubmit = document.getElementById('register-submit');
    const modal = document.getElementById('otp-modal');
    const backdrop = modal.querySelector('.otp-backdrop');
    const btnClose = modal.querySelector('.otp-close');
    const btnVerify = document.getElementById('otp-verify-btn');
    const digits = Array.from(modal.querySelectorAll('.otp-digit'));
    const timerEl = document.getElementById('otp-timer');
    const phoneMask = document.getElementById('otp-phone-mask');
    const devHint = document.getElementById('otp-dev-hint');

    let currentOtp = null;
    let timerId = null;
    let timeoutId = null;
    let remaining = 20;
    let formData = null; // Store form data for actual submission

    function toggleInputs(disabled) {
      form.querySelectorAll('input[name="first_name"],input[name="last_name"],input[name="billing_phone"],input[name="email"],input[name="password"]').forEach(i => i.disabled = disabled);
    }

    function maskPhone(p) {
      const d = (p || '').replace(/\D+/g, '');
      if (d.length < 4) return '***';
      return d.slice(0, -2).replace(/\d/g, '•') + d.slice(-2);
    }

    function openModal() {
      modal.classList.add('show');
      setTimeout(() => digits[0].focus(), 50);
    }

    function closeModal() {
      modal.classList.remove('show');
    }

    function formatTime(s) {
      const m = Math.floor(s / 60);
      const r = s % 60;
      return `${m}:${String(r).padStart(2, '0')}`;
    }

    function startCountdown() {
      remaining = 20;
      timerEl.textContent = formatTime(remaining);
      timerId = setInterval(() => {
        remaining--;
        timerEl.textContent = formatTime(remaining);
        if (remaining <= 0) {
          clearInterval(timerId);
        }
      }, 1000);
      timeoutId = setTimeout(() => {
        closeModal();
        location.reload();
      }, 20000);
    }

    function clearTimers() {
      if (timerId) clearInterval(timerId);
      if (timeoutId) clearTimeout(timeoutId);
    }

    function genOtp() {
      currentOtp = String(Math.floor(1000 + Math.random() * 9000));
      devHint.textContent = 'Code (dev simulation): ' + currentOtp;
    }

    function readEntered() {
      return digits.map(i => i.value.trim()).join('');
    }

    function resetDigits() {
      digits.forEach(i => i.value = '');
      digits[0].focus();
    }

    // Auto navigation between OTP digits
    digits.forEach((inp, idx) => {
      inp.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 1);
        if (e.target.value && idx < digits.length - 1) { digits[idx + 1].focus(); }
      });
      inp.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) { digits[idx - 1].focus(); }
      });
      inp.addEventListener('paste', e => {
        const t = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 4);
        if (!t) return;
        e.preventDefault();
        digits.forEach((d, i) => d.value = t[i] || '');
        const last = Math.min(t.length, 4) - 1;
        digits[Math.max(last, 0)].focus();
      });
    });

    // Show OTP modal on registration form submit
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      // Store form data for later submission
      formData = new FormData(form);

      toggleInputs(true);
      btnSubmit.disabled = true;

      // Prepare display data
      const countryCode = form.querySelector('select[name="country_code"]').value || '';
      const phoneVal = form.querySelector('input[name="billing_phone"]').value || '';
      const fullPhone = countryCode + phoneVal;
      phoneMask.textContent = maskPhone(fullPhone);

      // Open modal and start simulation
      resetDigits();
      genOtp();
      openModal();
      startCountdown();
    });

    // Modal close handlers
    function cancelFlow() {
      clearTimers();
      closeModal();
      location.reload();
    }
    btnClose.addEventListener('click', cancelFlow);
    backdrop.addEventListener('click', cancelFlow);

    // Verify OTP and submit registration
    btnVerify.addEventListener('click', function () {
      const entered = readEntered();
      if (entered.length < 4) { return digits[3].focus(); }
      if (entered !== currentOtp) {
        // Simple shake animation for feedback
        modal.querySelector('.otp-dialog').animate([
          { transform: 'translateX(0)' },
          { transform: 'translateX(-6px)' },
          { transform: 'translateX(6px)' },
          { transform: 'translateX(0)' }
        ], { duration: 220 });
        resetDigits();
        return;
      }

      // OTP verified: submit actual registration
      clearTimers();
      closeModal();

      // Submit form normally to WooCommerce
      const hiddenForm = document.createElement('form');
      hiddenForm.method = 'POST';
      hiddenForm.action = form.action;
      hiddenForm.style.display = 'none';

      // Add all form data
      for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        hiddenForm.appendChild(input);
      }

      document.body.appendChild(hiddenForm);
      hiddenForm.submit();
    });
  })();
</script>

<?php
get_footer();
?>