import './bootstrap';

// Global functions
import { setupNotifications, newNotification, closeNotification } from './notifications';

window.closeNotification = closeNotification;
window.setupNotifications = setupNotifications;
window.newNotification = newNotification;

window.previewAndUpload = function(input) {
    const file = input.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        newNotification("error", "File size must be 2 MB or smaller.")
        return;
    }

    const previews = document.querySelectorAll(".user-picture");

    // Show instant preview
    const reader = new FileReader();
    reader.onload = e => {
        previews.forEach(preview => {
            preview.src = e.target.result;
        });
    };
    reader.readAsDataURL(file);

    // Upload to server
    const formData = new FormData();
    formData.append('profile_pic', file);

    fetch('/api/profile/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                newNotification("success", "Profile picture updated successfully.");
            } else {
                newNotification("error", `Upload failed: ${data.message || 'Unknown error'}`);
            }
        })
        .catch(err => {
            newNotification("error", `Upload error: ${err}`);
        });
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const urlParams = new URLSearchParams(window.location.search);

        // Call setupNotifications to initialize notifications
        setupNotifications();

        // Load modal functionality if modal elements exists
        const modalConfigs = [
            { selector: '#imageModal', functions: ['openImageModal', 'closeImageModal'] },
            { selector: '#filterModal', functions: ['openFilterModal', 'closeFilterModal'] },
            { selector: '#timelineModal', functions: ['openTimelineModal', 'closeTimelineModal'] },
            { selector: '#modalLoading', functions: ['openLoadingModal', 'closeLoadingModal', 'stopFetch'] },
            { selector: '#uniqueBuffsModal', functions: ['openUniqueBuffsModal', 'closeUniqueBuffsModal']}];

        // Find which modals exist on the page
        const existingModals = modalConfigs.filter(({ selector }) =>
            document.querySelector(selector)
        );

        // Import modal module only once if any modals exist
        if (existingModals.length > 0) {
            import('./modal').then(modalModule => {
                existingModals.forEach(({ functions }) => {
                    functions.forEach(funcName => {
                        window[funcName] = modalModule[funcName];
                    });
                });
            });
        }

        const profilePicInput = document.querySelectorAll('.profile-pic-input');
        profilePicInput.forEach(input => {
            input.addEventListener('change', e => previewAndUpload(e.target));
        })

        // Load password visibility toggle if that field exists
        if (document.getElementById('showPassword')) {
            import('./utils').then(({ togglePasswordVisibility }) => {
                document.getElementById('showPassword').addEventListener('change', () => {
                    togglePasswordVisibility('password');
                });
            });
        }

        // Load home page functionality on home page
        if (document.getElementById('homeContent')) {
            import('./pages/home.js');
        }

        // Load register page functionality on register page
        if (document.getElementById('registerform')) {
            import('./pages/register').then(({ checkUsernameField, checkFormErrors, checkPasswordField }) => {
                document.getElementById("username")?.addEventListener('change', (event) => {
                    checkUsernameField(event.target.value);
                });

                [document.getElementById("password"), document.getElementById("passwordConfirmation")].forEach(field => {
                    field.addEventListener("change", checkPasswordField);
                })

                document.getElementById("registerform")?.addEventListener('submit', (event) => {
                    event.preventDefault();
                    checkFormErrors(event);
                });
            });
        }

        // Load login page functionality on login page
        if (document.getElementById("loginForm")) {
            import('./pages/login').then(({ toggleForm, sendVerificationCode, checkPasswords }) => {
                document.getElementById("toggleLink")?.addEventListener("click", toggleForm);
                if (urlParams.get('panel') === 'changepassword') toggleForm()
                history.replaceState(null, '', window.location.pathname);
                document.getElementById("sendCodeBtn")?.addEventListener("click", (e) => sendVerificationCode(e.target));
                document.getElementById("changePasswordForm")?.addEventListener("submit", checkPasswords);
            });
        }

        // Load item page functionality on character and lightcone index pages
        if (document.getElementById('characterPage') || document.getElementById('lightconePage')) {
            import('./pages/item.js');
        }

        // Load item page functionality on character show pages
        if (document.getElementById('characterContent')) {
            import('./pages/character/show.js').then(mod => mod.default());
        }

        if (document.getElementById('uniqueBuffsCyreneModal')) {
            import('./pages/modal/unique-buffs-cyrene.js');
        }        

        // Load item page functionality on lightcone show pages
        if (document.getElementById('lightconeContent')) {
            import('./pages/lightcone/show.js').then(mod => mod.default());
        }

        // Load patch page functionality on patch list page
        if (document.getElementById('patchContent')) {
            import('./pages/patch.js').then(mod => mod.default());
        }

        // Load relic page functionality on relic page
        if (document.getElementById('relicContent')) {
            import('./pages/relic.js').then(mod => mod.default());
        }

        // Load user SPA on user page
        if (document.getElementById("userApp")) {
            import('./pages/user/user-app.js');
        }

        // Load admin SPA on admin page
        if (document.getElementById("adminApp")) {
            import('./pages/admin/admin-app.js');
        }

        // Load gacha page functionality on gacha page
        if (document.getElementById("gachaContent")) {
            import('./pages/gacha.js');
        }
    } catch (error) {
        console.error('Init failed', error);
    }
});