
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

async function fetchApi(method, data) {
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

document.getElementById('tasksList').addEventListener('click', function (event) {
    if (!event.target.classList.contains('js-validate-btn')) return;

    // Check and validate data
    const data = {
        action: 'done',
        id: parseInt(event.target.closest('[data-id-task]').dataset.idTask),
        token: getToken()
    };

    if (isNaN(data.id) || data.token.length < 1) {
        displayError('Oups... un problème est survenu');
        return;
    }

    // Send HTTP request to the server with collected datas
    fetchApi('PUT', data)
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

});

document.getElementById('formAdd').addEventListener('submit', function (event) {
    event.preventDefault();
    // Check and validate data
    const data = {
        action: 'add',
        token: getToken(),
        text: this.querySelector('input[name="text"]').value
    };

    if (data.text.length < 1) {
        displayError('Merci de saisir le texte de la tâche.');
        return;
    }
    if (data.token.length < 1) {
        displayError('Sécu !? HELP !!!!');
        return;
    }

    fetchApi('POST', data)
        .then(responseApi => {
            // An error occurs, dispay error message
            if (!responseApi.result) {
                displayError(responseApi.error);
                return;
            }

            // Update user interface
            const taskElement = document.importNode(document.getElementById('taskTemplate').content, true);

            taskElement.querySelector('[data-content="text"]').innerText = responseApi.text;
            taskElement.querySelector('[data-id-task]').dataset.idTask = responseApi.idTask;
            document.getElementById('tasksList').appendChild(taskElement);

            document.querySelector('#formAdd input[name="text"]').value = '';
        });
});