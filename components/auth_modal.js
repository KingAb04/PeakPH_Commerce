/**
 * Auth Modal Component JavaScript
 * Handles login/signup modal functionality with OTP verification
 * This file should be included after the DOM is loaded
 */

// Global variables for OTP
let otpTimer = null;
let otpTimeLeft = 300; // 5 minutes
let currentUserEmail = '';

// Auth Modal functionality
function initAuthModal() {
  const loginIcon = document.getElementById("loginIcon");
  const authModal = document.getElementById("authModal");
  const closeModalBtn = document.getElementById("closeModal");
  const showSignupLink = document.getElementById("showSignup");
  const showLoginLink = document.getElementById("showLogin");
  const backToSignupLink = document.getElementById("backToSignup");
  const loginForm = document.getElementById("loginForm");
  const signupForm = document.getElementById("signupForm");
  const otpForm = document.getElementById("otpVerificationForm");

  // Check if required elements exist
  if (!loginIcon || !authModal || !closeModalBtn) {
    console.warn('Auth modal elements not found. Modal functionality disabled.');
    return;
  }

  // Open modal - always show login form first
  loginIcon.addEventListener("click", () => {
    authModal.classList.add("active");
    showLoginForm();
  });

  // Close modal
  closeModalBtn.addEventListener("click", () => {
    authModal.classList.remove("active");
    resetForms();
  });

  // Switch to signup form
  if (showSignupLink) {
    showSignupLink.addEventListener("click", (e) => {
      e.preventDefault();
      showSignupForm();
    });
  }

  // Switch to login form
  if (showLoginLink) {
    showLoginLink.addEventListener("click", (e) => {
      e.preventDefault();
      showLoginForm();
    });
  }

  // Back to signup from OTP
  if (backToSignupLink) {
    backToSignupLink.addEventListener("click", (e) => {
      e.preventDefault();
      showSignupForm();
      clearOTPTimer();
    });
  }

  // Close when clicking outside modal
  authModal.addEventListener("click", (e) => {
    if (e.target === authModal) {
      authModal.classList.remove("active");
      resetForms();
    }
  });

  // Initialize form handlers
  initSignupHandler();
  initOTPHandler();
  initPasswordToggles();
}

// Show login form
function showLoginForm() {
  const loginForm = document.getElementById("loginForm");
  const signupForm = document.getElementById("signupForm");
  const otpForm = document.getElementById("otpVerificationForm");
  
  if (loginForm && signupForm && otpForm) {
    loginForm.style.display = "block";
    signupForm.style.display = "none";
    otpForm.style.display = "none";
  }
  clearOTPTimer();
}

// Show signup form
function showSignupForm() {
  const loginForm = document.getElementById("loginForm");
  const signupForm = document.getElementById("signupForm");
  const otpForm = document.getElementById("otpVerificationForm");
  
  if (loginForm && signupForm && otpForm) {
    loginForm.style.display = "none";
    signupForm.style.display = "block";
    otpForm.style.display = "none";
  }
  clearOTPTimer();
}

// Show OTP verification form
function showOTPForm(email) {
  const loginForm = document.getElementById("loginForm");
  const signupForm = document.getElementById("signupForm");
  const otpForm = document.getElementById("otpVerificationForm");
  const otpEmailSpan = document.getElementById("otpEmail");
  
  if (loginForm && signupForm && otpForm) {
    loginForm.style.display = "none";
    signupForm.style.display = "none";
    otpForm.style.display = "block";
  }
  
  if (otpEmailSpan) {
    otpEmailSpan.textContent = email;
  }
  
  currentUserEmail = email;
  startOTPTimer();
}

// Initialize signup form handler
function initSignupHandler() {
  const signupForm = document.getElementById("emailSignupForm");
  if (!signupForm) return;
  
  signupForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const submitBtn = document.getElementById("signupSubmitBtn");
    const formData = new FormData(signupForm);
    
    // Add CSRF token
    const csrfToken = await getCSRFToken();
    formData.append('csrf_token', csrfToken);
    
    // Show loading state
    setButtonLoading(submitBtn, true);
    clearMessages();
    
    try {
      const response = await fetch('signup_handler.php', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-Token': csrfToken
        }
      });
      
      const result = await response.json();
      
      if (result.success) {
        // Show success message briefly
        showMessage('success', result.message);
        
        // Move to OTP verification
        setTimeout(() => {
          showOTPForm(result.email);
        }, 1000);
        
      } else {
        showMessage('error', result.message);
      }
      
    } catch (error) {
      console.error('Signup error:', error);
      showMessage('error', 'Network error. Please check your connection and try again.');
    } finally {
      setButtonLoading(submitBtn, false);
    }
  });
}

