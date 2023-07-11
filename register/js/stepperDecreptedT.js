$(document).on("click", ".freeAppointment", function() {
    $(".selected-time-appointment").css("border", "1px solid #7b7b7b70").css('box-shadow','none').removeClass("selected-time-appointment");
    $(this).css("border", "1px solid #0c8656").css('box-shadow','inset 0px 0px 50px 0px #242424').addClass('selected-time-appointment');
    $('#timePreviewStamp').html(this.innerText);

});

const debounce = (func, wait, immediate)=> {
    var timeout;
    return function executedFunction() {
        var context = this;
        var args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};

$(document).on("click", ".saveAppointment", function() { saveAppointment();});

$(document).ready(function () {
    document.querySelectorAll("input").forEach(input => {input.addEventListener("focus", function() {this.style.backgroundColor = "#f8f9fa";});input.addEventListener("blur", function() {this.style.backgroundColor = "#ffffff";});});
    document.querySelectorAll("input").forEach(input => {input.style.transition = "all 0.2s ease-in-out";});
    document.querySelectorAll("label").forEach(label => {label.style.fontSize = "1.2rem";});
    document.querySelectorAll("input").forEach(input => {input.style.fontSize = "1.2rem";});
});

function getSelectedServices() {
    const checkboxes = document.querySelectorAll('.serviceSelector');
    let selectedServices = [];
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selectedServices.push(checkbox.id);
        }
    });
    return selectedServices;
}

function activateHover(id,name){
    const customHover = document.getElementById("hover-"+id);
    if(customHover.classList.contains("active-custom-hover")){
        customHover.classList.remove("active-custom-hover");
        if (name) {
            document.getElementById(name).checked = false;
        }
    }else{
        customHover.classList.add("active-custom-hover");
        if (name) {
            document.getElementById(name).checked = true;
        }
    }
    let selectedServices = getSelectedServices();
    if(selectedServices.length > 0){
            $('.controls').html('<button class="customButton" onclick="stepDetector(\'next\',0)" style=" width: 80%;height: 40px;border-radius: 10px;margin-left: auto;margin-right: auto">Next Step</button>');
    }else{
        $('.controls').html('');
    }
}

