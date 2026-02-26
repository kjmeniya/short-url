/**
 * Profile Form Validation
 * Used for Admin Profile Edit form
 */

$(document).ready(function () {
    // Add custom validation method for phone numbers
    $.validator.addMethod("phonePattern", function(value, element) {
        if (!value) return true; // Let required handle empty values
        return /^[0-9+\-\s\(\)]+$/.test(value);
    }, "Please enter a valid phone number format (numbers, spaces, +, -, parentheses only)");

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

    // Form Validation
    $("#profileEditForm").validate({
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
                passwordRequirements: true
            },
            password_confirmation: {
                passwordConfirmation: true
            },
            phone: {
                maxlength: 20,
                phonePattern: true
            },
            address: {
                maxlength: 500
            },
            date_of_birth: {
                date: true
            }
        },
        messages: {
            name: {
                required: "Please enter your full name",
                minlength: "Name must be at least 2 characters long",
                maxlength: "Name cannot exceed 255 characters"
            },
            email: {
                required: "Please enter a valid email address",
                email: "Please enter a valid email format",
                maxlength: "Email cannot exceed 255 characters"
            },
            password: {
                // Custom error messages handled by passwordRequirements method
            },
            password_confirmation: {
                // Custom error messages handled by passwordConfirmation method
            },
            phone: {
                maxlength: "Phone number cannot exceed 20 characters",
                phonePattern: "Please enter a valid phone number format"
            },
            address: {
                maxlength: "Address cannot exceed 500 characters"
            },
            date_of_birth: {
                date: "Please enter a valid date"
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
            console.log('Profile validation passed, submitting form');

            // Show loading state
            const submitBtn = $('#submitBtn');
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Updating Profile...').prop('disabled', true);

            // Submit the form
            form.submit();
        }
    });

    // Real-time validation feedback
    $('#profileEditForm input, #profileEditForm textarea').on('blur', function () {
        $(this).valid();
    });

    // Phone number formatting
    $('#phone').on('input', function () {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });

    // Password validation dependency
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

    // Manual form submission on button click
    $('#submitBtn').on('click', function (e) {
        e.preventDefault();
        console.log('Profile submit button clicked');

        // Trigger form validation
        if ($('#profileEditForm').valid()) {
            console.log('Profile validation passed, submitting...');

            // Show loading state
            const submitBtn = $('#submitBtn');
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Updating Profile...').prop('disabled', true);

            // Submit the form using native DOM method
            $('#profileEditForm')[0].submit();
        } else {
            console.log('Profile validation failed');
        }
    });


});
