$(document).ready(function () {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Initialize Bootstrap Maxlength
    $('[data-maxlength="true"]').each(function () {
        $(this).maxlength({
            warningClass: "badge bg-warning",
            limitReachedClass: "badge bg-danger",
            placement: 'bottom-right-inside',
            validate: true
        });
    });

    // jQuery Validation Configuration
    $.validator.setDefaults({
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
        errorPlacement: function (error, element) {
            // Place error after the element or after its parent if in input group
            if (element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        }
    });

    // Add custom pattern validation method
    $.validator.addMethod('pattern', function (value, element, param) {
        if (this.optional(element)) {
            return true;
        }
        if (typeof param === 'string') {
            param = new RegExp('^(?:' + param + ')$');
        }
        return param.test(value);
    }, 'Invalid format');

    // Initialize form validation
    var validator = $('#contactForm').validate({
        rules: {
            name: {
                required: true,
                minlength: 2,
                maxlength: 100,
                pattern: /^[a-zA-Z\s'-]+$/
            },
            email: {
                required: true,
                email: true,
                maxlength: 255
            },
            subject: {
                required: true,
                minlength: 3,
                maxlength: 200
            },
            message: {
                required: true,
                minlength: 10,
                maxlength: 2000
            }
        },
        messages: {
            name: {
                required: 'Please enter your name',
                minlength: 'Name must be at least 2 characters long',
                maxlength: 'Name cannot exceed 100 characters',
                pattern: 'Please enter a valid name (letters, spaces, hyphens, and apostrophes only)'
            },
            email: {
                required: 'Please enter your email address',
                email: 'Please enter a valid email address',
                maxlength: 'Email cannot exceed 255 characters'
            },
            subject: {
                required: 'Please enter a subject',
                minlength: 'Subject must be at least 3 characters long',
                maxlength: 'Subject cannot exceed 200 characters'
            },
            message: {
                required: 'Please enter your message',
                minlength: 'Message must be at least 10 characters long',
                maxlength: 'Message cannot exceed 2000 characters'
            }
        },
        submitHandler: function (form) {
            // Show loading state
            var $submitBtn = $('#submitBtn');
            var originalHtml = $submitBtn.html();
            $submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...'
            );

            // Remove existing alerts
            $('.alert').remove();

            // Submit via AJAX
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // Show success message
                        var successHtml = '<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">' +
                            '<i data-lucide="check-circle" class="icon-sm me-2"></i>' +
                            response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';

                        $(form).before(successHtml);

                        // Reset form
                        form.reset();
                        validator.resetForm();
                        $('.is-valid').removeClass('is-valid');

                        // Reinitialize icons
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                },
                error: function (xhr) {
                    var errorHtml = '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">' +
                        '<ul class="mb-0">';

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function (key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                    } else {
                        errorHtml += '<li>Something went wrong. Please try again later.</li>';
                    }

                    errorHtml += '</ul>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';

                    $(form).before(errorHtml);
                },
                complete: function () {
                    // Restore button state
                    $submitBtn.prop('disabled', false).html(originalHtml);

                    // Reinitialize icons for the alerts
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            });
        },
        invalidHandler: function (event, validator) {
            // Scroll to first error
            if (validator.numberOfInvalids() > 0) {
                $('html, body').animate({
                    scrollTop: $(validator.errorList[0].element).offset().top - 100
                }, 500);
            }
        }
    });

    // Reset button functionality
    $('#resetBtn').on('click', function () {
        validator.resetForm();
        $('.form-control').removeClass('is-invalid is-valid');
        // Reinitialize maxlength after reset
        $('[data-maxlength="true"]').each(function () {
            $(this).maxlength('destroy');
            $(this).maxlength({
                warningClass: "badge bg-warning",
                limitReachedClass: "badge bg-danger",
                placement: 'bottom-right-inside',
                validate: true
            });
        });
        lucide.createIcons();
    });

    // Real-time validation on blur
    $('#contactForm input, #contactForm textarea').on('blur', function () {
        $(this).valid();
    });
});