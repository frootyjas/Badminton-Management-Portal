// Function to toggle edit/save mode
function toggleEditSave(formId, button) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input, select');
    const toggleIcons = form.querySelectorAll('.toggle-password');
    
    if (button.innerText === 'Edit') {
        inputs.forEach(input => input.disabled = false);
        toggleIcons.forEach(icon => icon.classList.add('enabled'));
        button.innerText = 'Save';
    } else {
        inputs.forEach(input => input.disabled = true);
        toggleIcons.forEach(icon => icon.classList.remove('enabled'));
        button.innerText = 'Edit';
    }
}

document.getElementById('file-input').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-picture').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

/*// Function to toggle password visibility
function togglePasswordVisibility(id) {
    const passwordInput = document.getElementById(id);
    const icon = passwordInput.nextElementSibling;
    
    if (!passwordInput.disabled) { // Check if the input field is enabled (in edit mode)
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        }
    }
}

function openDeleteModal() {
    document.getElementById("deleteModal").style.display = "block";
}

function closeDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
}

function openConfirmDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
    document.getElementById("confirmDeleteModal").style.display = "block";
}

function closeConfirmDeleteModal() {
    document.getElementById("confirmDeleteModal").style.display = "none";
}

function deleteAccount() {
    // Add the logic to delete the account here
    window.location.href = "http://localhost/Badminton-Management-System/logout_action.php"; // Redirect to the index page
}*/

function openDeleteModal() {
    $('#deleteModal').modal('show');
}

function closeDeleteModal() {
    $('#deleteModal').modal('hide');
}

function openConfirmDeleteModal() {
    $('#deleteModal').modal('hide');
    $('#confirmDeleteModal').modal('show');
}

function closeConfirmDeleteModal() {
    $('#confirmDeleteModal').modal('hide');
}

function deleteAccount() {
    var password = $('#confirmDeletePassword').val();
    $.ajax({
        url: 'deleteAccount.php',
        type: 'POST',
        data: { password: password },
        success: function(response) {
            if (response === 'success') {
                window.location.href = 'logout_action.php';
            } else {
                alert('Password incorrect.');
            }
        }
    });
}

$('#changePasswordForm').on('submit', function(e) {
    e.preventDefault();
    var oldPassword = $('#oldPassword').val();
    var newPassword = $('#newPassword').val();
    var confirmPassword = $('#confirmPassword').val();

    if (newPassword !== confirmPassword) {
        alert('Passwords do not match.');
        return;
    }

    if (newPassword.length < 8 || !/[A-Z]/.test(newPassword) || !/[a-z]/.test(newPassword) || !/[0-9]/.test(newPassword) || !/[^A-Za-z0-9]/.test(newPassword)) {
        alert('Password must be at least 8 characters long and include upper case, lower case, number, and special character.');
        return;
    }

    $.ajax({
        url: 'changePassword.php',
        type: 'POST',
        data: {
            oldPassword: oldPassword,
            newPassword: newPassword
        },
        success: function(response) {
            if (response === 'success') {
                alert('Password changed successfully. You will be logged out.');
                window.location.href = 'logout_action.php';
            } else {
                alert('Old password incorrect.');
            }
        }
    });
});
