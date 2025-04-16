window.onload = function () {
    if (document.querySelector('.alert-danger')) {
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