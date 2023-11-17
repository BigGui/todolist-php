import * as utils from './functions';

document.getElementById('tasksList').addEventListener('click', function (event) {
    if (!event.target.classList.contains('js-validate-btn')) return;

    // Check and validate data
    const data = {
        action: 'done',
        id: parseInt(event.target.closest('[data-id-task]').dataset.idTask),
        token: utils.getToken()
    };

    if (isNaN(data.id) || data.token.length < 1) {
        utils.displayError('Oups... un problème est survenu');
        return;
    }

    // Send HTTP request to the server with collected datas
    utils.fetchApi('PUT', data)
        .then(responseJson => {
            // An error occurs, dispay error message
            if (!responseJson.result) {
                utils.displayError(responseJson.error);
                return;
            }

            // Update user interface
            document.querySelector(`[data-id-task="${data.id}"]`).remove();

            // Notify user
            utils.displayNotification(responseJson.notification);
        });

});

document.getElementById('formAdd').addEventListener('submit', function (event) {
    event.preventDefault();
    // Check and validate data
    const data = {
        action: 'add',
        token: utils.getToken(),
        text: this.querySelector('input[name="text"]').value
    };

    if (data.text.length < 1) {
        utils.displayError('Merci de saisir le texte de la tâche.');
        return;
    }
    if (data.token.length < 1) {
        utils.displayError('Sécu !? HELP !!!!');
        return;
    }

    utils.fetchApi('POST', data)
        .then(responseApi => {
            // An error occurs, dispay error message
            if (!responseApi.result) {
                utils.displayError(responseApi.error);
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