(function() {
    'use strict';

    // Form initialization time for spam protection
    let formInitTime = null;

    // Email regex pattern
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    // UK telephone regex pattern (various formats)
    const ukTelPattern = /^(\+44\s?7\d{3}|\(?07\d{3}\)?)\s?\d{3}\s?\d{3}$|^(\+44\s?[1-9]\d{1,4}|\(?0[1-9]\d{1,4}\)?)\s?\d{3,4}\s?\d{3,4}$/;

    /**
     * Create and inject the form
     */
    function createForm() {
        const container = document.getElementById('jsForm');

        if (!container) {
            console.error('Container with id "jsForm" not found');
            return;
        }

        // Record form initialization time
        formInitTime = Date.now();

        const formHTML = `
            <form id="contactForm" class="needs-validation" novalidate>
                <!-- Honeypot fields (hidden from users) -->
                <div style="position: absolute; left: -9999px; top: -9999px;">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">

                    <label for="company">Company</label>
                    <input type="text" id="company" name="company" tabindex="-1" autocomplete="off">
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                        <div class="invalid-feedback">Please enter your first name.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="surname" class="form-label">Surname <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="surname" name="surname" required>
                        <div class="invalid-feedback">Please enter your surname.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="telephone" class="form-label">Telephone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" required>
                        <div class="invalid-feedback">Please enter a valid UK telephone number.</div>
                    </div>

                    <div class="col-12">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        <div class="invalid-feedback">Please enter your message.</div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-lg btn-success" id="submitBtn">
                            <span class="submit-text">Send Message</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>

                <div id="formMessage" class="mt-3"></div>
            </form>
        `;

        container.innerHTML = formHTML;

        // Attach event listeners
        const form = document.getElementById('contactForm');
        form.addEventListener('submit', handleSubmit);

        // Add real-time validation
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    }

    /**
     * Validate individual field
     */
    function validateField(field) {
        const fieldId = field.id;
        let isValid = true;

        // Skip honeypot fields
        if (fieldId === 'website' || fieldId === 'company') {
            return true;
        }

        // Check if field is required and empty
        if (field.hasAttribute('required') && !field.value.trim()) {
            isValid = false;
        }

        // Email validation
        if (fieldId === 'email' && field.value.trim()) {
            if (!emailPattern.test(field.value.trim())) {
                isValid = false;
            }
        }

        // UK telephone validation
        if (fieldId === 'telephone' && field.value.trim()) {
            if (!ukTelPattern.test(field.value.trim())) {
                isValid = false;
            }
        }

        // Update field validity
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }

        return isValid;
    }

    /**
     * Validate entire form
     */
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input:not([id="website"]):not([id="company"]), textarea');

        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Check honeypot fields
     */
    function checkHoneypot() {
        const website = document.getElementById('website');
        const company = document.getElementById('company');

        // If either honeypot field is filled, it's likely a bot
        if (website.value || company.value) {
            return false;
        }

        return true;
    }

    /**
     * Check form completion time
     */
    function checkTimestamp() {
        if (!formInitTime) {
            return false;
        }

        const currentTime = Date.now();
        const timeDiff = (currentTime - formInitTime) / 1000; // Convert to seconds

        // If form was completed in less than 3 seconds, likely spam
        if (timeDiff < 3) {
            return false;
        }

        return true;
    }

    /**
     * Show message to user
     */
    function showMessage(message, type = 'danger') {
        const messageDiv = document.getElementById('formMessage');
        messageDiv.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    /**
     * Handle form submission
     */
    function handleSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const submitBtn = document.getElementById('submitBtn');
        const submitText = submitBtn.querySelector('.submit-text');
        const spinner = submitBtn.querySelector('.spinner-border');

        // Validate form
        if (!validateForm(form)) {
            showMessage('Please correct the errors in the form.', 'danger');
            return;
        }

        // Check honeypot
        if (!checkHoneypot()) {
            // Silently fail for bots
            console.log('Honeypot triggered');
            return;
        }

        // Check timestamp
        if (!checkTimestamp()) {
            showMessage('Please take your time filling out the form.', 'warning');
            return;
        }

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitText.classList.add('d-none');
        spinner.classList.remove('d-none');

        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'jspf_submit_form');
        formData.append('nonce', jspfData.nonce);
        formData.append('first_name', document.getElementById('firstName').value.trim());
        formData.append('surname', document.getElementById('surname').value.trim());
        formData.append('email', document.getElementById('email').value.trim());
        formData.append('telephone', document.getElementById('telephone').value.trim());
        formData.append('message', document.getElementById('message').value.trim());

        // Submit via AJAX
        fetch(jspfData.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to success page
                window.location.href = '/success-message-sent/';
            } else {
                showMessage(data.data.message || 'An error occurred. Please try again.', 'danger');

                // Re-enable submit button
                submitBtn.disabled = false;
                submitText.classList.remove('d-none');
                spinner.classList.add('d-none');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred. Please try again.', 'danger');

            // Re-enable submit button
            submitBtn.disabled = false;
            submitText.classList.remove('d-none');
            spinner.classList.add('d-none');
        });
    }

    /**
     * Initialize form after DOM is loaded
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on the contact page
        if (window.location.pathname === '/contact/' || window.location.pathname === '/contact') {
            createForm();
        }
    });

})();
