window.onload = function () {
    if (document.querySelector('.request-container .invalid-feedback')) {
        document.querySelector('.request-container').style.display = 'block';
    }
}

function selectChange() {
    var select = document.getElementById('select');
    var value = select.value;
    switch (value) {
        case 'pending':
            select.setAttribute('class', 'select-pending');
            break;
        case 'approved':
            select.setAttribute('class', 'select-approved');
            break;
        case 'rejected':
            select.setAttribute('class', 'select-rejected');
            break;
    }
}

function openRequest() {
    var container = document.querySelector('.request-container');
    container.style.display=='block' ? container.style.display='none' : container.style.display='block';
}

function cancelRequest(){
    var container = document.querySelector('.request-container');
    container.style.display='none';
}

$(document).ready(function () {
    if ($.fn.DataTable.isDataTable('#congeTable')) {
        $('#congeTable').DataTable().clear().destroy();
    }

    $('#congeTable').DataTable({
        language: {
            emptyTable: "Aucun congé trouvé",
            search: "Rechercher:",
            lengthMenu: "Afficher _MENU_ entrées",
            info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            infoEmpty: "Affichage de 0 à 0 sur 0 entrées",
            zeroRecords: "Aucun congé trouvé",
            paginate: {
                first: "Premier",
                last: "Dernier",
                next: "Suivant",
                previous: "Précédent"
            }
        },
        order: [[0, "asc"]],
        columnDefs: [
            { width: '25%', targets: 0 }
        ]
    });
});
