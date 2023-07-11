<style>.sidebar-message{
        margin-top: 0px;
        padding: 15px!important;
        -webkit-transition: all 1s ease-in-out;
        -moz-transition: all 1s ease-in-out;
        -o-transition: all 1s ease-in-out;
        transition: all 1s ease-in-out;
    }

    .backBad{
        width: 100%;
        background-position: center;
        height: 93px;
        position: absolute;
        z-index: 6;
        margin: -20px;
        background-size: cover;
        opacity: 0.1;
        -webkit-transition: all 1s ease-in-out;
        -moz-transition: all 1s ease-in-out;
        -o-transition: all 1s ease-in-out;
        transition: all 1s ease-in-out;
    }


    .backBad2{
        background-color: #2d2d2d;
        padding: 5px;
        margin-top: -15px;
        border-radius: 9px;
        color: white;
    }

    .assignedDeposit{
        cursor: pointer!important;
        padding: 3px 5px 3px 10px!important;
        margin-bottom: 3px!important;
        position: absolute!important;
        right: 14px!important;
        margin-top: -5px!important;
        background-color: #1d1d1d!important;
        border-radius: 0px!important;
        border: none;important;
        color: #FFEB3B!important;
    }
    .normalSecInput{
        display:none;
    }
    .fade-scale {
        transform: scale(0);
        opacity: 0;
        -webkit-transition: all .25s linear;
        -o-transition: all .25s linear;
        transition: all .25s linear;
    }

    .fade-scale.show {
        opacity: 1;
        transform: scale(1);
    }

</style>
<div class="footer" >
    <div class="float-right">
            Made with <strong style="color:red;font-size:14px;">&hearts;</strong> For Non Profitable purpose.
    </div>
    <div>
        All Rights and Lefts reserved.
    </div>
</div>
</div>
<!-- Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content" style="background-color:var(--secondary-color)">
          <div class="modal-header">
            <div class="modal-title" id="approvalModalLabel">Appointments Awaiting Approval</div>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
          </div>
            <div class="modal-body" id="approvalModalBody"></div>
       </div>
    </div>
</div>


