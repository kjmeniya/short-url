/**
 * Common User Form Validation
 * Used for both Create and Edit User forms
 */

$(document).ready(function () {
    // Debug: Check if jQuery and validation plugin are loaded
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('jQuery validate loaded:', typeof $.fn.validate !== 'undefined');

    // Add custom pattern validation method
    $.validator.addMethod("pattern", function (value, element, param) {
        if (this.optional(element)) {
            return true;
        }
        if (typeof param === "string") {
            param = new RegExp("^(?:" + param + ")$");
        }
        return param.test(value);
    }, "Please enter a valid format.");
    // Initialize Flatpickr for date of birth
    if ($('#date_of_birth').length) {
        const defaultDate = $('#date_of_birth').val() || '';
        flatpickr("#date_of_birth", {
            dateFormat: "Y-m-d",
            maxDate: "today",
            altInput: true,
            altFormat: "F j, Y",
            placeholder: "Select date of birth",
            defaultDate: defaultDate
        });
    }

    // Initialize Bootstrap MaxLength
    $('[data-maxlength="true"]').each(function () {
        $(this).maxlength({
            alwaysShow: true,
            threshold: 10,
            warningClass: "badge mt-1 bg-success",
            limitReachedClass: "badge mt-1 bg-danger",
            separator: ' of ',
            preText: 'You have ',
            postText: ' chars remaining.',
            validate: true
        });
    });

    // Determine if this is edit mode
    const isEditMode = $('#userEditForm').length > 0;
    const formSelector = isEditMode ? '#userEditForm' : '#userCreateForm';

    // Debug: Check if form is found
    console.log('Form selector:', formSelector);
    console.log('Form found:', $(formSelector).length);

    // Ensure form exists before applying validation
    if ($(formSelector).length === 0) {
        console.error('Form not found with selector:', formSelector);
        return;
    }

    // Test basic validation setup
    console.log('Initializing validation for:', formSelector);

    // Form Validation
    const validator = $(formSelector).validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
                maxlength: 255
            },
            email: {
                required: true,
                email: true,
                maxlength: 255
            },
            password: {
                required: !isEditMode, // Required only for create
                passwordRequirements: true
            },
            password_confirmation: {
                required: !isEditMode, // Required only for create
                passwordConfirmation: true
            },
            phone: {
                maxlength: 20,
                pattern: "[0-9+\\-\\s\\(\\)]+"
            },
            address: {
                maxlength: 500
            },
            date_of_birth: {
                date: true
            },
            role: {
                required: true
            },
            is_active: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Please enter the user's full name",
                minlength: "Name must be at least 2 characters long",
                maxlength: "Name cannot exceed 255 characters"
            },
            email: {
                required: "Please enter a valid email address",
                email: "Please enter a valid email format",
                maxlength: "Email cannot exceed 255 characters"
            },
            password: {
                required: "Please provide a password"
            },
            password_confirmation: {
                required: isEditMode ? "Please confirm the new password" : "Please confirm the password"
            },
            phone: {
                maxlength: "Phone number cannot exceed 20 characters",
                pattern: "Please enter a valid phone number format"
            },
            address: {
                maxlength: "Address cannot exceed 500 characters"
            },
            date_of_birth: {
                date: "Please enter a valid date"
            },
            role: {
                required: "Please select a user role"
            },
            is_active: {
                required: "Please select account status"
            }
        },
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.closest('.password-field-wrapper').length) {
                error.insertAfter(element.closest('.password-field-wrapper'));
            } else if (element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element, errorClass) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element, errorClass) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        submitHandler: function (form) {
            // This will be called when validation passes
            console.log('Validation passed, submitting form');

            // Show loading state
            const submitBtn = $('#submitBtn');
            const originalText = submitBtn.html();
            const loadingText = isEditMode ?
                '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Updating User...' :
                '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Creating User...';

            submitBtn.html(loadingText).prop('disabled', true);

            // Submit the form
            form.submit();
        }
    });

    // Real-time validation feedback
    $(formSelector + ' input, ' + formSelector + ' textarea').on('blur', function () {
        $(this).valid();
    });

    // Manual form submission on button click
    $('#submitBtn').on('click', function (e) {
        e.preventDefault();
        console.log('Submit button clicked');

        // Trigger form validation
        if ($(formSelector).valid()) {
            console.log('Form validation passed, submitting...');

            // Show loading state
            const submitBtn = $('#submitBtn');
            const loadingText = isEditMode ?
                '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Updating User...' :
                '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Creating User...';

            submitBtn.html(loadingText).prop('disabled', true);

            // Submit the form using native DOM method
            $(formSelector)[0].submit();
        } else {
            console.log('Form validation failed');
        }
    });

    // Phone number formatting
    $('#phone').on('input', function () {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });

    // Password validation dependency for edit mode
    if (isEditMode) {
        $('#password').on('input', function () {
            if ($(this).val().length > 0) {
                $('#password_confirmation').rules('add', {
                    required: true,
                    messages: {
                        required: "Please confirm the new password"
                    }
                });
            } else {
                $('#password_confirmation').rules('remove', 'required');
            }
        });
    }

    // Test validation on page load
    console.log('Validation setup complete for:', formSelector);
    console.log('Form validation instance:', $(formSelector).data('validator'));
});
