/**
 * Get the current token available in the page.
 * @returns {string} Current token
 */
export function getToken() {
    return document.getElementById('tokenField').value;
}

/**
 * Display an error message on the page.
 * The message will disapear after 2 seconds.
 * 
 * @param {string} errorMessage 
 */
export function displayError(errorMessage) {
    const notif = document.createElement('li');
    notif.classList.add('error');
    notif.textContent = errorMessage;

    document.getElementById('notification-wrapper').appendChild(notif);
    setTimeout(() => notif.remove(), 2000);
}

/**
 * Display a notification on the page.
 * The notification will disapear after 2 seconds.
 * 
 * @param {string} errorMessage 
 */
export function displayNotification(notification) {
    const notif = document.createElement('li');
    notif.classList.add('notification');
    notif.textContent = notification;

    document.getElementById('notification-wrapper').appendChild(notif);
    setTimeout(() => notif.remove(), 2000);
}

/**
 * Call the api.php script asynchronously with the HTTP method given.
 * Data object will be sent into the request body. * 
 * 
 * @param {string} method 
 * @param {array} data 
 * @returns 
 */
export async function fetchApi(method, data) {
    try {
        const response = await fetch('api.php', {
            method: method,
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        return response.json();
    }
    catch (error) {
        console.error('Unable to load api');
    }
}