function stepDetector(type,stepNum){
    const step1 = document.getElementById("tab1");
    const step2 = document.getElementById("tab2");
    const step3 = document.getElementById("tab3");
    var stepCounter = 1;
    if(stepNum > 0){
        stepCounter = stepNum;
    }else{
        if(step1.classList.contains("active")){ stepCounter++; }
        if(step2.classList.contains("active")){ stepCounter++; }
        if(step3.classList.contains("active")){ stepCounter++; }
    }
    if(stepCounter === 1){
        step3.classList.remove("active");
        step3.classList.remove("active-stepp");
        $('.tab-3-content').removeClass('active');
        step2.classList.remove("active");
        step2.classList.remove("active-stepp");
        $('.tab-2-content').removeClass('active');
        step1.classList.add("active");
        step1.classList.add("active-stepp");
        $('.tab-1-content').addClass('active');
        $('#line1 > p').removeClass('active-stepp');
        $('#line2 > p').removeClass('active-stepp');
        $('.controls').html('<button class="customButton" onclick="stepDetector(\'next\',2)" style=" width: 80%;height: 40px;border-radius: 10px;margin-left: auto;margin-right: auto">Next Step</button>');
    }
    if(stepCounter === 2){
        $('.loaderCont').css('display','flex');
        $('#displayCont').css('display', 'none');
        $('.selectedServcesPreview').html(printArray(getSelectedServices()));
        getPriceAndTimeByServices(getSelectedServices());
        step3.classList.remove("active");
        step3.classList.remove("active-stepp");
        $('.tab-3-content').removeClass('active');
        $('.tab-1-content').removeClass('active');
        $('#line1 > p').addClass('active-stepp');
        $('#line2 > p').removeClass('active-stepp');
        step1.classList.add("active");
        step1.classList.add("active-stepp");
        step2.classList.add("active");
        step2.classList.add("active-stepp");
        $('.tab-2-content').addClass('active');
        $('.controls').html('<button class="customButton" onclick="stepDetector(\'prev\',1)" style=" width: 40%;height: 40px;border-radius: 10px;margin-left: auto;margin-right: auto">Prev Step</button><button class="customButton" onclick="stepDetector(\'next\',3)" style=" width: 40%;height: 40px;border-radius: 10px;margin-left: auto;margin-right: auto">Next Step</button>');
        if (!$('.fc-view').length || type === 'next') {
            // Perform an action when FullCalendar is rendered
            const workSpec = [
                {
                    daysOfWeek: [1,2,3,4,5,6,7],
                    startTime: '09:00',
                    endTime: '22:00'
                }
            ];
            const workMin = workSpec.map(item => item.startTime).sort().shift();
            const workMax = workSpec.map(item => item.endTime).sort().pop();
            const workDays = [...new Set(workSpec.flatMap(item => item.daysOfWeek))];
            const hideDays = [...Array(7).keys()].filter(day => !workDays.includes(day));
            var Calendar = FullCalendar.Calendar;
            var calendarEl = document.getElementById('calendar');
            var today = new Date();
            var reconstructedDate = today.getFullYear()+'-'+String(today.getMonth() + 1).padStart(2, '0')+'-'+String(today.getDate()).padStart(2, '0');
            var calendar = new Calendar(calendarEl, {
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                nowIndicator: true,
                headerToolbar: {
                    right: 'next',
                    center: 'title',
                    left: 'prev'
                },
                businessHours: workSpec,
                slotMinTime: workMin,
                slotMaxTime: workMax,
                hiddenDays: hideDays,
                editable: false,
                droppable: false,
                aspectRatio: 0.7,
                validRange: {
                    start: today
                },
                dateClick: function(info){
                    if (window.innerWidth < 768) {
                        $('html, body').animate({
                            scrollTop: $(".freeAppointmentCont").offset().top
                        }, 500);
                    }
                    $(".fc-day-today").removeClass("fc-day-today");
                    $(info.dayEl).addClass("fc-day-today");
                    print_available_appointments(info.dateStr);
                    $('#datePreviewStamp').html(info.dateStr);
                }
            });
            calendar.render();
            print_available_appointments(reconstructedDate);
            $('#datePreviewStamp').html(reconstructedDate);
            document.querySelector('.fc-next-button').addEventListener('click', function () {$('#freeAppointments').html('');});
            document.querySelector('.fc-prev-button').addEventListener('click', function () {$('#freeAppointments').html('');  $(".fc-day-today").removeClass("fc-day-today");});
        }
    }
    if(stepCounter === 3){
        if (document.getElementsByClassName("selected-time-appointment").length) {
            $('.loaderCont').css('display','flex');
            $('.finalServiceContainer').css('display','none');
            $('.clockPreview').css('display','none');
            updateClock();
            step2.classList.add("active");
            step2.classList.add("active-stepp");
            step1.classList.add("active");
            step1.classList.add("active-stepp");
            step3.classList.add("active");
            step3.classList.add("active-stepp");
            $('#line1 > p').addClass('active-stepp');
            $('#line2 > p').addClass('active-stepp');
            $('.tab-1-content').removeClass('active');
            $('.tab-2-content').removeClass('active');
            $('.tab-3-content').addClass('active');
            $('.controls').html('<button class="customButton" onclick="stepDetector(\'prev\',2)" style=" width: 40%;height: 40px;border-radius: 10px;margin-left: auto;margin-right: auto">Prev Step</button><button class="customButton preSaveButton saveAppointment" style="width: 40%;height: 40px;border-radius: 10px;margin-left: auto;margin-right: auto;font-size: 18px;background-color: rebeccapurple;">Book</button>');
        }else{
            swal({
                title: "You must select an hour first!",
                type: "warning",
                showCancelButton: false,
                showConfirmButton: true,
                showConfirmButtonText: 'Ok',
                customClass: "customSwallClass"
            },function(){
                if (window.innerWidth < 768) {
                    $('html, body').animate({
                        scrollTop: $(".freeAppointmentCont").offset().top
                    }, 500);
                }
            });
        }
    }
}
function getPriceAndTimeByServices(services){
    $.ajax({
        type: "POST",
        url: "AJAXPOSTS",
        data: {
            action: "getPriceAndTimeForServices",
            services: services
        },
        success: function(response) {
            response = JSON.parse(response);
            $('.timePreview').html(response.sumTime);
            $('.pricePreview').html(response.sumPrice+' €');
            $('.loaderCont').css('display','none');
            $('#displayCont').css('display', 'flex');
        }
    });
}