// Initialize OTP verification handler
function initOTPHandler() {
  const otpForm = document.getElementById("otpVerificationFormSubmit");
  const resendBtn = document.getElementById("resendOtpBtn");
  const otpInput = document.getElementById("otp_code");
  
  if (!otpForm) return;
  
  // OTP form submission
  otpForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const verifyBtn = document.getElementById("verifyOtpBtn");
    const otpCode = otpInput.value.trim();
    
    if (!otpCode || otpCode.length !== 6) {
      showOTPMessage('error', 'Please enter a valid 6-digit code');
      return;
    }
    
    const formData = new FormData();
    formData.append('email', currentUserEmail);
    formData.append('otp_code', otpCode);
    
    // Add CSRF token
    const csrfToken = await getCSRFToken();
    formData.append('csrf_token', csrfToken);
    
    // Show loading state
    setButtonLoading(verifyBtn, true);
    clearOTPMessages();
    
    try {
      const response = await fetch('verify_otp.php', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-Token': csrfToken
        }
      });
      
      const result = await response.json();
      
      if (result.success) {
        showOTPMessage('success', result.message);
        
        // Close modal and redirect after success
        setTimeout(() => {
          document.getElementById("authModal").classList.remove("active");
          resetForms();
          
          // Redirect to dashboard or reload page
          if (result.redirect) {
            window.location.href = result.redirect;
          } else {
            window.location.reload();
          }
        }, 2000);
        
      } else {
        showOTPMessage('error', result.message);
        
        // Clear the OTP input for retry
        otpInput.value = '';
        otpInput.focus();
      }
      
    } catch (error) {
      console.error('OTP verification error:', error);
      showOTPMessage('error', 'Network error. Please check your connection and try again.');
    } finally {
      setButtonLoading(verifyBtn, false);
    }
  });
  
  // Resend OTP handler
  if (resendBtn) {
    resendBtn.addEventListener("click", async () => {
      if (resendBtn.disabled) return;
      
      const formData = new FormData();
      formData.append('email', currentUserEmail);
      
      // Add CSRF token
      const csrfToken = await getCSRFToken();
      formData.append('csrf_token', csrfToken);
      
      // Show loading state
      setResendLoading(resendBtn, true);
      clearOTPMessages();
      
      try {
        const response = await fetch('resend_otp.php', {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-Token': csrfToken
          }
        });
        
        const result = await response.json();
        
        if (result.success) {
          showOTPMessage('success', result.message);
          
          // Reset timer
          clearOTPTimer();
          otpTimeLeft = result.expires_in || 300;
          startOTPTimer();
          
        } else {
          showOTPMessage('error', result.message);
        }
        
      } catch (error) {
        console.error('Resend OTP error:', error);
        showOTPMessage('error', 'Network error. Please try again.');
      } finally {
        setResendLoading(resendBtn, false);
      }
    });
  }
  
  // Auto-format OTP input
  if (otpInput) {
    otpInput.addEventListener("input", (e) => {
      // Only allow digits
      e.target.value = e.target.value.replace(/\D/g, '');
      
      // Auto-submit when 6 digits are entered
      if (e.target.value.length === 6) {
        setTimeout(() => {
          otpForm.dispatchEvent(new Event('submit'));
        }, 500);
      }
    });
  }
}

// OTP Timer functions
function startOTPTimer() {
  const timerElement = document.getElementById("otpTimer");
  const resendBtn = document.getElementById("resendOtpBtn");
  
  if (!timerElement) return;
  
  clearOTPTimer();
  
  otpTimer = setInterval(() => {
    otpTimeLeft--;
    
    const minutes = Math.floor(otpTimeLeft / 60);
    const seconds = otpTimeLeft % 60;
    timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (otpTimeLeft <= 0) {
      clearOTPTimer();
      timerElement.textContent = "00:00";
      showOTPMessage('error', 'Verification code expired. Please request a new one.');
      
      // Enable resend button
      if (resendBtn) {
        resendBtn.disabled = false;
      }
    }
  }, 1000);
  
  // Disable resend button initially
  if (resendBtn) {
    resendBtn.disabled = true;
    setTimeout(() => {
      if (resendBtn) resendBtn.disabled = false;
    }, 30000); // Enable after 30 seconds
  }
}

