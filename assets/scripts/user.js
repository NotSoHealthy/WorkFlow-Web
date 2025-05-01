function openConfirmation(event){
    let button = event.target;
    let confirmationContainer = document.querySelector(".confirmation-container");
    let confirmButton = confirmationContainer.querySelector(".btn-success");
    var link = button.getAttribute("data-link");

    confirmButton.setAttribute("href", link);
    confirmationContainer.style.display = "block";
}

function closeConfirmation(){
    let confirmationContainer = document.querySelector(".confirmation-container");
    confirmationContainer.style.display = "none";
}

$(document).ready(function () {
    if (!$.fn.DataTable.isDataTable('#employeesTable')) {
        $('#employeesTable').DataTable({
            responsive: true,
            language: {
                emptyTable: "Aucun employé trouvé",
                lengthMenu: "Afficher _MENU_ entrées",
                info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                infoEmpty: "Affichage de 0 à 0 sur 0 entrées",
                zeroRecords: "Aucun employé trouvé",
                search: "Rechercher:",
                paginate: {
                    first: "Premier",
                    last: "Dernier",
                    next: "Suivant",
                    previous: "Précédent"
                }
            },
            order: [[0, "asc"]],
            columnDefs: [
                { orderable: false, targets: [3] },
                { width: '30%', targets: [0, 1, 2] },
                { width: '5%', targets: [3] }
            ]
        });
    }
});
