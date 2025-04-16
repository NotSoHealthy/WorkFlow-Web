var months = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
var days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
var today = new Date();

window.onload = function () {
    loadHours();
    loadWeek();
    var month = document.getElementById('month');
    month.innerHTML =months[today.getMonth()]+' '+today.getFullYear();
}

function loadHours() {
    var lines = document.querySelectorAll('.time-line');
    var dates = document.querySelectorAll('.time-date');
    var attendances = JSON.parse(document.getElementById('time-container').getAttribute('data-attendances'));
    if (attendances.length == 0) {
        return;
    }
        var date = new Date(attendances[0].date);
    
    for (let i = 0; i <7 ; i++) {
        if(date.getDay() == 0 || date.getDay() == 6){
            date.setDate(date.getDate() + 1);
            i--;
            continue;
        }

        dates[i].innerHTML = days[date.getDay()]+"."+date.getDate();
        date.setDate(date.getDate() + 1);

        if ( i < attendances.length ) {
            let entryTime = `1970-01-01T${attendances[i].entry_time.substring(0, 5)}:00Z`;
            let exitTime = `1970-01-01T${attendances[i].exit_time.substring(0, 5)}:00Z`;
            let workedMinutes = (new Date(exitTime) - new Date(entryTime)) / (1000 * 60);
            let percentageValue = (workedMinutes / 480) * 100;
            percentageValue = Math.max(0, Math.min(100, percentageValue));
            let percentage = Math.round(percentageValue);

            lines[i].style.height = percentage + '%';
            lines[i].setAttribute('data-toggle', 'tooltip');
            lines[i].setAttribute('title', attendances[i].entry_time + ' - ' + attendances[i].exit_time);
        }
        else{
            lines[i].style.height = '0%';
        }
    }
}

function loadWeek(){
    var day = new Date();
    var d = document.querySelectorAll('.week-day');
    while (days[day.getDay()] != 'Lun') {
        day.setDate(day.getDate() - 1);
    }
    for (let i = 0; i < 7; i++) {
        if (day.getDay().toString() === today.getDay().toString()) {
            console.log(day);
            d[i].classList.add('week-current-day');
        }

        d[i].childNodes[0].innerHTML = days[day.getDay()];
        d[i].childNodes[1].innerHTML = day.getDate();
        day.setDate(day.getDate() + 1);
    }
}