let lastBreakpoint = null;

// Show Password
export function togglePasswordVisibility(id) {
    const checkbox = document.getElementById("showPassword")
    const passwordField = document.getElementById(id)

    if (checkbox.checked) {
        document.getElementById(id).type = "text"
    } else {
        document.getElementById(id).type = "password"
    }
}

/**
 * Change the values of ATK, HP and DEF according to the value of the slider 
 * Sets the amount of materials required
 * 
 * @param {number} value - The level selected
 */
export function updateEnemySlider(value) {
    const sliders = document.querySelectorAll('.stat-slider-input');
    sliders.forEach(slider => {
        slider.value = value
    })
    const maxValue = sliders[0].max // any slider

    // Update level text
    const levelDisplays = document.querySelectorAll(".level-display");
    levelDisplays.forEach(levelDisplay => {
        levelDisplay.textContent = `Level ${value}`;
    });

    // Update active track width
    const activeTracks = document.querySelectorAll('.active-track');
    const percentage = ((value - 1) / (maxValue - 1)) * 100;
    activeTracks.forEach(activeTrack => {
        activeTrack.style.width = percentage + '%';
    })

    // Update stat values
    const statOrder = ["atk", "hp", "def", "spd", "atk", "hp", "def", "spd"]
    const statRows = document.querySelectorAll(".stat-row")
    // Loop through the rows (except SPD row)
    Array.from(statRows).forEach((row, index) => {
        const statKey = statOrder[index];

        const valueEl = row.querySelector(".stat-value");
        if (valueEl) valueEl.textContent = Math.floor(statsTable[value][statKey]);

        const widthEl = row.querySelector(".stat-width");
        if (widthEl) widthEl.style.width = (statsTable[value][statKey] / maxStatValues[statKey]) * 100 + '%';
    });


    // Update material requirements pannel
    const materialsPanel = document.querySelectorAll('.materials-panel');
    const materialsContainer = document.querySelectorAll('.materials-container');

    // Find current breakpoint
    let currentBreakpoint = 0
    if (parseInt(value) >= 20) {
        currentBreakpoint = Math.floor(parseInt(value) / 10) - 1
        if (value == 80) {
            currentBreakpoint -= 1;
        }
    }

    // Check if a breakpoint was crossed
    if (currentBreakpoint !== lastBreakpoint) {
        if (currentBreakpoint === 0) {
            // Close panel if level is lower than 20
            materialsPanel.forEach(panel => {
                panel.style.maxHeight = '0';
                panel.addEventListener("transitionend", function handler() {
                    materialsContainer.forEach(container => container.innerHTML = '');
                    panel.removeEventListener("transitionend", handler);
                })
            })
        } else {
            // Show materials pannel and update materials
            updateMaterials(currentBreakpoint);
            materialsPanel.forEach(panel => {
                panel.style.maxHeight = '400px';
            })
        }
        lastBreakpoint = currentBreakpoint;
    }
}

/**
 * Sets the amount of materials required
 * 
 * @param {number} breakpoint - The ascension breakpoint reached (1st breakpoint for level 20, 2nd for level 30, etc)
 */
function updateMaterials(breakpoint) {
    const materialsContainer = document.querySelectorAll('.materials-container');
    // Clear existing materials
    materialsContainer.forEach(container => {
        container.innerHTML = '';
    })

    // Add new materials
    const mats = levelUpMats[breakpoint];
    mats.forEach(mat => {
        const materialEl = document.createElement('div');
        materialEl.className = 'flex flex-col items-center';
        materialEl.innerHTML = `
                                <div class="w-12 h-12 rounded-full bg-[#FFE0E0] p-1 flex items-center justify-center">
                                <img src="${mat.material.image_url}" alt="${mat.material.name}" class="w-10 h-10 object-contain">
                                </div>
                                <span class="text-white text-xs mt-1 text-center break-words max-w-[4rem]">${mat.material.name}</span>
                                <span class="text-yellow-200 text-xs font-bold">×${mat.quantity}</span>
                            `;
        materialsContainer.forEach(container => {
            container.appendChild(materialEl.cloneNode(true));
        })

        setTimeout(() => {
            materialEl.classList.add('animate-fadeIn');
        }, 100 * mats.indexOf(mat));
    });
}

/**
 * Toggles the visibility of the stats panel for lightcone and characters
 * Used for mobile screens
 */
export function hideStatsPanel() {
    const panel = document.querySelector("#statsPanel");
    const isOpen = panel.classList.contains("max-h-[500px]");

    if (isOpen) {
        panel.classList.remove("max-h-[500px]", "opacity-100");
        panel.classList.add("max-h-0", "opacity-0");
    } else {
        panel.classList.remove("max-h-0", "opacity-0");
        panel.classList.add("max-h-[500px]", "opacity-100");
    }
}

/**
 * Updates the data inside the panel
 * @param {HTMLElement} icon - The icon element containing the source data
 * @param {HTMLElement} popUp - The popup element to populate
 */
export function updatePopupContent(icon, popUp) {
    const properties = Array.from(popUp.querySelectorAll("tr:not(#popUpWallpaperRow)")).map(row => {
        let propertyName = row.id.replace(/^popUp(.*)Row$/, "$1");
        const elementToPopulate = row.querySelector(`#popUp${propertyName}`);
        propertyName = propertyName.toLowerCase();

        return { row, propertyName, elementToPopulate }
    });

    properties.forEach(property => {
        if (icon.dataset[property.propertyName]) {
            if (property.elementToPopulate) {
                property.elementToPopulate.textContent = icon.dataset[property.propertyName];
                property.row.style.display = 'table-row';
            }
        } else {
            property.row.style.display = 'none';
        }
    });

    const popupImage = popUp.querySelector("#popUpImage");
    const iconImage = icon.querySelector("img");

    const popUpWallpaper = popUp.querySelector("#popUpWallpaper");
    if (popUpWallpaper) popUpWallpaper.style.backgroundImage = icon.style.backgroundImage;
    if (popupImage) {
        popupImage.src = iconImage?.src;
        popupImage.alt = iconImage?.alt;
        popupImage.title = iconImage?.title;
    }
}

/**
 * Positions the popup relative to the hovered icon
 * @param {HTMLElement} icon - The icon element to position the popup relative to
 * @param {HTMLElement} popup - The popup element to position
 */
export function positionPopup(icon, popup) {
    const iconRect = icon.getBoundingClientRect();
    const gap = 10;

    // Temporarily show the popup to measure the width
    const prevDisplay = popup.style.display;
    popup.style.visibility = 'hidden';
    popup.style.display = 'block';
    const popupWidth = popup.offsetWidth;
    const popupHeight = popup.offsetHeight;
    popup.style.display = prevDisplay;
    popup.style.visibility = '';

    let left = iconRect.right + gap;
    const top = iconRect.top + (iconRect.height / 2) - (popup.offsetHeight / 2);

    // If there's not enough space, flip the popup to the left
    if (left + popupWidth > window.innerWidth) {
        left = iconRect.left - popupWidth - gap;
    }

    if (left < gap) left = gap;

    popup.style.left = left + 'px';
    popup.style.top = top + 'px';
}