function updateClock() {
    var date = $('#datePreviewStamp').text();
    var time = $('#timePreviewStamp').text();
    var durationPre = $('.timePreview').text();
    $.ajax({
        type: "POST",
        url: "AJAXPOSTS",
        data: {
            action: "reconOfDateToStartEndTime" ,
            selectedDate: date,
            selectedTime: time,
            duration: durationPre
        },
        success: function(response) {
            response = JSON.parse(response);
            $('.datePreview').html(response.fullMessage);
            //Set start and end times for rotation
            const startDate = new Date(response.startDate);
            const endDate = new Date(response.endDate);
            const startSeconds = (startDate.getSeconds() / 60) * 360;
            const startMinutes = (startDate.getMinutes() / 60) * 360;
            const startHours = ((startDate.getHours() + startDate.getMinutes() / 60) / 12) * 360;
            const endSeconds = (endDate.getSeconds() / 60) * 360;
            const endMinutes = (endDate.getMinutes() / 60) * 360;
            const endHours = ((endDate.getHours() + endDate.getMinutes() / 60) / 12) * 360;
            //Update CSS
            const root = document.documentElement;
            var duration = (endDate - startDate) / 3600000; // duration in hours
            $('.min-container').html("≈"+durationPre+"'");
            var number = 120;
            if(duration > 1){
                number += (duration - 1) * 180;
            }
            const hoursRange = '-10% -10%, 30% -10%, 50% 50%, '+number+'% -10%, 110% -10%, 110% 110%, -10% 110%';
            root.style.setProperty("--s-rotate-from", startSeconds + "deg");
            root.style.setProperty("--m-rotate-from", startMinutes + "deg");
            root.style.setProperty("--h-rotate-from", startHours + "deg");
            root.style.setProperty("--s-rotate-to", endSeconds + "deg");
            root.style.setProperty("--m-rotate-to", endMinutes + "deg");
            root.style.setProperty("--h-rotate-to", endHours + "deg");
            root.style.setProperty("--length-view", hoursRange);
            setTimeout(function(){
                $('.finalServiceContainer').css('display', 'block');
                $('.clockPreview').css('display','flex');
                $('.loaderCont').css('display','none');
            },400);
        }
    });
}

function checkBusinessHours() {
    var date = new Date();
    var currentDay = date.getDay();
    var currentTime = date.getHours() + ":" + date.getMinutes();
    var isBusinessHour = false;
    $.ajax({
        type: "POST",
        url: "AJAXPOSTS",
        data: { action: "getBusinessHours" },
        success: function(response) {
            var businessHours = JSON.parse(response);
            //loop through the business hours to find the current day
            for (var i = 0; i < businessHours.length; i++) {
                if (businessHours[i].name.toLowerCase() === getDayName(currentDay).toLowerCase() && businessHours[i].active === "1") {
                    //check if the current time is within the start and end time for the current day
                    if (currentTime >= businessHours[i].startTime && currentTime <= businessHours[i].endTime) {
                        isBusinessHour = true;
                        break;
                    }
                }
            }
            if (isBusinessHour) {
                //We're open!
                isBusinessHour = true;
            } else {
                //check if it's a business day
                if (currentDay !== 0 && currentDay !== 6) {
                    //it's a business day but not within business hours
                    isBusinessHour = true;
                } else {
                    isBusinessHour = false;
                }
            }
            return isBusinessHour;
        }
    });
}

function getDayName(day) {
    var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    return days[day];
}

function print_available_appointments(date){
    $('#freeAppointments').html('<span class="loader"></span>').css('display','flex').css('height','100%');
    let today = new Date();
    const fullDate = today.toISOString().slice(0, 10);
    const dayName = today.toLocaleString('default', { weekday: 'long' });
    if(fullDate === date){
        if(checkBusinessHours() === false){
            $('#freeAppointments').html('<div class="col-lg-12" style="margin-bottom: 30px;text-align: center;font-size: 30px;margin-top: 20px;">'+fullDate+'</div><div class="col-lg-12" style="text-align: center;font-size: 20px;">Sorry!, We are closed at '+dayName+'</div>');
            return false;
        }
    }
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {
            action: 'getRecourcesForSchedule',
            date: date,
            services: getSelectedServices()
        },
        dataType: "json",
        success: function (data) {
            var exportation = '';
            for (var i=0;i<=data.length-1;i++){
                if(data[i].free){
                    exportation += '<div class="freeAppointment" data-starttime="'+ data[i].start +'" data-endtime="'+ data[i].end +'">'+ data[i].start +' - '+ data[i].end +'</div>';
                }else{
                    exportation += '<div class="reservedAppointment">'+ data[i].start +' - '+ data[i].end +'</div>';
                }
            }
            $('#freeAppointments').html(exportation).css('display','block');
        }
    });
}

