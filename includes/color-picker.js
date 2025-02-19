document.addEventListener('DOMContentLoaded', function () {
    const colorFields = document.querySelectorAll('.my-color-field');
    if (colorFields.length > 0) {
        colorFields.forEach((field) => {
            jQuery(field).wpColorPicker();
        });
    }
});