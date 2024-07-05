function showSignUpForm(userType) {
    // Hide all signup forms first
    hideAllForms();
    // Show the appropriate signup form based on the userType
    document.getElementById(userType + 'FormContainer').style.display = "block";
}

function hideAllForms() {
    // Hide all signup forms
    document.querySelectorAll('.signup-form').forEach(form => {
        form.style.display = "none";
    });
}

function hideSignupForm() {
    // Hide the currently visible signup form
    document.querySelectorAll('.signup-form').forEach(form => {
        if (form.style.display === "block") {
            form.style.display = "none";
        }
    });
}

function validateName(input) {
    var regex = /^[a-zA-Z]*$/; // Regular expression to match only letters
    var errorMessage = document.getElementById(input.id + "-error");
    if (!regex.test(input.value)) {
        errorMessage.textContent = "Please type in letters only.";
        input.value = input.value.replace(/[^a-zA-Z]/g, ''); // Remove any non-letter characters
    } else {
        errorMessage.textContent = "";
    }
}

function validateAge(inputElement) {
    var dob = new Date(inputElement.value);
    var today = new Date();
    var age = today.getFullYear() - dob.getFullYear();
    var monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    if (age < 18 || dob > today) {
        var errorMessage = inputElement.parentElement.querySelector('.error-message');
        errorMessage.textContent = "Please enter a valid birthday (age 18 or above and not exceeding today's date).";
        inputElement.value = ""; // Reset to default value
        return;
    } else {
        var errorMessage = inputElement.parentElement.querySelector('.error-message');
        errorMessage.textContent = "";
    }
}

function validateContactNumber(inputElement) {
    var inputValue = inputElement.value;

    // Remove non-numeric characters
    var numericValue = inputValue.replace(/\D/g, '');

    // Flag to track if "09" has been entered
    var isStartedWith09 = inputValue.startsWith("09");

    // Check if the input doesn't start with "09" and contains numbers 1-8
    if (!isStartedWith09 && inputValue.match(/[1-8]/)) {
        var errorMessage = inputElement.parentElement.querySelector('.error-message');
        errorMessage.textContent = "Please enter a valid contact number starting with '09'.";
        inputElement.value = ""; // Clear the input
        return;
    }

    // Check if the input doesn't start with "09" or contains letters
    if (!isStartedWith09 || inputValue.match(/[a-zA-Z]/)) {
        var errorMessage = inputElement.parentElement.querySelector('.error-message');
        errorMessage.textContent = "Please enter a valid contact number starting with '09'.";
        inputElement.value = numericValue; // Remove any letters if entered
        return;
    }

    // Limit the contact number to 11 digits after "09"
    if (numericValue.length > 2) { // if "09" is already typed
        numericValue = numericValue.slice(0, 11); // Limit to 11 digits after "09"
    }

    // Update the input value
    inputElement.value = numericValue;

    // Clear any previous error message
    var errorMessage = inputElement.parentElement.querySelector('.error-message');
    errorMessage.textContent = "";
}

function checkPassword(userType) {
    var passwordField = document.getElementById('password1-' + userType);
    var passwordChecker = document.getElementById('password-checklist-' + userType);

    passwordField.addEventListener('click', function() {
        passwordChecker.style.display = 'block';
    });

    passwordField.addEventListener('blur', function() {
        passwordChecker.style.display = 'none';
    });

    var password = passwordField.value;
    var lengthCheck = password.length >= 8;
    var uppercaseCheck = /[A-Z]/.test(password);
    var lowercaseCheck = /[a-z]/.test(password);
    var numberCheck = /\d/.test(password);
    var specialCheck = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(password);

    updateCheckmark(userType, 'length', lengthCheck);
    updateCheckmark(userType, 'uppercase', uppercaseCheck);
    updateCheckmark(userType, 'lowercase', lowercaseCheck);
    updateCheckmark(userType, 'number', numberCheck);
    updateCheckmark(userType, 'special', specialCheck);

    // Check if all password criteria are met
    var isValidPassword = lengthCheck && uppercaseCheck && lowercaseCheck && numberCheck && specialCheck;

    // If both passwords match and are valid, hide the error message
    var password2Field = document.getElementById('password2-' + userType);
    var passwordError = document.getElementById('password-error-' + userType);
    if (password2Field.value === password && isValidPassword) {
        passwordError.style.display = 'none';
    }
}

