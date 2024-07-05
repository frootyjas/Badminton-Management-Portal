document.addEventListener("DOMContentLoaded", function() {
    const uploadButton = document.getElementById("uploadButton");
    const imageInput = document.getElementById("imageInput");
    const imagePreviewContainer = document.getElementById("imagePreviewContainer");
    let images = [];

    if (uploadButton && imageInput && imagePreviewContainer) {
        uploadButton.addEventListener("click", () => {
            imageInput.click();
        });

        imageInput.addEventListener("change", function() {
            const files = Array.from(this.files);
            files.forEach(file => {
                if (images.length >= 5) {
                    alert("You can upload a maximum of 5 images.");
                    return;
                }

                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => {
                    const id = Date.now().toString();
                    images.push({ id, src: reader.result, file });
                    updateImagePreviews();
                };
            });
        });

        function updateImagePreviews() {
            imagePreviewContainer.innerHTML = '';
            images.forEach(image => {
                const div = document.createElement('div');
                div.classList.add('image-preview');
                div.innerHTML = `
                    <img src="${image.src}" alt="Image Preview">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeImage('${image.id}')"><i class="fas fa-times"></i></button>
                `;
                imagePreviewContainer.appendChild(div);
            });
        }

        window.removeImage = function(id) {
            images = images.filter(image => image.id !== id);
            updateImagePreviews();
        };
    }

    submitFormButton.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default form submission
        const formData = new FormData(document.getElementById('announcementForm'));
        images.forEach(image => {
            formData.append('image[]', image.file, image.file.name);
        });
    
        fetch('createAnnouncementAdmin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log('Success:', data);
            // Redirect based on the form type
            const typeSelector = document.getElementById('typeSelector').value;
            switch(typeSelector) {
                case 'announcement':
                    window.location.href = "manageAnnouncements.php";
                    break;
                case 'event':
                    window.location.href = "manageEvents.php";
                    break;
                case 'tournament':
                    window.location.href = "manageTournaments.php";
                    break;
                default:
                    window.location.href = "managePosts.php";
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
    


    // Event Form Switch for Event
    const registrationSwitchEvent = document.getElementById('registrationSwitchEvent');
    const feeSwitchEvent = document.getElementById('feeSwitchEvent');
    const registrationFieldsEvent = document.getElementById('registrationFieldsEvent');
    const feeFieldsEvent = document.getElementById('feeFieldsEvent');

    if (registrationSwitchEvent && feeSwitchEvent && registrationFieldsEvent && feeFieldsEvent) {
        registrationSwitchEvent.addEventListener('change', function () {
            if (registrationSwitchEvent.checked) {
                registrationFieldsEvent.classList.add('visible');
            } else {
                registrationFieldsEvent.classList.remove('visible');
            }
        });

        feeSwitchEvent.addEventListener('change', function () {
            if (feeSwitchEvent.checked) {
                feeFieldsEvent.classList.add('visible');
            } else {
                feeFieldsEvent.classList.remove('visible');
            }
        });
    }

    // Tournament Form Switch
    const registrationSwitchTournament = document.getElementById('registrationSwitchTournament');
    const feeSwitchTournament = document.getElementById('feeSwitchTournament');
    const registrationFieldsTournament = document.getElementById('registrationFieldsTournament');
    const feeFieldsTournament = document.getElementById('feeFieldsTournament');

    if (registrationSwitchTournament && feeSwitchTournament && registrationFieldsTournament && feeFieldsTournament) {
        registrationSwitchTournament.addEventListener('change', function () {
            if (registrationSwitchTournament.checked) {
                registrationFieldsTournament.classList.add('visible');
            } else {
                registrationFieldsTournament.classList.remove('visible');
            }
        });

        feeSwitchTournament.addEventListener('change', function () {
            if (feeSwitchTournament.checked) {
                feeFieldsTournament.classList.add('visible');
            } else {
                feeFieldsTournament.classList.remove('visible');
            }
        });
    }
});

function addQualificationField() {
    var qualificationFields = document.getElementById('qualificationFields');
    var inputField = document.createElement('div');
    inputField.classList.add('qualification-input');
    inputField.innerHTML = `<div class="input-with-button">
                                <input type="text" name="qualification[]" class="form-control">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeQualificationField(this)"><i class="fas fa-times"></i></button>
                           </div>`;
    qualificationFields.appendChild(inputField);
}

function removeQualificationField(button) {
    var inputField = button.parentNode.parentNode; // To traverse two levels up
    var qualificationFields = document.getElementById('qualificationFields');
    qualificationFields.removeChild(inputField);
}
