# PeakPH Commerce - Reusable Components

## Auth Modal Component

The authentication modal has been extracted into reusable components for better maintainability.

### Files Created:
- `components/auth_modal.php` - The HTML structure of the login/signup modal
- `components/auth_modal.js` - The JavaScript functionality for the modal

### How to Use:

#### 1. Include the Modal HTML
Add this line where you want the modal to appear (usually before closing body tag):
```php
<?php include 'components/auth_modal.php'; ?>
```

#### 2. Include the JavaScript
Add this script tag after your other scripts:
```html
<script src="components/auth_modal.js"></script>
```

#### 3. Make sure you have a Login Button
Your page needs a button with `id="loginIcon"` to trigger the modal:
```html
<button id="loginIcon" class="login-btn">
  <i class="bi bi-person"></i>
  <span>Login</span>
</button>
```

### Features:
- ✅ Themed with camping/outdoor aesthetic
- ✅ Switch between Login and Signup forms
- ✅ Close with X button, clicking outside, or ESC key
- ✅ Smart image path handling for different directory levels
- ✅ Fallback content if image is missing
- ✅ Form validation and PHP integration
- ✅ Consistent styling across all pages

### Currently Used On:
- `index.php` - Main landing page
- `ProductCatalog.php` - Product catalog page

### Benefits:
1. **DRY Principle** - No duplicate modal code across pages
2. **Easy Maintenance** - Update modal in one place, affects all pages
3. **Consistent UX** - Same modal behavior everywhere
4. **Better Organization** - Separates concerns into logical components
5. **Scalability** - Easy to add the modal to new pages

### Adding to New Pages:
1. Include the component: `<?php include 'components/auth_modal.php'; ?>`
2. Add the script: `<script src="components/auth_modal.js"></script>`
3. Ensure you have the required CSS classes in your stylesheet
4. Add a trigger button with `id="loginIcon"`

That's it! The modal will work automatically.