function validatePasswords(userType) {
    var password1 = document.getElementById('password1-' + userType).value;
    var password2 = document.getElementById('password2-' + userType).value;
    var passwordError = document.getElementById('password-error-' + userType);

    // Check if passwords match
    if (password1 !== password2) {
        passwordError.textContent = 'Passwords do not match';
        passwordError.style.display = 'inline';
        return false;
    }

    // Check if password meets standard criteria
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

    return true;
}

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

document.getElementById("ownerSignUpForm").addEventListener("submit", function(event) {
    // Validate form and allow submission if all validations pass
    if (!validatePasswords("owner")) {
        event.preventDefault();
    }
});

document.getElementById("coachSignUpForm").addEventListener("submit", function(event) {
    // Validate form and allow submission if all validations pass
    if (!validatePasswords("coach")) {
        event.preventDefault();
    }
});

document.getElementById("playerSignUpForm").addEventListener("submit", function(event) {
    // Validate form and allow submission if all validations pass
    if (!validatePasswords("player")) {
        event.preventDefault();
    }
});

/*document.addEventListener('click', function(event) {
    if (event.target && event.target.classList.contains('toggle-password')) {
        var targetId = event.target.getAttribute('data-target');
        var passwordInput = document.getElementById(targetId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            event.target.classList.remove('fa-eye-slash');
            event.target.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            event.target.classList.remove('fa-eye');
            event.target.classList.add('fa-eye-slash');
        }
    }
});

function setupPasswordValidation(passwordId, confirmId, errorId) {
    document.getElementById(confirmId).addEventListener("input", function() {
        var password = document.getElementById(passwordId).value;
        var confirmPassword = document.getElementById(confirmId).value;
        var errorSpan = document.getElementById(errorId);
        
        if (password !== confirmPassword) {
            errorSpan.style.display = "inline";
        } else {
            errorSpan.style.display = "none";
        }
    });
}

setupPasswordValidation("password1-player", "password2-player", "password-error-player");
setupPasswordValidation("password1-coach", "password2-coach", "password-error-coach");
setupPasswordValidation("password1-owner", "password2-owner", "password-error-owner");

// Function to handle form submission and redirection
function validateAndRedirect(formId, destination, checkFileCount) {
    var form = document.getElementById(formId);
    var inputs = form.querySelectorAll('input, select');

    // Check if all fields are filled
    for (var i = 0; i < inputs.length; i++) {
        if (!inputs[i].value) {
            alert("Please fill out all fields.");
            return false; // Return false to prevent form submission
        }
    }

    // Check if passwords match
    var password1 = form.querySelector("#password1-" + formId.split("SignUpForm")[0]).value;
    var password2 = form.querySelector("#password2-" + formId.split("SignUpForm")[0]).value;
    if (password1 !== password2) {
        alert("Passwords do not match.");
        return false; // Return false to prevent form submission
    }

    // If all validations pass, allow the form to submit
    return true;
}

document.getElementById("coachSignUpForm").addEventListener("submit", function(event) {
    // Validate form and allow submission if all validations pass
    if (!validateAndRedirect("coachSignUpForm", "homeCoach.php", true)) {
        event.preventDefault(); 
    }
});
document.getElementById("playerSignUpForm").addEventListener("submit", function(event) {
    // Validate form and allow submission if all validations pass
    if (!validateAndRedirect("playerSignUpForm", "home.php", true)) {
        event.preventDefault(); 
    }
});
document.getElementById("ownerSignUpForm").addEventListener("submit", function(event) {
    // Validate form and allow submission if all validations pass
    if (!validateAndRedirect("ownerSignUpForm", "createAnnouncementAdmin.php", true)) {
        event.preventDefault(); 
    }
});*/

