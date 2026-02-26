/**
 * Password Utilities
 * Handles password visibility toggle and strength meter functionality
 */

'use strict';

(function () {
    
    // Initialize password utilities when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initPasswordToggles();
        initPasswordStrengthMeters();
        initCommonPasswordValidation();
    });

    /**
     * Initialize password visibility toggles
     */
    function initPasswordToggles() {
        const toggleButtons = document.querySelectorAll('.password-toggle-btn');

        toggleButtons.forEach(button => {
            // Add click event
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                togglePasswordVisibility(this);
            });

            // Add keyboard support
            button.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    e.stopPropagation();
                    togglePasswordVisibility(this);
                }
            });
        });
    }

    /**
     * Toggle password visibility for a specific field
     */
    function togglePasswordVisibility(toggleButton) {
        const targetId = toggleButton.getAttribute('data-target');
        const passwordField = document.getElementById(targetId);
        const icon = toggleButton.querySelector('.password-toggle-icon');

        if (!passwordField || !icon) return;

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
            toggleButton.setAttribute('aria-label', 'Hide password');
        } else {
            passwordField.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
            toggleButton.setAttribute('aria-label', 'Show password');
        }

        // Re-initialize lucide icons for the changed icon
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    /**
     * Initialize password strength meters
     */
    function initPasswordStrengthMeters() {
        const passwordFields = document.querySelectorAll('.password-field[data-strength-target]');

        passwordFields.forEach(field => {
            const strengthTargetId = field.getAttribute('data-strength-target');
            // Only proceed if we have a valid strength target ID
            if (strengthTargetId && strengthTargetId.trim() !== '') {
                const strengthMeter = document.getElementById(strengthTargetId);
                if (strengthMeter) {
                    // Show strength meter on focus
                    field.addEventListener('focus', function() {
                        if (this.value.length > 0) {
                            strengthMeter.style.display = 'block';
                        }
                    });

                    // Update strength on input
                    field.addEventListener('input', function() {
                        if (this.value.length > 0) {
                            strengthMeter.style.display = 'block';
                            // Get requirements from data attribute
                            const requirementsData = this.getAttribute('data-requirements');
                            let requirements = null;
                            if (requirementsData) {
                                try {
                                    requirements = JSON.parse(requirementsData);
                                } catch (e) {
                                    console.warn('Invalid requirements data:', e);
                                }
                            }
                            updatePasswordStrength(this.value, strengthMeter, requirements);
                        } else {
                            strengthMeter.style.display = 'none';
                        }
                    });

                    // Hide strength meter on blur if empty
                    field.addEventListener('blur', function() {
                        if (this.value.length === 0) {
                            strengthMeter.style.display = 'none';
                        }
                    });

                    // Hide strength meter on page load if field is empty
                    if (field.value.length === 0) {
                        strengthMeter.style.display = 'none';
                    }
                }
            }
        });

        // Also hide any strength meters that don't have corresponding password fields
        const allStrengthMeters = document.querySelectorAll('.password-strength-meter');
        allStrengthMeters.forEach(meter => {
            const meterId = meter.id;
            const correspondingField = document.querySelector(`[data-strength-target="${meterId}"]`);
            if (!correspondingField) {
                meter.style.display = 'none';
            }
        });
    }

    /**
     * Update password strength meter
     */
    function updatePasswordStrength(password, strengthMeter, requirements = null) {
        const strength = calculatePasswordStrength(password, requirements);
        const progressBar = strengthMeter.querySelector('.strength-bar');
        const strengthText = strengthMeter.querySelector('.strength-text');

        // Update progress bar
        progressBar.style.width = strength.percentage + '%';
        progressBar.className = 'progress-bar strength-bar ' + strength.colorClass;

        // Update strength text
        strengthText.textContent = strength.label;
        strengthText.className = 'strength-text ' + strength.textClass;

        // Update requirements
        updateRequirements(password, strengthMeter, requirements);
    }

    /**
     * Calculate password strength
     */
    function calculatePasswordStrength(password, requirements = null) {
        let score = 0;

        if (password.length === 0) {
            return {
                score: 0,
                percentage: 0,
                label: 'Enter password',
                colorClass: 'bg-secondary',
                textClass: 'text-muted'
            };
        }

        // Default requirements if not provided
        if (!requirements) {
            requirements = {
                length: { enabled: true, min: 8 },
                uppercase: { enabled: false },
                lowercase: { enabled: false },
                number: { enabled: false },
                special: { enabled: false }
            };
        }

        // Length check
        if (requirements.length.enabled) {
            const minLength = requirements.length.min || 8;
            if (password.length >= minLength) score += 30;
            if (password.length >= minLength + 4) score += 10;
            if (password.length >= minLength + 8) score += 10;
        }

        // Character variety checks (only if enabled)
        if (requirements.lowercase.enabled && /[a-z]/.test(password)) score += 15;
        if (requirements.uppercase.enabled && /[A-Z]/.test(password)) score += 15;
        if (requirements.number.enabled && /[0-9]/.test(password)) score += 15;
        if (requirements.special.enabled && /[^A-Za-z0-9]/.test(password)) score += 15;

        // Bonus points for meeting all enabled requirements
        let allRequirementsMet = true;
        if (requirements.length.enabled && password.length < (requirements.length.min || 8)) allRequirementsMet = false;
        if (requirements.lowercase.enabled && !/[a-z]/.test(password)) allRequirementsMet = false;
        if (requirements.uppercase.enabled && !/[A-Z]/.test(password)) allRequirementsMet = false;
        if (requirements.number.enabled && !/[0-9]/.test(password)) allRequirementsMet = false;
        if (requirements.special.enabled && !/[^A-Za-z0-9]/.test(password)) allRequirementsMet = false;

        if (allRequirementsMet) score += 20;

        // Determine strength level
        let label, colorClass, textClass;

        if (score < 30) {
            label = 'Very Weak';
            colorClass = 'bg-danger';
            textClass = 'text-danger';
        } else if (score < 50) {
            label = 'Weak';
            colorClass = 'bg-warning';
            textClass = 'text-warning';
        } else if (score < 70) {
            label = 'Fair';
            colorClass = 'bg-info';
            textClass = 'text-info';
        } else if (score < 90) {
            label = 'Good';
            colorClass = 'bg-success';
            textClass = 'text-success';
        } else {
            label = 'Excellent';
            colorClass = 'bg-success';
            textClass = 'text-success';
        }

        return {
            score: score,
            percentage: Math.min(score, 100),
            label: label,
            colorClass: colorClass,
            textClass: textClass
        };
    }

    /**
     * Update password requirements checklist
     */
    function updateRequirements(password, strengthMeter, requirements = null) {
        // Default requirements if not provided
        if (!requirements) {
            requirements = {
                length: { enabled: true, min: 8 },
                uppercase: { enabled: false },
                lowercase: { enabled: false },
                number: { enabled: false },
                special: { enabled: false }
            };
        }

        const checks = {
            length: password.length >= (requirements.length.min || 8),
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        Object.keys(checks).forEach(requirement => {
            const element = strengthMeter.querySelector(`[data-requirement="${requirement}"]`);
            if (element) {
                const icon = element.querySelector('.requirement-icon');

                if (checks[requirement]) {
                    element.classList.add('met');
                    icon.setAttribute('data-lucide', 'check-circle');
                } else {
                    element.classList.remove('met');
                    icon.setAttribute('data-lucide', 'circle');
                }
            }
        });

        // Re-initialize lucide icons for requirement icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    /**
     * Generate a strong password
     */
    function generateStrongPassword(length = 12) {
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        let password = '';
        let allChars = lowercase + uppercase + numbers + symbols;
        
        // Ensure at least one character from each category
        password += lowercase[Math.floor(Math.random() * lowercase.length)];
        password += uppercase[Math.floor(Math.random() * uppercase.length)];
        password += numbers[Math.floor(Math.random() * numbers.length)];
        password += symbols[Math.floor(Math.random() * symbols.length)];
        
        // Fill the rest randomly
        for (let i = password.length; i < length; i++) {
            password += allChars[Math.floor(Math.random() * allChars.length)];
        }
        
        // Shuffle the password
        return password.split('').sort(() => Math.random() - 0.5).join('');
    }

    /**
     * Initialize common password validation for all forms
     */
    function initCommonPasswordValidation() {
        // Add custom jQuery validation methods
        if (typeof $ !== 'undefined' && $.validator) {
            addCustomValidationMethods();
        }

        // Initialize password confirmation matching
        initPasswordConfirmationValidation();
    }

    /**
     * Add custom jQuery validation methods
     */
    function addCustomValidationMethods() {
        // Custom password validation method
        $.validator.addMethod("passwordRequirements", function(value, element) {
            if (!value) return true; // Let required handle empty values

            // Get requirements from data attribute
            const requirementsData = element.getAttribute('data-requirements');
            let requirements = {
                length: { enabled: true, min: 8 },
                uppercase: { enabled: false },
                lowercase: { enabled: false },
                number: { enabled: false },
                special: { enabled: false }
            };

            if (requirementsData) {
                try {
                    requirements = JSON.parse(requirementsData);
                } catch (e) {
                    console.warn('Invalid requirements data:', e);
                }
            }

            // Check length requirement
            if (requirements.length.enabled && value.length < (requirements.length.min || 8)) {
                return false;
            }

            // Check uppercase requirement
            if (requirements.uppercase.enabled && !/[A-Z]/.test(value)) {
                return false;
            }

            // Check lowercase requirement
            if (requirements.lowercase.enabled && !/[a-z]/.test(value)) {
                return false;
            }

            // Check number requirement
            if (requirements.number.enabled && !/[0-9]/.test(value)) {
                return false;
            }

            // Check special character requirement
            if (requirements.special.enabled && !/[^A-Za-z0-9]/.test(value)) {
                return false;
            }

            return true;
        }, function(params, element) {
            // Dynamic error message based on requirements
            const requirementsData = element.getAttribute('data-requirements');
            let requirements = {
                length: { enabled: true, min: 8 },
                uppercase: { enabled: false },
                lowercase: { enabled: false },
                number: { enabled: false },
                special: { enabled: false }
            };

            if (requirementsData) {
                try {
                    requirements = JSON.parse(requirementsData);
                } catch (e) {
                    requirements = { length: { enabled: true, min: 8 } };
                }
            }

            const messages = [];
            if (requirements.length.enabled) {
                messages.push(`at least ${requirements.length.min || 8} characters`);
            }
            if (requirements.uppercase.enabled) {
                messages.push('one uppercase letter');
            }
            if (requirements.lowercase.enabled) {
                messages.push('one lowercase letter');
            }
            if (requirements.number.enabled) {
                messages.push('one number');
            }
            if (requirements.special.enabled) {
                messages.push('one special character');
            }

            if (messages.length === 1) {
                return `Password must contain ${messages[0]}.`;
            } else if (messages.length === 2) {
                return `Password must contain ${messages[0]} and ${messages[1]}.`;
            } else {
                const lastMessage = messages.pop();
                return `Password must contain ${messages.join(', ')}, and ${lastMessage}.`;
            }
        });

        // Password confirmation validation method
        $.validator.addMethod("passwordConfirmation", function(value, element) {
            const passwordField = document.querySelector('input[name="password"]');
            return !passwordField || value === passwordField.value;
        }, "Password confirmation does not match.");
    }

    /**
     * Initialize password confirmation validation
     */
    function initPasswordConfirmationValidation() {
        const confirmationFields = document.querySelectorAll('input[name="password_confirmation"]');

        confirmationFields.forEach(field => {
            const passwordField = document.querySelector('input[name="password"]');
            if (passwordField) {
                // Update confirmation validation when password changes
                passwordField.addEventListener('input', function() {
                    if (field.value && typeof $ !== 'undefined' && $(field).valid) {
                        $(field).valid(); // Revalidate confirmation field
                    }
                });

                // Validate confirmation when it changes
                field.addEventListener('input', function() {
                    if (typeof $ !== 'undefined' && $(this).valid) {
                        $(this).valid(); // Validate confirmation field
                    }
                });
            }
        });
    }

    /**
     * Get password requirements for a field
     */
    function getPasswordRequirements(element) {
        const requirementsData = element.getAttribute('data-requirements');
        let requirements = {
            length: { enabled: true, min: 8 },
            uppercase: { enabled: false },
            lowercase: { enabled: false },
            number: { enabled: false },
            special: { enabled: false }
        };

        if (requirementsData) {
            try {
                requirements = JSON.parse(requirementsData);
            } catch (e) {
                console.warn('Invalid requirements data:', e);
            }
        }

        return requirements;
    }

    /**
     * Validate password against requirements
     */
    function validatePassword(password, requirements) {
        const errors = [];

        if (requirements.length.enabled && password.length < (requirements.length.min || 8)) {
            errors.push(`at least ${requirements.length.min || 8} characters`);
        }

        if (requirements.uppercase.enabled && !/[A-Z]/.test(password)) {
            errors.push('one uppercase letter');
        }

        if (requirements.lowercase.enabled && !/[a-z]/.test(password)) {
            errors.push('one lowercase letter');
        }

        if (requirements.number.enabled && !/[0-9]/.test(password)) {
            errors.push('one number');
        }

        if (requirements.special.enabled && !/[^A-Za-z0-9]/.test(password)) {
            errors.push('one special character');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    // Export functions for global use
    window.PasswordUtils = {
        generateStrongPassword: generateStrongPassword,
        calculatePasswordStrength: calculatePasswordStrength,
        getPasswordRequirements: getPasswordRequirements,
        validatePassword: validatePassword
    };

})();
