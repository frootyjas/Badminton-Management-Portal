// Function to show the sign-up form based on formType
function showSignUpForm(formType) {
    console.log('showSignUpForm called with:', formType);
    var forms = ['courtOwnerFormContainer', 'userFormContainer'];

    forms.forEach(function(form) {
        var element = document.getElementById(form);
        if (element) {
            console.log('Element found:', form);
            element.style.display = (form === formType + 'FormContainer') ? 'block' : 'none';
        } else {
            console.log('Element not found:', form);
        }
    });
}

// Function to hide the sign-up forms
function hideSignupForm() {
    document.getElementById('courtOwnerFormContainer').style.display = 'none';
    document.getElementById('userFormContainer').style.display = 'none';
}

// Function to clear form inputs
function clearFormInputs() {
    document.querySelectorAll('form').forEach(function(form) {
        form.reset();
    });
}

// Form validation on submit
document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        var isFormValid = validateFormFields(form);

        if (!form.checkValidity() || !isFormValid) {
            event.preventDefault();  // Prevent form submission
            form.classList.add('was-validated');  // Bootstrap-style feedback (optional)
        }
    }, false);
});

// Validate that no required fields are blank, including dropdowns
function validateFormFields(form) {
    let isValid = true;

    // Validate text inputs and other input types
    form.querySelectorAll('[required]').forEach(function(input) {
        if (input.value.trim() === '') {
            isValid = false;
            input.classList.add('is-invalid');
            input.nextElementSibling.textContent = "This field is required.";
        } else {
            input.classList.remove('is-invalid');
            input.nextElementSibling.textContent = "";
        }
    });

    // Validate required dropdown fields
    form.querySelectorAll('select[required]').forEach(function(select) {
        if (select.value === '') {
            isValid = false;
            select.classList.add('is-invalid');
            select.nextElementSibling.textContent = "Please fill out this field.";
        } else {
            select.classList.remove('is-invalid');
            select.nextElementSibling.textContent = "";
        }
    });

    return isValid;
}

// Validation for name inputs
function validateName(input) {
    var regex = /^[a-zA-Z\s]*$/; // Allow letters and spaces
    var errorMessage = document.getElementById(input.id + "-error");
    if (!regex.test(input.value)) {
        errorMessage.textContent = "Please type in letters only.";
        input.value = input.value.replace(/[^a-zA-Z\s]/g, ''); // Remove invalid characters
    } else {
        errorMessage.textContent = "";
    }
}

// Validate age based on date of birth
function validateAge(inputElement) {
    var dob = new Date(inputElement.value);
    var today = new Date();
    var age = today.getFullYear() - dob.getFullYear();
    var monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    var errorMessage = inputElement.parentElement.querySelector('.error-message');
    if (age < 18 || dob > today) {
        errorMessage.textContent = "Please enter a valid birthday (age 18 or above and not exceeding today's date).";
        inputElement.value = "";
    } else {
        errorMessage.textContent = "";
    }
}

// Validate contact number format
function validateContactNumber(inputElement) {
    var inputValue = inputElement.value;
    var numericValue = inputValue.replace(/\D/g, '');
    var isStartedWith09 = inputValue.startsWith("09");

    var errorMessage = inputElement.parentElement.querySelector('.error-message');
    if (!isStartedWith09 || inputValue.match(/[a-zA-Z]/)) {
        errorMessage.textContent = "Please enter a valid contact number starting with '09'.";
        inputElement.value = numericValue;
        return;
    }

    if (numericValue.length > 11) {
        numericValue = numericValue.slice(0, 11);
    }

    inputElement.value = numericValue;
    errorMessage.textContent = "";
}

// Initialize password checker functionality
function checkPassword(userType) {
    var passwordField = document.getElementById('password1-' + userType);
    var passwordChecker = document.getElementById('password-checklist-' + userType);
    var password2Field = document.getElementById('password2-' + userType);
    var passwordError = document.getElementById('password-error-' + userType);

    // Initially hide the password checklist
    passwordChecker.style.display = 'none';

    // Show password checklist on focus
    passwordField.addEventListener('focus', function() {
        passwordChecker.style.display = 'block';
    });

    // Hide password checklist on blur
    passwordField.addEventListener('blur', function() {
        passwordChecker.style.display = 'none';
    });

    // Validate password on keyup
    passwordField.addEventListener('keyup', function() {
        var password = passwordField.value;
        var lengthCheck = password.length >= 8;
        var uppercaseCheck = /[A-Z]/.test(password);
        var lowercaseCheck = /[a-z]/.test(password);
        var numberCheck = /\d/.test(password);
        var specialCheck = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(password);

        // Update checklist
        updateCheckmark(userType, 'length', lengthCheck);
        updateCheckmark(userType, 'uppercase', uppercaseCheck);
        updateCheckmark(userType, 'lowercase', lowercaseCheck);
        updateCheckmark(userType, 'number', numberCheck);
        updateCheckmark(userType, 'special', specialCheck);

        // Check if passwords match and validate
        if (password2Field.value === password && lengthCheck && uppercaseCheck && lowercaseCheck && numberCheck && specialCheck) {
            passwordError.style.display = 'none';
        } else {
            passwordError.style.display = 'block'; // Show error if passwords don't match or criteria not met
        }
    });

    // Validate passwords on the second field's keyup event
    password2Field.addEventListener('keyup', function() {
        if (password2Field.value === passwordField.value && passwordField.value.length >= 8) {
            passwordError.style.display = 'none';
        } else {
            passwordError.style.display = 'block';
        }
    });
}

// Initialize the password checker for both user and owner types
document.addEventListener('DOMContentLoaded', function() {
    checkPassword('user');  // Initialize for the user type
    checkPassword('owner'); // Initialize for the owner type
});

// Validate passwords on form submission or any other trigger
function validatePasswords(userType) {
    var password1 = document.getElementById('password1-' + userType).value;
    var password2 = document.getElementById('password2-' + userType).value;
    var passwordError = document.getElementById('password-error-' + userType);

    if (password1 !== password2) {
        passwordError.textContent = 'Passwords do not match';
        passwordError.style.display = 'inline';
        return false;
    }

    var lengthCheck = password1.length >= 8;
    var uppercaseCheck = /[A-Z]/.test(password1);
    var lowercaseCheck = /[a-z]/.test(password1);
    var numberCheck = /\d/.test(password1);
    var specialCheck = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(password1);

    if (!(lengthCheck && uppercaseCheck && lowercaseCheck && numberCheck && specialCheck)) {
        passwordError.textContent = 'Password does not meet the standard criteria';
        passwordError.style.display = 'inline';
        return false;
    }

    passwordError.style.display = 'none'; // Hide error if validation is successful
    return true;
}

// Update checklist with the status of the criteria
function updateCheckmark(userType, criterion, isValid) {
    var checklist = document.getElementById('password-checklist-' + userType);
    var criterionElement = checklist.querySelector(`#${criterion}-${userType}`);
    
    if (isValid) {
        criterionElement.querySelector('i').classList.remove('fa-times');
        criterionElement.querySelector('i').classList.add('fa-check');
    } else {
        criterionElement.querySelector('i').classList.remove('fa-check');
        criterionElement.querySelector('i').classList.add('fa-times');
    }
}