<script src="js/customFunctions/others.js?ver=<?=time()?>"></script>
<script src="js/plugins/chosen/chosen.jquery.js"></script>
<script src="js/customFunctions/modals.js?ver=<?=time()?>"></script>
<script src="js/plugins/sweetalert/sweetalert.min.js"></script>
<script>
    $(".count-info").click(function(){
        if (!$(this).closest("li").hasClass("show")) {
            show_notifications();
        }
    });

    check_notifications_count();

    function check_notifications_count(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {
                action: 'gatherNotificationsCount'
            },
            dataType: "json",
            success: function (data) {
                if(data.responseSos){
                    $('#approvalModalBody').html('');
                    gather_pending_sos(data.responseSos,data.businessHours,data.appointmentStep);
                    $('#approvalModal').modal('show');
                }else{
                    $('#approvalModal').modal('hide');
                    $('#approvalModalBody').html('');
                }
                var alreadyCounted = getCookie('activeNotificationNum');
                setCookie('activeNotificationNum',data.response.count,1);
                if(data.response.count > parseInt(alreadyCounted)){
                    $('#aud').html('<iframe src="tones/facebook_pop.mp3" allow="autoplay" style="display:none">');
                    if(data.action === 'updated'){
                        toastr.warning('Haircut Updated!', '<i style="font-size: 20px;padding-bottom:10px;color:white;" class="customFA fa fa-scissors" aria-hidden="true"></i> Barbreon');
                    }
                    if(data.action === 'created'){
                        toastr.info('New Haircut!', '<i style="font-size: 20px;padding-bottom:10px;color:white;" class="customFA fa fa-scissors" aria-hidden="true"></i> Barbreon');
                    }
                    if(data.action === 'deleted'){
                        toastr.warning('Haircut Deleted!', '<i style="font-size: 20px;padding-bottom:10px;color:white;" class="customFA fa fa-scissors" aria-hidden="true"></i> Barbreon');
                    }
                    calendar_render();
                    show_notifications();
                }
                $('.notifNumTransactions').text(getCookie('activeNotificationNum'));
            },
            complete: function (data) {
                setTimeout(function () {
                    check_notifications_count();
                }, 5000);
            }
        });
    }

    function show_notifications(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {
                action: 'gatherNotifications'
            },
            dataType: "json",
            success: function (data) {
                if(data.response) {
                    var result = '';
                    var resultSwaps = [];
                    if (data.response.length > 0) {
                        for (var i = 0; i <= data.response.length-1; i++) {
                            var dateTime = data.response[i].notificationDateTimeUpdated;
                            var nod = dateFormat(new Date(), 'Y-m-d H:i:s');
                            var dateFirst = new Date(dateTime);
                            var dateSecond = new Date(nod);
                            var differance = new DateDiff(dateSecond, dateFirst);
                            var resultedDifferance = '';
                            if (differance.days > 0) {
                                resultedDifferance = differance.days + 'd ' + differance.hours + 'h';
                            } else if (differance.hours > 0) {
                                resultedDifferance = differance.hours + 'h ' + differance.minutes + 'm';
                            } else if (differance.minutes > 0) {
                                resultedDifferance = differance.minutes + 'm ago';
                            } else if (differance.seconds > 0 && differance.seconds <= 60) {
                                resultedDifferance = 'Just now!';
                            }
                            var appointmentExecutionInfoExtraCss = '';
                            if(data.response[i].state){
                                if(data.response[i].state.name === 'Completed'){
                                     appointmentExecutionInfoExtraCss = 'opacity: 0.5; pointer-events: none;';
                                }
                                var appointmentExecutionInfo = data.response[i].state.icon;
                            }else{
                                var appointmentExecutionInfo = '';
                                appointmentExecutionInfoExtraCss = 'opacity: 0.5; pointer-events: none;';
                            }
                            if(data.response[i].data.active === 1){
                                if(data.response[i].state.name === 'Completed'){
                                    var procedure = 'Completed';
                                }else{
                                    var procedure = data.response[i].action.charAt(0).toUpperCase() + data.response[i].action.slice(1);
                                }
                                var colors = '#5cffb8';
                                var acceptedText = 'test';
                                var extraFunc = "add_haircut('modals','edit',"+ data.response[i].data.id +");";
                            }else{
                                var procedure = 'Deleted';
                                var colors = '#ff5332';
                                var acceptedText = '';
                                var extraFunc = '';
                            }
                            var isSeen = '';
                            if(data.response[i].seen !== true){
                                var isSeen = 'background-color: #0c865666;';
                            }
                            resultSwaps.push(['' +
                            '<li>' +
                                '<a style="'+isSeen+appointmentExecutionInfoExtraCss+'padding: 0px;cursor:pointer!important;" href="javascript:void(0)" onclick="'+ extraFunc +'"><div style="min-height: 50px;" class="dropdown-messages-box">' +
                                '<div style="min-height: 60px;border: 2px solid #141c28;padding-top: 5px;padding-left: 5px;padding-right: 10px;">' +
                                    '<div style="width: 10%;float: left;height:55px;display: flex">'+ appointmentExecutionInfo +'</div>' +
                                    '<div style="width: 30%;float: left;height:55px;">' +
                                        '<div class="flex-center">' +
                                            '<i style="font-size: 20px;padding-bottom:5px;color: '+ colors +'" class="customFA fa fa-scissors" aria-hidden="true"></i>' +
                                        '</div>' +
                                        '<div class="flex-center"> ' + procedure + '</div>' +
                                    '</div>' +
                                    '<div style="width: 60%;float: left;height:55px;">' +
                                        '<small class="float-right" style="padding-top: 5%;">' + resultedDifferance + '</small>' +
                                        ' New Appointment. <br><small class="text-muted">' + data.response[i].data.patient.name + '</small>' +
                                    '</div>' +
                                '</div>' +
                            '</li>', dateTime]);
                        }
                    } else {
                        resultSwaps = resultSwaps + '<li style="text-align: -webkit-center;padding: 20px;font-weight: 600;">No new Notifications!</li>';
                    }
                    var sumResultShorted = resultSwaps.concat(resultSwaps);
                    for (var f = 0; f <= data.response.length - 1; f++) {
                        result = result + sumResultShorted[f][0];
                    }
                    $('.dropdown-menu-transactions').html(result);
                }
            }
        });
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for(var i = 0; i <ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    function checkCookie(cookName,cookValue) {
        var username = getCookie(cookName);
        if (username === "undefined" || typeof username === undefined || username === "" || username === null) {
            setCookie(cookName, cookValue, 1);
        } else {
            if (username === cookValue){
                return true;
            }else{
                setCookie(cookName, cookValue, 1);
            }
        }
    }

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function deleteCookie(name) {
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }







    function gather_pending_sos(response,businessHours,appointmentStep){
        if(response){
            var exportedHtml = '';
            exportedHtml = ' ';
            const staticContainers = document.getElementsByClassName('gray-bg');
            const numCategories = response.length;
            const elementWidth = staticContainers[0].offsetWidth - 50;
            var categoryWidth = $(window).width() > 999 ? 0 : (elementWidth / numCategories);
            var tabsHtml = '';
            var tabButtonsHtml = '';
            for (var i = 0; i < response.length ; i++) {
                var isActive = '';
                if (i === 0) {
                    isActive = 'active';
                }
                if(response.length > 1){
                    tabButtonsHtml += '<li style="width:' + categoryWidth + 'px;overflow: hidden;text-align: center;min-width: fit-content;"><a class="nav-link ' + isActive + '" data-toggle="tab" href="#categoryTab-' + i + '">' + response[i].date + '</a></li>';
                }
                tabsHtml += '<div class="tab-pane tab-' + [i] + '-content ' + isActive + '" id="categoryTab-' + i + '">';
                tabsHtml += '<div class="appointment"><div id="calendarRender_'+ response[i].date +'_sos"></div>';
                response[i].data.forEach(function(appointment) {
                    if(appointment.newApp) {
                        tabsHtml += '<div class="row sosAppControls"><div class="col-lg-9" style="padding: 0px;">';
                        if (appointment.clientNote && appointment.clientNote !== '') {
                          var  clientMessage = '<div class="col-lg-12" style="min-height: 40px;display: flex;">' +
                              '<div id="toast-container" class="toast-top-right col-lg-12" aria-live="polite" role="alert" style="z-index: 0;position: relative;top: 0px;">' +
                                '<div class="toast toast-info toastrCustom">' +
                                '<div class="toast-message">'+ appointment.clientNote +'</div>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                        } else {
                            var clientMessage = '';

                        }
                        tabsHtml += '<div class="col-lg-12">'+ appointment.title +'</div>';
                        tabsHtml += '<div class="col-lg-12">'+ appointment.start +'</div>';
                        tabsHtml += '<div class="col-lg-12">'+ appointment.services +'</div>';
                        tabsHtml += clientMessage +
                            '                    <div class="col-lg-12" style="padding: 0px;"><textarea id="txt_area_sos_'+ appointment.id +'" class="form-control" placeholder="Write down message for client" style="color:white;"></textarea></div>';
                        tabsHtml += '' +
                            '        <div class="col-lg-3" style="margin: auto;">' +
                            '        <div class="col-lg-12" style="margin: auto;font-size: 31px;margin-bottom: 20px;">'+ appointment.price +'€</div>' +
                            '               <button class="btn btn-success approve-btn" data-sosid="'+ appointment.id +'" style="width: 100%;margin-top: 10px;margin-bottom: 10px;">Approve</button>' +
                            '                    <button class="btn btn-danger decline-btn" data-sosid="'+ appointment.id +'" style="width:100%;">Decline</button>' +
                            '        </div>' +
                            '       </div>' +
                            '</div>';
                    }
                });
                tabsHtml += '</div>';
                tabsHtml += '</div>';
            }
            if(response.length >= 1){
                if ($(window).width() <= 999) {
                    tabButtonsHtml += '<div class="scroll-hint">' +
                        '             <span class="arrow">&#8594;</span>' +
                        '         </div>';
                }
                exportedHtml +=     '   <div class="tabs-container">' +
                    '                           <ul class="nav nav-tabs">' + tabButtonsHtml + '</ul>'+
                    '                           <div class="ibox-content tab-content" style="display: flex;padding: 0px;padding-top: 20px;">' + tabsHtml +'</div>' +
                    '                       </div>';
            }

            var oldSize = localStorage.getItem('sosLocalStorage');
            var newSize = response.length;
            if(parseInt(oldSize) !== parseInt(newSize)){
                localStorage.setItem('sosLocalStorage', newSize);
                $('#approvalModalBody').html(exportedHtml);
                setTimeout(function(){
                    render_ready_data_calendar_by_element_id('calendarRender_'+ response[0].date  +'_sos', response[0].data,businessHours,appointmentStep,response[0].date);
                },500);
                for (var i = 0; i < response.length ; i++) {
                    (function(i) {
                        $('a[href="#categoryTab-' + i + '"]').on('shown.bs.tab', function (e) {
                            render_ready_data_calendar_by_element_id('calendarRender_'+ response[i].date  +'_sos', response[i].data,businessHours,appointmentStep,response[i].date);
                        });
                    })(i);
                }

                $('.approve-btn').click(function() {
                    var sosid = $(this).data('sosid');
                    console.log('approve butt');
                    $.ajax({
                        url: "AJAXPOSTS",
                        type: "POST",
                        data: {
                            action: 'acceptSos',
                            sosId: sosid,
                            message: $('#txt_area_sos_'+sosid).val()
                        },
                        dataType: "json",
                        success: function(data) {
                            check_notifications_count();
                        }
                    });
                });
                $('.decline-btn').click(function() {
                    var sosid = $(this).data('sosid');
                    console.log('dec butt');
                    $.ajax({
                        url: "AJAXPOSTS",
                        type: "POST",
                        data: {
                            action: 'declineSos',
                            sosId: sosid,
                            message: $('#txt_area_sos_'+sosid).val()
                        },
                        dataType: "json",
                        success: function(data) {
                            check_notifications_count();
                        }
                    });
                });

                var scrollHint = $('.scroll-hint');
                var scrollContainer = $('.nav.nav-tabs');

                scrollContainer.on('scroll', function() {
                    if (scrollContainer.scrollLeft() > 0) {
                        scrollHint.addClass('active');
                    } else {
                        scrollHint.removeClass('active');
                    }
                });
            }
        }
    }

    function render_ready_data_calendar_by_element_id(elementId,events,businessHours,appointmentStep,selectedDate){
        var calendar;
        var isMobile = detectDevice();
        var selectedAspectRatio = 0.5;
        var initialView = 'timeGridDay';
        var workSpec = businessHours.map(function(hour) {
            var daysOfWeek = [];
            switch(hour.name) {
                case 'Δευτέρα':
                    daysOfWeek.push(1);
                    break;
                case 'Τρίτη':
                    daysOfWeek.push(2);
                    break;
                case 'Τετάρτη':
                    daysOfWeek.push(3);
                    break;
                case 'Πέμπτη':
                    daysOfWeek.push(4);
                    break;
                case 'Παρασκευή':
                    daysOfWeek.push(5);
                    break;
                case 'Σάββατο':
                    daysOfWeek.push(6);
                    break;
                case 'Κυριακή':
                    daysOfWeek.push(0);
                    break;
            }
            return {
                daysOfWeek: daysOfWeek,
                startTime: hour.startTime,
                endTime: hour.endTime
            };
        });
        var workMin = workSpec.map(item => item.startTime).sort().shift();
        var workMax = workSpec.map(item => item.endTime).sort().pop();
        var workDays = [...new Set(workSpec.flatMap(item => item.daysOfWeek))];
        var hideDays = [...Array(7).keys()].filter(day => !workDays.includes(day));
        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(elementId);
        var halfAppointmentStep = parseInt(appointmentStep) / 2;
        var totalMinutes = halfAppointmentStep > 10 ? halfAppointmentStep - 10 : halfAppointmentStep;
        var slotDuration = '00:' + (totalMinutes < 10 ? '0' : '') + totalMinutes + ':00';

        calendar = new Calendar(calendarEl, {
            locale: 'el',
            slotDuration: slotDuration,
            initialDate: selectedDate,
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            nowIndicator: true,
            headerToolbar: {
                right: '',
                center: 'title',
                left: ''
            },
            firstDay: 1,
            businessHours: workSpec,
            slotMinTime: workMin,
            slotMaxTime: workMax,
            hiddenDays: hideDays,
            views: {
                timeGridDay: {
                    buttonText: 'Daily View',
                    allDaySlot: false,
                },
            },
            initialView: initialView,
            contentHeight: 'auto',
            editable: true,
            droppable: false,
            aspectRatio: selectedAspectRatio,
            events: events,
            eventClassNames: 'takis',
            eventDrop: function(info) {
                function toTimeZone(date, timeZone) {
                    var formatOptions = {
                        timeZone: timeZone,
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    };

                    var formatter = new Intl.DateTimeFormat('en-GB', formatOptions);
                    return formatter.format(date).replace(',', '').replace(/\//g, '-');
                }
                var date = new Date(info.event.start);
                var timeZone = 'Europe/Athens';
                var formattedDate = toTimeZone(date, timeZone);
                var id = info.event.id;
               /* update_date_haircut(formattedDate, id); //here wants update sos*/
            },
            eventResize: function(info) {
                info.revert();
            },
            /*eventClick: function (info) {
                add_haircut('modals','edit',info.event.id);
            },*/
        });
        calendar.render();
    }

</script>

