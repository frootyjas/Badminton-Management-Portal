document.addEventListener('DOMContentLoaded', function () {
    const showFormButton = document.getElementById('showFormButton');
    const postForm = document.getElementById('postForm');

    showFormButton.addEventListener('click', function () {
        if (postForm.style.display === 'none' || postForm.style.display === '') {
            postForm.style.display = 'block';
            showFormButton.style.display = 'none';
        } else {
            postForm.style.display = 'none';
        }
    });
});
