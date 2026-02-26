/**
 * Common Auth Form Validation
 * 
 * This file contains common validation patterns and utilities for auth forms
 */

// Common validation rules
const AuthValidation = {
    rules: {
        email: {
            required: true,
            email: true
        },
        password: {
            required: true,
            minlength: 8
        },
        password_confirmation: {
            required: true,
            minlength: 8,
            equalTo: "[name='password']"
        },
        name: {
            required: true,
            minlength: 2,
            maxlength: 255
        },
        code: {
            required: true,
            minlength: 4,
            maxlength: 6
        }
    },

    messages: {
        email: {
            required: "Please enter your email address",
            email: "Please enter a valid email address"
        },
        password: {
            required: "Please enter a password",
            minlength: "Password must be at least 8 characters"
        },
        password_confirmation: {
            required: "Please confirm your password",
            minlength: "Password confirmation must be at least 8 characters",
            equalTo: "Passwords do not match"
        },
        name: {
            required: "Please enter your full name",
            minlength: "Name must be at least 2 characters",
            maxlength: "Name cannot exceed 255 characters"
        },
        code: {
            required: "Please enter a verification code",
            minlength: "Code must be at least 4 characters",
            maxlength: "Code cannot exceed 6 characters"
        }
    },

    // Common validation settings
    settings: {
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        errorPlacement: function(error, element) {
            // Check if the element is inside a password-field-wrapper
            const passwordWrapper = element.closest('.password-field-wrapper');
            if (passwordWrapper.length) {
                // Place error after the password-field-wrapper div
                error.insertAfter(passwordWrapper);
            } else {
                // Default placement - after the element
                error.insertAfter(element);
            }
        }
    },

    // Initialize form validation
    init: function(formId, customRules = {}, customMessages = {}, submitText = 'Processing...') {
        const form = $(formId);
        if (!form.length) return;

        // Merge custom rules with default rules
        const rules = { ...this.rules, ...customRules };
        const messages = { ...this.messages, ...customMessages };

        // Get only the rules for fields that exist in the form
        const formRules = {};
        const formMessages = {};
        
        form.find('input, select, textarea').each(function() {
            const fieldName = $(this).attr('name');
            if (fieldName && rules[fieldName]) {
                formRules[fieldName] = rules[fieldName];
                formMessages[fieldName] = messages[fieldName];
            }
        });

        // Initialize validation
        form.validate({
            rules: formRules,
            messages: formMessages,
            ...this.settings,
            submitHandler: function(form) {
                const submitBtn = $('button[type="submit"]', form);
                const originalText = submitBtn.html();
                
                submitBtn.html(`<span class="spinner-border spinner-border-sm me-2" role="status"></span>${submitText}`)
                         .prop('disabled', true);
                
                // Submit the form
                form.submit();
            }
        });
    },

    // Utility function to add custom validation method
    addMethod: function(name, method, message) {
        $.validator.addMethod(name, method, message);
    },

    // Common submit button loading state
    setSubmitLoading: function(button, text = 'Processing...') {
        const btn = $(button);
        const originalText = btn.data('original-text') || btn.html();
        btn.data('original-text', originalText);
        btn.html(`<span class="spinner-border spinner-border-sm me-2" role="status"></span>${text}`)
           .prop('disabled', true);
    },

    // Reset submit button
    resetSubmitButton: function(button) {
        const btn = $(button);
        const originalText = btn.data('original-text');
        if (originalText) {
            btn.html(originalText).prop('disabled', false);
        }
    }
};

// Export for use in other files
window.AuthValidation = AuthValidation;
