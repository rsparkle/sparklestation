const containerSelector = '#notifications-container';

/**
 * Shows existing notifications and hides them after a delay
 */
export function setupNotifications(autoHideMs = 5000, staggerMs = 100) {
    let container = document.querySelector(containerSelector);
    if (!container) {
        container = document.createElement('div');
        container.id = containerSelector.replace('#','');
        document.body.appendChild(container);
    }

    const notifications = container.querySelectorAll('.flash-message');

    notifications.forEach((notification, i) => {
        setTimeout(() => {
            notification.classList.add('show');

            if (autoHideMs > 0) {
                setTimeout(() => {
                    notification.classList.add('hide');
                    setTimeout(() => notification.remove(), 350);
                }, autoHideMs);
            }
        }, i * staggerMs);
    });
}

/**
 * Shows a new notification and auto-removes it
 * @param {string} type - Notification type ('success', 'error' or 'loading')
 * @param {string} message - Text to display in the notification
 * @param {number} [autoHideMs=5000] - Time in ms before it auto-hides
 */
export function newNotification(type, message, autoHideMs = 5000) {
    let container = document.querySelector(containerSelector);
    if (!container) {
        container = document.createElement('div');
        container.id = containerSelector.replace('#','');
        document.body.appendChild(container);
    }

    const wrapper = document.createElement('div');
    wrapper.className = `flash-message ${type}-message z-50`;

    const span = document.createElement('span');
    span.className = 'flash-message-content';
    span.textContent = message;

    const btn = document.createElement('button');
    btn.className = 'text-white';
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Close notification');
    btn.textContent = '✖';
    btn.addEventListener('click', () => closeNotification(btn));

    wrapper.appendChild(span);
    wrapper.appendChild(btn);

    container.appendChild(wrapper);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => wrapper.classList.add('show'));
    });

    if (autoHideMs > 0) {
        setTimeout(() => {
            wrapper.classList.add('hide');
            setTimeout(() => wrapper.remove(), 350);
        }, autoHideMs);
    }

    return wrapper;
}

/**
 * Manually closes a notification when user clicks the close button
 * @param {HTMLElement} element - The clicked close button element
 */
export function closeNotification(element) {
    const notification = element.closest('.flash-message');

    if (notification) {
        notification.classList.add('hide');
        setTimeout(() => notification.remove(), 350);
    }
}
