document.addEventListener('DOMContentLoaded', function () {
   // let topPanelLinkIndex = document.querySelectorAll('#top-panel-links-container .section').length;
    
   const sectionsContainer = document.getElementById('sections-container')

    const sections = document.getElementsByClassName('section')
    if(!sections.length){
        sectionsContainer.style.display = 'none'
    }

    // Dynamic visibility for settings options
    const serveHdocCheckbox = document.getElementById('serve-hdoc-checkbox');
    const urlPrefixOption = document.getElementById('url-prefix-option');
    const prefixDescription = document.getElementById('prefix-description');
    const removalSelectorsOption = document.getElementById('removal-selectors-option');

    function toggleSettingsVisibility() {
        if (serveHdocCheckbox && urlPrefixOption && prefixDescription && removalSelectorsOption) {
            if (serveHdocCheckbox.checked) {
                // Show URL prefix option and description, hide removal selectors
                urlPrefixOption.style.display = 'block';
                prefixDescription.style.display = 'block';
                removalSelectorsOption.style.display = 'none';
            } else {
                // Hide URL prefix option and description, show removal selectors
                urlPrefixOption.style.display = 'none';
                prefixDescription.style.display = 'none';
                removalSelectorsOption.style.display = 'block';
            }
        }
    }

    // Add event listener for checkbox changes
    if (serveHdocCheckbox) {
        serveHdocCheckbox.addEventListener('change', toggleSettingsVisibility);
    }


   let sectionIndex = document.querySelectorAll('#sections-container .section').length;

    const selectImageButton = document.getElementById('select-image');
    const imageUrlInput = document.getElementById('image-url');

    selectImageButton.addEventListener('click', () => {
        // Open WordPress Media Library
        const mediaFrame = wp.media({
            title: 'Select Image',
            button: { text: 'Use This Image' },
            multiple: false,
        });

        mediaFrame.on('select', () => {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            imageUrlInput.value = attachment.url; // Set the URL of the selected image
        });

        mediaFrame.open();
    });
    // Add a new section
     // Delegate event for dynamically added elements
     document.getElementById('top-panel-links-container').addEventListener('click', function (event) {
        const target = event.target;

        // Add a new link
        if (target.classList.contains('add-link')) {
            const section = target.closest('#top-panel-links-container');
            const linkIndex = section.querySelectorAll('.link').length;
            const linkTemplate = `
            <div class="link">
                <label>Link text: </label>
                <input type="text" name="stwbpb_settings[top_panel][links][${linkIndex}][text]" value="" />
                <div class="spacerH10"></div>
                <label>Link URL: </label>
                <input type="text" name="stwbpb_settings[top_panel][links][${linkIndex}][url]" value="" />
                <div class="spacerH10"></div>
                <button type="button" class="remove-link">Remove Link</button>
            </div>`;
            section.querySelector('.links').insertAdjacentHTML('beforeend', linkTemplate);
        }

        // Remove a link
        if (target.classList.contains('remove-link')) {
            target.closest('.link').remove();
        }

        // Remove a section
        if (target.classList.contains('remove-section')) {
            target.closest('.section').remove();
        }
    });
    // Add a new section
    document.getElementById('add-section').addEventListener('click', function () {
        const sectionTemplate = `
        <div class="section">
            <label>Section Title: </label>
            <input class="single-text-input" type="text" name="stwbpb_settings[bottom_panel][sections][${sectionIndex}][title]" value="" />
            <div class="spacerH10"></div>
            <div class="links"></div>
            <button type="button" class="add-link">Add Link</button>
            <div class="spacerH10"></div>
            <button type="button" class="remove-section">Remove Section</button>
        </div>`;

        const sectionsContainer = document.getElementById('sections-container')
        sectionsContainer.insertAdjacentHTML('beforeend', sectionTemplate);
        sectionsContainer.style.display = 'block'
        
        sectionIndex++;
    });

    // Delegate event for dynamically added elements
    document.getElementById('sections-container').addEventListener('click', function (event) {
        const target = event.target;

        // Add a new link
        if (target.classList.contains('add-link')) {
            const section = target.closest('.section');
            // Get the parent container of all sections
            const sectionsContainer = document.querySelector('#sections-container');
            
            // Find the index of the current section
            const sections = Array.from(sectionsContainer.querySelectorAll('.section'));
            const sectionIndex = sections.indexOf(section); // Get the correct index
            const linkIndex = section.querySelectorAll('.link').length;
            //const sectionName = section.querySelector('input[type="text"]').name.replace('title', `links[${linkIndex}][url]`);
            const linkTemplate = `
            <div class="link">
                <label>Link text: </label>
                <input type="text" name="stwbpb_settings[bottom_panel][sections][${sectionIndex}][links][${linkIndex}][text]" value="" />
                <div class="spacerH10"></div>
                <label>Link URL: </label>
                <input type="text" name="stwbpb_settings[bottom_panel][sections][${sectionIndex}][links][${linkIndex}][url]" value="" />
                <div class="spacerH10"></div>
                <button type="button" class="remove-link">Remove Link</button>
            </div>`;
            section.querySelector('.links').insertAdjacentHTML('beforeend', linkTemplate);
        }

        // Remove a link
        if (target.classList.contains('remove-link')) {
            target.closest('.link').remove();
        }

        // Remove a section
        if (target.classList.contains('remove-section')) {
            target.closest('.section').remove();
            const sectionsContainer = document.getElementById('sections-container')

            const sections = document.getElementsByClassName('section')
            if(!sections.length){
                sectionsContainer.style.display = 'none'
            }
        }
    });
});
