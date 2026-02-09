# JS Powered Contact Form - WordPress Plugin

A JavaScript-powered contact form plugin for WordPress with spam protection, validation, and email notifications.

## Features

- **JavaScript-powered form**: Loads only on `/contact/` page after DOMContentLoaded
- **Form fields**: First Name, Surname, Email, Telephone, Message
- **Client-side validation**:
  - Regex validation for email addresses
  - UK telephone number validation
  - Real-time field validation with visual feedback
- **Anti-spam protection**:
  - Honeypot fields (invisible to users)
  - Time-based protection (rejects submissions under 3 seconds)
- **Database storage**: All submissions saved to WordPress database
- **Admin interface**: View and manage submissions from WordPress admin
- **Email notifications**: Sends form contents to configured email using WP Mail SMTP
- **Success redirect**: Redirects to `/success-message-sent/` after successful submission
- **Bootstrap 5 compatible**: Works seamlessly with LiveCanvas/Bootstrap 5

## Installation

1. Upload the `js-powered-form` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the recipient email address (see Configuration below)

## Configuration

### 1. Set Recipient Email Address

Edit `js-powered-form.php` and change line 24:

```php
define('JSPF_RECIPIENT_EMAIL', 'your-email@example.com'); // CHANGE THIS
```

Replace `your-email@example.com` with your actual email address.

### 2. Configure WP Mail SMTP (Recommended)

To ensure reliable email delivery with Gmail:

1. Install the "WP Mail SMTP" plugin (or "Post SMTP" or similar)
2. Configure it to use Gmail SMTP:
   - SMTP Host: `smtp.gmail.com`
   - SMTP Port: `587` (TLS) or `465` (SSL)
   - Encryption: TLS or SSL
   - Username: Your Gmail address
   - Password: App-specific password (required for Gmail)

**Important**: Gmail requires an "App Password" rather than your regular password:
- Go to your Google Account → Security → 2-Step Verification → App passwords
- Generate a new app password for "Mail"
- Use this password in WP Mail SMTP settings

### 3. Create Required Pages

Create these pages in WordPress:

1. **Contact Page**:
   - URL: `/contact/`
   - Add a div with id `jsForm` where you want the form to appear:
   ```html
   <div id="jsForm"></div>
   ```

2. **Success Page**:
   - URL: `/success-message-sent/`
   - Add your success message content

## Usage

### Adding the Form to Your Contact Page

1. Create or edit the `/contact/` page in WordPress
2. In the page content (or with your page builder), add:
   ```html
   <div id="jsForm"></div>
   ```
3. The form will automatically be injected into this div when the page loads

### Form Fields

The form includes the following fields:
- **First Name** (required)
- **Surname** (required)
- **Email** (required, validated)
- **Telephone** (required, UK format validated)
- **Message** (required)

### Validation Rules

- **Email**: Standard email format (example@domain.com)
- **UK Telephone**: Accepts various UK formats:
  - Mobile: `07123 456 789`, `07123456789`, `+447123456789`
  - Landline: `01234 567890`, `020 1234 5678`, etc.

### Viewing Submissions

1. Go to WordPress Admin
2. Click "Form Submissions" in the sidebar menu
3. View all submissions in a table format
4. Click "View" to see full submission details
5. Delete individual submissions or use bulk actions

## Anti-Spam Features

### Honeypot Fields
- Two hidden fields (`website` and `company`) are invisible to real users
- If a bot fills these fields, the submission is silently rejected

### Time-Based Protection
- Tracks when form is loaded
- Rejects submissions completed in less than 3 seconds
- Helps prevent automated bot submissions

### Server-Side Validation
- All data is validated and sanitized on the server
- WordPress nonce verification for security
- IP address and user agent logging for tracking

## File Structure

```
js-powered-form/
├── js-powered-form.php           # Main plugin file
├── uninstall.php                 # Cleanup script
├── README.md                     # This file
├── assets/
│   ├── css/
│   │   └── contact-form.css      # Form styles
│   └── js/
│       └── contact-form.js       # Form logic & validation
└── includes/
    ├── class-database.php        # Database operations
    ├── class-form-handler.php    # AJAX & email handling
    ├── class-admin.php           # Admin interface
    └── class-assets.php          # Asset enqueuing
```

## Customization

### Styling
- Edit `assets/css/contact-form.css` to customize form appearance
- The form uses Bootstrap 5 classes by default
- Compatible with LiveCanvas theme

### Validation
- Edit `assets/js/contact-form.js` to modify validation rules
- Regex patterns defined at the top of the file:
  - `emailPattern`: Email validation
  - `ukTelPattern`: UK telephone validation

### Email Template
- Edit `includes/class-form-handler.php`
- Look for the `send_email_notification()` method
- Customize subject and message format

### Spam Protection Timing
- Edit `assets/js/contact-form.js`
- Find the `checkTimestamp()` function
- Change the minimum time threshold (currently 3 seconds)

## Troubleshooting

### Form Not Appearing
- Verify you're on the `/contact/` page
- Check that `<div id="jsForm"></div>` exists on the page
- Open browser console to check for JavaScript errors

### Emails Not Sending
- Verify `JSPF_RECIPIENT_EMAIL` is set correctly
- Install and configure WP Mail SMTP plugin
- Check your email spam folder
- Enable WordPress debug logging to see errors

### Validation Issues
- Check browser console for JavaScript errors
- Verify telephone number matches UK format
- Test with different email formats

### Submissions Not Saving
- Verify database table was created on activation
- Check WordPress error logs
- Ensure proper file permissions

## Database Table

The plugin creates a table `wp_jspf_submissions` with the following structure:

- `id`: Unique submission ID
- `first_name`: Submitter's first name
- `surname`: Submitter's surname
- `email`: Email address
- `telephone`: Phone number
- `message`: Message content
- `ip_address`: Submitter's IP
- `user_agent`: Browser information
- `submission_time`: When form was submitted
- `created_at`: Database record creation time

## Uninstall

When you delete the plugin through WordPress:
1. The database table `wp_jspf_submissions` is automatically deleted
2. All submission data is permanently removed
3. No plugin files remain

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review WordPress error logs
3. Check browser console for JavaScript errors

## Version

Current version: 1.0.0

## License

GPL v2 or later
