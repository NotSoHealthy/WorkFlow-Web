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