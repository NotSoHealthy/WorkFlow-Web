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

function request() {
    var request = document.getElementById('request-row');
    request.style.display=='' ? request.style.display='none' : request.style.display='';
}

function cancelRequest(){
    var request = document.getElementById('request-row');
    request.style.display='none';
}