function clearOTPTimer() {
  if (otpTimer) {
    clearInterval(otpTimer);
    otpTimer = null;
  }
}

// Password toggle functionality
function initPasswordToggles() {
  const passwordFields = document.querySelectorAll('.password-field');
  
  passwordFields.forEach(field => {
    const input = field.querySelector('input');
    const icon = field.querySelector('i');
    
    if (input && icon) {
      icon.addEventListener('click', () => {
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('bi-eye');
          icon.classList.add('bi-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('bi-eye-slash');
          icon.classList.add('bi-eye');
        }
      });
    }
  });
}

// Utility functions
function setButtonLoading(button, loading) {
  if (!button) return;
  
  const btnText = button.querySelector('.btn-text');
  const btnLoading = button.querySelector('.btn-loading');
  
  if (loading) {
    button.classList.add('loading');
    button.disabled = true;
    if (btnText) btnText.style.display = 'none';
    if (btnLoading) btnLoading.style.display = 'inline';
  } else {
    button.classList.remove('loading');
    button.disabled = false;
    if (btnText) btnText.style.display = 'inline';
    if (btnLoading) btnLoading.style.display = 'none';
  }
}

function setResendLoading(button, loading) {
  if (!button) return;
  
  const resendText = button.querySelector('.resend-text');
  const resendLoading = button.querySelector('.resend-loading');
  
  if (loading) {
    button.disabled = true;
    if (resendText) resendText.style.display = 'none';
    if (resendLoading) resendLoading.style.display = 'inline';
  } else {
    button.disabled = false;
    if (resendText) resendText.style.display = 'inline';
    if (resendLoading) resendLoading.style.display = 'none';
  }
}

function showMessage(type, message) {
  // You can customize this to show messages in your preferred way
  console.log(`${type}: ${message}`);
}

function showOTPMessage(type, message) {
  const errorDiv = document.getElementById("otpErrorMessage");
  const successDiv = document.getElementById("otpSuccessMessage");
  
  if (type === 'error' && errorDiv) {
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    if (successDiv) successDiv.style.display = 'none';
  } else if (type === 'success' && successDiv) {
    successDiv.textContent = message;
    successDiv.style.display = 'block';
    if (errorDiv) errorDiv.style.display = 'none';
  }
}

function clearMessages() {
  // Clear any existing messages
}

function clearOTPMessages() {
  const errorDiv = document.getElementById("otpErrorMessage");
  const successDiv = document.getElementById("otpSuccessMessage");
  
  if (errorDiv) errorDiv.style.display = 'none';
  if (successDiv) successDiv.style.display = 'none';
}

function resetForms() {
  // Reset all forms to initial state
  const signupForm = document.getElementById("emailSignupForm");
  const otpForm = document.getElementById("otpVerificationFormSubmit");
  
  if (signupForm) signupForm.reset();
  if (otpForm) otpForm.reset();
  
  clearMessages();
  clearOTPMessages();
  clearOTPTimer();
  
  currentUserEmail = '';
  otpTimeLeft = 300;
}

async function getCSRFToken() {
  // Get CSRF token from server or session
  try {
    const response = await fetch('get_csrf_token.php');
    const result = await response.json();
    return result.token;
  } catch (error) {
    console.error('Failed to get CSRF token:', error);
    return '';
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initAuthModal);
  window.addEventListener("click", (event) => {
    if (event.target === authModal) {
      authModal.classList.remove("active");
    }
  });

  // Close with Esc key
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && authModal.classList.contains("active")) {
      authModal.classList.remove("active");
    }
  });

  // Function to show login form
  function showLoginForm() {
    if (loginForm && signupForm) {
      loginForm.style.display = "block";
      signupForm.style.display = "none";
    }
  }

  // Function to show signup form
  function showSignupForm() {
    if (loginForm && signupForm) {
      loginForm.style.display = "none";
      signupForm.style.display = "block";
    }
  }
}

// Initialize modal when DOM is loaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAuthModal);
} else {
  // DOM already loaded
  initAuthModal();
}