function printArray(arr) {
    var output = "";
    arr.forEach(function(item, index) {
        output += item;
        if (index !== arr.length - 1) {
            output += ", ";
        }
    });
   return output;
}

function saveAppointment(){
    $('.preSaveButton').removeClass('saveAppointment').text('Booking...');
    var services = getSelectedServices();
    var date = $('#datePreviewStamp').text();
    var time = $('#timePreviewStamp').text();
    var barberId = $('#brbid').val();
    var clientName = $('#name').val();
    var clientSurname = $('#surname').val();
    var clientEmail = $('#email').val();
    var clientPhone = $('#phone').val();
    var referralCode = $('#referralCode').val();
    $.ajax({
        type: "POST",
        url: "AJAXPOSTS",
        data: {
            action: "saveAppointment",
            services: services,
            barberId: barberId,
            date: date,
            time: time,
            clientName: clientName,
            clientSurname: clientSurname,
            clientEmail: clientEmail,
            clientPhone: clientPhone,
            referralCode: referralCode
        },
        success: function(response) {
            response = JSON.parse(response);
           if(response.response){
               var successScreen = $('#successScreen').html();
               var extraHtml = '' +
                   '<div style="width:100%;height: 100px;display: flex;">' +
                   '<div class="icon icon--order-success svg" style="margin: auto;">' +
                   '          <svg xmlns="http://www.w3.org/2000/svg" width="72px" height="72px">' +
                   '            <g fill="none" stroke="#0a9465" stroke-width="2">' +
                   '              <circle cx="36" cy="36" r="35" style="stroke-dasharray:240px, 240px; stroke-dashoffset: 480px;"></circle>' +
                   '              <path d="M17.417,37.778l9.93,9.909l25.444-25.393" style="stroke-dasharray:50px, 50px; stroke-dashoffset: 0px;"></path>' +
                   '            </g>' +
                   '          </svg>' +
                   '        </div>' +
                   '</div>' +
                   '<div class="ibox finalMessageFix"><h1 style="margin: 0px;text-align: center">Appointment Booked!</h1></div>';
               $('#successScreenCont').html(extraHtml+successScreen);
               $('.stepper').remove();
               $('.controls').remove();
               $('.removeTitle').remove();
           }else{
               if(response.errorMessage){
                   var title = response.errorMessage;
               }else{
                   var title ='Error on send appointment! please contact us';
               }
               var text = '';
               for (var j = 0; j <= response.missingFields.length-1; j++) {
                   if(response.missingFields[j] === 'clientName'){
                       text += 'Name';
                   }
                   if(response.missingFields[j] === 'clientSurname'){
                       text += 'Surname';
                   }
                   if(response.missingFields[j] === 'clientPhone'){
                       text += 'Phone';
                   }
                   if(j !== response.missingFields.length-1){
                        text += ', '
                   }
               }
               swal({
                   title: title,
                   text: text,
                   type: "error",
                   showCancelButton: false,
                   showConfirmButton: true,
                   showConfirmButtonText: 'Ok',
                   customClass: "customSwallClass"
               },(function() {
                   $('.preSaveButton').addClass('saveAppointment').text('Book');
                   if(response.missingFields){
                       $('#name').removeClass('requiredInput');
                       $('#surname').removeClass('requiredInput');
                       $('#email').removeClass('requiredInput');
                       $('#phone').removeClass('requiredInput');
                       var meter = 0;
                       for (var i = 0; i <= response.missingFields.length-1; i++) {
                           if(response.missingFields[i] === 'clientName'){
                               $('#name').addClass('requiredInput');
                               meter++;
                               setTimeout(function(){
                                   $("#name").focus();
                               },100);
                           }
                           if(response.missingFields[i] === 'clientSurname'){
                               $('#surname').addClass('requiredInput');
                               if(meter <= 0){
                                   setTimeout(function() {
                                       $("#surname").focus();
                                   },100);
                                   meter++;
                               }
                           }
                           if(response.missingFields[i] === 'clientEmail'){
                               $('#email').addClass('requiredInput');
                               if(meter <= 0){
                                   setTimeout(function() {
                                       $("#email").focus();
                                   },100);
                                   meter++;
                               }
                           }
                           if(response.missingFields[i] === 'clientPhone'){
                               $('#phone').addClass('requiredInput');
                               if(meter <= 0){
                                   setTimeout(function() {
                                       $("#phone").focus();
                                   },100);
                                   meter++;
                               }
                           }
                       }
                   }
               }));
           }
        }
    });
}