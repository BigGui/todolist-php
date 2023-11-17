
function getToken() {
    return document.getElementById('tokenField').value;
}

function displayError(errorMessage) {
    const notif = document.createElement('li');
    notif.classList.add('error');
    notif.textContent = errorMessage;

    document.getElementById('notification-wrapper').appendChild(notif);
    setTimeout(() => notif.remove(), 2000);
}

function displayNotification(notification) {
    const notif = document.createElement('li');
    notif.classList.add('notification');
    notif.textContent = notification;

    document.getElementById('notification-wrapper').appendChild(notif);
    setTimeout(() => notif.remove(), 2000);
}

const validateBtnArray = document.querySelectorAll('.js-validate-btn');
validateBtnArray.forEach(btn => {
    btn.addEventListener('click', function (event) {
        // Check and validate data
        const data = {
            action: 'done',
            id: parseInt(this.closest('[data-id-task]').dataset.idTask),
            token: getToken()
        };

        if (isNaN(data.id) || data.token.length < 1) {
            displayError('Oups... un problème est survenu');
            return;
        }

        // Send HTTP request to the server with collected datas
        // fetch('api.php?action=done&id=' + id + '&token=' + getToken())
        fetch('api.php', {
                method: 'PUT',
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(responseJson => {

                // An error occurs, dispay error message
                if (!responseJson.result) {
                    displayError(responseJson.error);
                    return;
                }

                // Update user interface
                document.querySelector(`[data-id-task="${data.id}"]`).remove();
                
                // Notify user
                displayNotification(responseJson.notification);
            });
    })
});