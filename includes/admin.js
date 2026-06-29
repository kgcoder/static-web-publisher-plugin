document.addEventListener('DOMContentLoaded', function () {
   // let topPanelLinkIndex = document.querySelectorAll('#top-panel-links-container .section').length;

   const sectionsContainer = document.getElementById('sections-container')

    if (sectionsContainer) {
        const sections = document.getElementsByClassName('section')
        if(!sections.length){
            sectionsContainer.style.display = 'none'
        }
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


   let sectionIndex = sectionsContainer ? document.querySelectorAll('#sections-container .section').length : 0;

    const selectImageButton = document.getElementById('select-image');
    const imageUrlInput = document.getElementById('image-url');

    if (selectImageButton) {
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
    }

    // Add a new section
     // Delegate event for dynamically added elements
    const topPanelLinksContainer = document.getElementById('top-panel-links-container')
    if (topPanelLinksContainer) {
        topPanelLinksContainer.addEventListener('click', function (event) {
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
    }

    // Add a new section
    const addSectionBtn = document.getElementById('add-section')
    if (addSectionBtn) {
        addSectionBtn.addEventListener('click', function () {
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

            sectionsContainer.insertAdjacentHTML('beforeend', sectionTemplate);
            sectionsContainer.style.display = 'block'

            sectionIndex++;
        });
    }

    // Sidebar sections
    const sidebarFieldPrefix = (window.swpSidebarFieldPrefix !== undefined)
        ? window.swpSidebarFieldPrefix
        : 'stwbpb_settings[post_sidebar]'

    let sidebarSectionIndex = document.querySelectorAll('#sidebar-sections-container .sidebar-section').length

    function sidebarSectionTemplate(idx) {
        return `
        <div class="sidebar-section">
            <div class="settings-option-div">
                <label>Section type: </label>
                <div class="spacerW10"></div>
                <select name="${sidebarFieldPrefix}[sections][${idx}][type]" class="sidebar-section-type">
                    <option value="search">Search</option>
                    <option value="recent-comments">Recent Comments</option>
                    <option value="links">Links</option>
                    <option value="recent-posts">Recent Posts</option>
                </select>
            </div>
            <div class="sidebar-section-fields sidebar-type-search">
                <div class="settings-option-div">
                    <label>Search action URL (use %s for the search term): </label>
                    <div class="spacerW10"></div>
                    <input class="single-text-input" type="text" name="${sidebarFieldPrefix}[sections][${idx}][action]" value="" />
                </div>
                <div class="settings-option-div">
                    <label>Placeholder: </label>
                    <div class="spacerW10"></div>
                    <input class="single-text-input" type="text" name="${sidebarFieldPrefix}[sections][${idx}][placeholder]" value="" />
                </div>
                <div class="settings-option-div">
                    <label>Open results in: </label>
                    <div class="spacerW10"></div>
                    <select name="${sidebarFieldPrefix}[sections][${idx}][target]">
                        <option value="_self">Same tab</option>
                        <option value="_blank">New tab</option>
                    </select>
                </div>
            </div>
            <div class="sidebar-section-fields sidebar-type-recent-comments" style="display:none">
                <div class="settings-option-div">
                    <label>Title: </label>
                    <div class="spacerW10"></div>
                    <input class="single-text-input" type="text" name="${sidebarFieldPrefix}[sections][${idx}][title]" value="" disabled />
                </div>
                <div class="settings-option-div">
                    <label>Max number of comments: </label>
                    <div class="spacerW10"></div>
                    <input type="number" min="1" name="${sidebarFieldPrefix}[sections][${idx}][max]" value="5" disabled />
                </div>
                <div class="settings-option-div">
                    <label>Format (use {author} and {post}): </label>
                    <div class="spacerW10"></div>
                    <input class="single-text-input" type="text" name="${sidebarFieldPrefix}[sections][${idx}][format]" value="" disabled />
                </div>
                <div class="settings-option-div">
                    <label>Include excerpt</label>
                    <div class="spacerW10"></div>
                    <input class="single-checkbox-input" type="checkbox" name="${sidebarFieldPrefix}[sections][${idx}][include_excerpt]" value="1" disabled />
                </div>
            </div>
            <div class="sidebar-section-fields sidebar-type-links" style="display:none">
                <div class="settings-option-div">
                    <label>Title: </label>
                    <div class="spacerW10"></div>
                    <input class="single-text-input" type="text" name="${sidebarFieldPrefix}[sections][${idx}][title]" value="" disabled />
                </div>
                <div class="sidebar-links"></div>
                <button type="button" class="add-sidebar-link">Add Link</button>
            </div>
            <div class="sidebar-section-fields sidebar-type-recent-posts" style="display:none">
                <div class="settings-option-div">
                    <label>Title: </label>
                    <div class="spacerW10"></div>
                    <input class="single-text-input" type="text" name="${sidebarFieldPrefix}[sections][${idx}][title]" value="" disabled />
                </div>
                <div class="settings-option-div">
                    <label>Max number of posts: </label>
                    <div class="spacerW10"></div>
                    <input type="number" min="1" name="${sidebarFieldPrefix}[sections][${idx}][max]" value="5" disabled />
                </div>
            </div>
            <div class="spacerH10"></div>
            <button type="button" class="remove-sidebar-section">Remove Section</button>
        </div>`
    }

    const addSidebarSectionBtn = document.getElementById('add-sidebar-section')
    if (addSidebarSectionBtn) {
        addSidebarSectionBtn.addEventListener('click', function () {
            const container = document.getElementById('sidebar-sections-container')
            container.insertAdjacentHTML('beforeend', sidebarSectionTemplate(sidebarSectionIndex))
            container.style.display = 'block'
            sidebarSectionIndex++
        })
    }

    const sidebarSectionsContainer = document.getElementById('sidebar-sections-container')
    if (sidebarSectionsContainer) {
        sidebarSectionsContainer.addEventListener('change', function (event) {
            const target = event.target
            if (target.classList.contains('sidebar-section-type')) {
                const section = target.closest('.sidebar-section')
                section.querySelectorAll('.sidebar-section-fields').forEach(function (el) {
                    el.style.display = 'none'
                    el.querySelectorAll('input, select, textarea').forEach(function (input) {
                        input.disabled = true
                    })
                })
                const selected = target.value.replace(/[^a-z-]/g, '')
                const activeFields = section.querySelector('.sidebar-type-' + selected)
                if (activeFields) {
                    activeFields.style.display = ''
                    activeFields.querySelectorAll('input, select, textarea').forEach(function (input) {
                        input.disabled = false
                    })
                }
            }
        })

        sidebarSectionsContainer.addEventListener('click', function (event) {
            const target = event.target

            if (target.classList.contains('add-sidebar-link')) {
                const section = target.closest('.sidebar-section')
                const allSections = Array.from(document.querySelectorAll('#sidebar-sections-container .sidebar-section'))
                const secIdx = allSections.indexOf(section)
                const linkIdx = section.querySelectorAll('.sidebar-link').length
                const linkTemplate = `
            <div class="sidebar-link">
                <label>Link text: </label>
                <input type="text" name="${sidebarFieldPrefix}[sections][${secIdx}][links][${linkIdx}][text]" value="" />
                <div class="spacerH10"></div>
                <label>Link URL: </label>
                <input type="text" name="${sidebarFieldPrefix}[sections][${secIdx}][links][${linkIdx}][url]" value="" />
                <div class="spacerH10"></div>
                <button type="button" class="remove-sidebar-link">Remove Link</button>
            </div>`
                section.querySelector('.sidebar-links').insertAdjacentHTML('beforeend', linkTemplate)
            }

            if (target.classList.contains('remove-sidebar-link')) {
                target.closest('.sidebar-link').remove()
            }

            if (target.classList.contains('remove-sidebar-section')) {
                target.closest('.sidebar-section').remove()
                const container = document.getElementById('sidebar-sections-container')
                if (!container.querySelectorAll('.sidebar-section').length) {
                    container.style.display = 'none'
                }
            }
        })
    }

    // Delegate event for dynamically added elements
    if (sectionsContainer) {
        sectionsContainer.addEventListener('click', function (event) {
            const target = event.target;

            // Add a new link
            if (target.classList.contains('add-link')) {
                const section = target.closest('.section');
                // Get the parent container of all sections
                const sc = document.querySelector('#sections-container');

                // Find the index of the current section
                const sections = Array.from(sc.querySelectorAll('.section'));
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
                const sc = document.getElementById('sections-container')

                const sections = document.getElementsByClassName('section')
                if(!sections.length){
                    sc.style.display = 'none'
                }
            }
        });
    }
});
