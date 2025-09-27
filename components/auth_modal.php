<!-- LOGIN MODAL COMPONENT -->
<div id="authModal" class="login-modal">
  <div class="login-card">
    <!-- LOGIN FORM -->
    <div class="login-left" id="loginForm">
      <h2>🏕️ Welcome Back, Explorer!</h2>

      <?php if (isset($_GET['login']) && $_GET['login'] === 'failed'): ?>
        <p style="color: red;">Invalid email or password</p>
      <?php endif; ?>

      <p class="welcome-text">Ready for your next adventure?</p>

      <form id="emailLoginForm" method="POST" action="login.php">
        <label>📧 Email</label>
        <input type="email" name="email" placeholder="Enter your email address" required />

        <label>🔒 Password</label>
        <div class="password-field">
          <input type="password" name="password" placeholder="Enter your password" required />
          <i class="bi bi-eye"></i>
        </div>

        <a href="#" class="forgot-password">Forgot your Password?</a>
        <button type="submit" class="login-btn-main">Log in</button>

        <div class="or-divider"><span>Or Join the Expedition With</span></div>

        <div class="social-login">
          <button type="button" class="google-btn">
            <i class="bi bi-google"></i> Google
          </button>
          <button type="button" class="facebook-btn">
            <i class="bi bi-facebook"></i> Facebook
          </button>
        </div>
      </form>

      <p class="signup-text">
        🆕 New to the wilderness? <a href="#" id="showSignup">Sign Up</a>
      </p>
    </div>

    <!-- SIGNUP FORM -->
    <div class="login-left" id="signupForm" style="display: none;">
      <h2>🌲 Join the Adventure!</h2>
      <p class="welcome-text">Gear up and become part of our outdoor community</p>

      <form id="emailSignupForm">
        <label>👤 Full Name</label>
        <input type="text" name="full_name" id="signup_full_name" placeholder="Enter your adventurer name" required />

        <label>📧 Email</label>
        <input type="email" name="email" id="signup_email" placeholder="Enter your email address" required />

        <label>🔒 Password</label>
        <div class="password-field">
          <input type="password" name="password" id="signup_password" placeholder="Create a secure password" required />
          <i class="bi bi-eye"></i>
        </div>

        <label>🔒 Confirm Password</label>
        <div class="password-field">
          <input type="password" name="confirm_password" id="signup_confirm_password" placeholder="Confirm your password" required />
          <i class="bi bi-eye"></i>
        </div>

        <button type="submit" class="login-btn-main" id="signupSubmitBtn">
          <span class="btn-text">Sign Up</span>
          <span class="btn-loading" style="display: none;">
            <i class="bi bi-arrow-clockwise"></i> Sending verification...
          </span>
        </button>

        <div class="or-divider"><span>Or Join the Expedition With</span></div>

        <div class="social-login">
          <button type="button" class="google-btn">
            <i class="bi bi-google"></i> Google
          </button>
          <button type="button" class="facebook-btn">
            <i class="bi bi-facebook"></i> Facebook
          </button>
        </div>
      </form>

      <p class="signup-text">
        🔙 Already an explorer? <a href="#" id="showLogin">Welcome Back</a>
      </p>
    </div>

    <!-- OTP VERIFICATION FORM -->
    <div class="login-left" id="otpVerificationForm" style="display: none;">
      <h2>📧 Verify Your Email</h2>
      <p class="welcome-text">We've sent a verification code to <span id="otpEmail" class="email-highlight"></span></p>

      <div class="otp-info">
        <div class="otp-timer">
          <i class="bi bi-clock"></i>
          <span>Code expires in: <strong id="otpTimer">05:00</strong></span>
        </div>
      </div>

      <form id="otpVerificationFormSubmit">
        <label>🔐 Enter Verification Code</label>
        <div class="otp-input-group">
          <input type="text" name="otp_code" id="otp_code" placeholder="Enter 6-digit code" maxlength="6" class="otp-input" required />
          <button type="button" id="resendOtpBtn" class="resend-btn" disabled>
            <span class="resend-text">Resend Code</span>
            <span class="resend-loading" style="display: none;">
              <i class="bi bi-arrow-clockwise"></i>
            </span>
          </button>
        </div>

        <div id="otpErrorMessage" class="error-message" style="display: none;"></div>
        <div id="otpSuccessMessage" class="success-message" style="display: none;"></div>

        <button type="submit" class="login-btn-main" id="verifyOtpBtn">
          <span class="btn-text">Verify & Complete Signup</span>
          <span class="btn-loading" style="display: none;">
            <i class="bi bi-arrow-clockwise"></i> Verifying...
          </span>
        </button>
      </form>

      <p class="signup-text">
        🔙 Wrong email? <a href="#" id="backToSignup">Go back to signup</a>
      </p>
    </div>

    <button class="close-btn" id="closeModal">
      <i class="bi bi-x-lg"></i>
    </button>

    <div class="login-right">
      <div class="overlay">
        <?php 
        // Check if we're in a subdirectory (like admin) and adjust path accordingly
        $imagePath = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../Assets/forest-hiker.jpg' : 'Assets/forest-hiker.jpg';
        if (file_exists($imagePath)) {
          echo '<img src="' . htmlspecialchars($imagePath) . '" alt="Adventure awaits in the wilderness" class="modal-hero-image">';
        } else {
          // Fallback content if image doesn't exist
          echo '<div class="themed-content">';
          echo '<h3>🏔️ Peak Adventures Await</h3>';
          echo '<p>Join thousands of outdoor enthusiasts who trust PeakPH for their camping and hiking gear</p>';
          echo '<div class="adventure-stats">';
          echo '<div class="stat"><strong>10K+</strong><span>Happy Campers</span></div>';
          echo '<div class="stat"><strong>500+</strong><span>Quality Products</span></div>';
          echo '<div class="stat"><strong>24/7</strong><span>Adventure Support</span></div>';
          echo '</div></div>';
        }
        ?>
      </div>
    </div>
  </div>
</div>