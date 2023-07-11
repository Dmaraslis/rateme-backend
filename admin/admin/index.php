<!DOCTYPE html>
<html>
<?php $pagename = 'Overview'; include 'header.php';
$settings->gather_stats_for_graph_ZTD(0);
//$calendarific->save_holidays_to_json(); // den tha prepei na kanw polla push mesa ston mina giati tha midenizontai ta requests.
$clientsCount = $user->countCustomers();
$clientsBarbersCount = $barbers->count_barbers();
$hairCutsCount = $haircuts->count_haircuts();
$monthlyHaircutsAvTime = $haircuts->monthly_execution_time_average('annual');
$dailyHaircutsAvTime = $haircuts->monthly_execution_time_average('daily');
if($setting['discountSystem'] > 0) {
    $upcommingDiscounts = $haircuts->get_most_frequent_customer_id(3);
} ?>
<link href="css/plugins/fullcalendar/fullcalendar.css" rel="stylesheet">
<link href="css/plugins/fullcalendar/fullcalendar.print.css" rel='stylesheet' media='print'>
<link href="css/plugins/fullcalendar/fullcalendar-scheduler.min.css" rel="stylesheet">
<link href="css/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet">
<link href="css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">

<style>
    .fc .fc-day-today {
        background: none!important;
    }

    #calendar-loader {
        position: absolute;
        width: 100%;
        height: 100%;
        display: none;
        z-index: 9;
        padding: 0px;
        margin: 0px;
        top: 0px;
        left: 0px;
        padding-bottom: 25px;
    }

    .background-loader{
        background-color: #2d2d2d;
        border-radius: 10px;
        width: 100%;
        display: flex;
    }

</style>
<body>
<div id="wrapper">
    <?php include 'menu.php'; ?>
    <link href="css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="css/jquery-ui.min.css" rel="stylesheet">
    <link href="css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">
    <div class="wrapper wrapper-content row">
        <div class="col-lg-12 customCFix" style="padding-left: 0px;">
            <div class="ibox">
                <div class="ibox-title" style="padding: 0px; margin: 0px; min-height: 10px;"></div>
                <div class="ibox-content cal">
                    <div id="calendar-container">
                        <div id="calendar-loader">
                            <div class="background-loader">
                                <div class="loader"></div>
                            </div>
                        </div>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if($setting['discountSystem'] > 0){  ?>
            <div class="col-lg-4" style="    padding: 0px;">
                <div class="ibox custom-collapsed">
                    <div class="ibox-title">
                        <h5>Recent appointments</h5>
                    </div>
                    <div class="ibox-tools" style="top: 10px;">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-down" style="font-size: 25px;"></i>
                        </a>
                    </div>
                    <div class="ibox-content" style="    overflow: auto;">
                        <div class="recentHaircuts"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 customCFix upcomingDiscountsPannel">
            <div class="ibox custom-collapsed">
                <div class="ibox-title">
                    <h5>Discounts Systems</h5>
                </div>
                <div class="ibox-tools" style="top: 10px;right:30px;">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-down" style="font-size: 25px;"></i>
                    </a>
                </div>
                <div class="ibox-content">
                    <div class="row">

                        <div class="col-md-9 ">
                            <div class="chat-discussion">
                                <?php
                                foreach ($upcommingDiscounts['fourthAppointment'] as $client =>$Value){ ?>
                                    <div class="chat-message left">
                                        <div style="width: 50px;">
                                            <div style="height:50px;">
                                                <img class="message-avatar" src="images/customer.png" alt="" >
                                            </div>
                                            <div class="notifMessageButt customModal-shop-dam saveNotifyButt" style="width: 45px;height: 60px;text-align: center;border: 1px solid #0c8656;font-size: 12px;border-radius: 10px;">
                                                <i style="font-size: 40px;" class="customFA fa fa-mobile" aria-hidden="true"></i>
                                                SMS
                                            </div>
                                        </div>
                                        <div class="message" style="min-height: 120px;margin-top: -120px;">
                                            <a class="message-author" href="#"> <?=$upcommingDiscounts['fourthAppointment'][$client]['customerInfos']['name']?> <?=$upcommingDiscounts['fourthAppointment'][$client]['customerInfos']['surname']?> (<?=$upcommingDiscounts['fourthAppointment'][$client]['appointmentCount']?>°)</a>
                                            <span class="message-date">  <?php if($upcommingDiscounts[$client]['nextAppointment']['dateTimeExecuted']){ echo " Next haircut on: ".$upcommingDiscounts[$client]['nextAppointment']['dateTimeExecuted']; }?></span>
                                            <span class="message-content">
                                                    <div>
                                                        <?=$upcommingDiscounts['fourthAppointment'][$client]['name']?> <?=$upcommingDiscounts['fourthAppointment'][$client]['surname']?> Εγώ είμαι πάλι! Ήθελα απλώς να σας ενημερώσω ότι έφτασα στο τέταρτο ραντεβού μου μαζί μου - πόσο φοβερό είναι αυτό; 🎉 Ως ένας τρόπος για να σ' ευχαριστήσω, θα έχεις μια ειδική έκπτωση στην επόμενη επίσκεψή σου.
                                                    </div>
                                                      <div style="float:right;width: auto;padding: 10px;min-height: 30px;height: auto; color: aquamarine" class=" saveNotifyButt" data-appointmentid="655">
                                                         <?php
                                                         if($upcommingDiscounts['otherAppointments'][$client]['nextAppointment']['dateTimeExecuted']){ ?>
                                                             <small style="color:aquamarine;">Next haircut on <?=$upcommingDiscounts['otherAppointments'][$client]['nextAppointment']['dateTimeExecuted']?></small>
                                                         <?php } ?>
                                                      </div>
                                                </span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-md-3 impres">
                            <div class="chat-users">
                                <div class="users-list">
                                    <?php foreach ($upcommingDiscounts['otherAppointments'] as $client =>$Value){?>
                                        <div class="chat-user">
                                            <a class="message-author" href="#"> <?=$upcommingDiscounts['otherAppointments'][$client]['customerInfos']['name']?> <?=$upcommingDiscounts['otherAppointments'][$client]['customerInfos']['surname']?> (<?=$upcommingDiscounts['otherAppointments'][$client]['appointmentCount']?>°)</a>
                                            <?php if($upcommingDiscounts['otherAppointments'][$client]['nextAppointment']['dateTimeExecuted']){ ?>
                                                <span class="float-right label  customButton" onclick="load_appointment('<?=$upcommingDiscounts['otherAppointments'][$client]['nextAppointment']['id']?>');" style="border-radius: 7px;height: auto;font-size: 13px;padding: 8px;text-align: center;margin-top: 10px;"> Edit Haircut</span>
                                            <?php } ?>
                                            <img class="chat-avatar" src="images/customer.png" alt=""  style="border-radius: 30px;">
                                            <div class="chat-user-name">
                                                <?php
                                                if($upcommingDiscounts['otherAppointments'][$client]['nextAppointment']['dateTimeExecuted']){ ?>
                                                    <small style="color:aquamarine;">Next haircut on <?=$upcommingDiscounts['otherAppointments'][$client]['nextAppointment']['dateTimeExecuted']?></small>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php }else{ ?>
            <div class="col-lg-12" style="    padding: 0px;">
                <div class="ibox custom-collapsed">
                    <div class="ibox-title">
                        <h5>Recent appointments</h5>
                    </div>
                    <div class="ibox-tools" style="top: 10px;right:30px;">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-down" style="font-size: 25px;"></i>
                        </a>
                    </div>
                    <div class="ibox-content" style="    overflow: auto;">
                        <div class="recentHaircuts"></div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="col-lg-12">
            <div class="ibox custom-collapsed" id="firstGraphInfos">
                <div class="ibox-title">
                    <h5>Statistics</h5>
                </div>
                <div class="ibox-tools ext" style="top: 10px;">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-down" style="font-size: 25px;"></i>
                    </a>
                </div>
                <div class="ibox-content">
                    <div id="graphSpinner">
                        <div class="loader"></div>
                    </div>
                    <div class="row">
                        <div class="graphHandleButtonsMobile">
                            <button id="graphPreviousMonth" class="btn btn-success btn-lg" style="    margin-left: 30px;cursor:pointer;float:left" onclick="changeMonthGraph('prev');"> <i class="fa fa-arrow-left"></i> Previous Month</button>
                            <div id="MonthDisplay" style="height: 40px;position: absolute;top: 70px;left: 100px;z-index: 99;color: #0dda92;font-size: 20px;">Preview Range: </div>
                            <button id="graphNextMonth" class="btn btn-success btn-lg" style="    margin-right: 30px;cursor:pointer;float:right" onclick="changeMonthGraph('next');">Next Month <i class="fa fa-arrow-right"></i></button>
                        </div>
                        <div class="col-lg-12" style="margin-bottom:10px;">
                            <div class="row">
                                <div class="col-lg-3 col-sm-12">
                                    <div class="col-lg-8 col-sm-12" style="margin: auto;">
                                        <div id="morris-donut-chart" ></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 customMobileFunc" style="margin-top: auto;margin-bottom: auto;">
                                    <div class="ibox impres " style="margin-bottom: 10px;">
                                        <div class="ibox-title" style="    background-color: #1d1d1d!important;padding: 10px;">
                                            <h5>Hair cut execution time</h5>
                                        </div>
                                        <div class="ibox-content contentCustom" style="    background-color: #1d1d1d!important;padding: 0px 10px 15px 10px;min-height:100px;">
                                            <div style="float:left;margin-top:20px;">
                                                <h1 class="no-margins standardChecks"><?=$dailyHaircutsAvTime?> <small style="font-size: 13px;">min</small></h1>
                                                <small class="label label-success float-right" style="background-color: #0dda92;">Daily</small>
                                            </div>
                                            <div style="float:right;margin-top:20px;">
                                                <h1 class="no-margins"><?=$monthlyHaircutsAvTime?> <small style="font-size: 13px;">min</small></h1>
                                                <small class="label label-success float-right" style="background-color: #0dda92;">Annual</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 customMobileFunc" style="margin-top: auto;margin-bottom: auto;">
                                    <div class="ibox impres" style="    margin-bottom: 10px;">
                                        <div class="ibox-title" style="    background-color: #1d1d1d!important;padding: 10px;">
                                            <span class="label label-success float-right" style="background-color: #0dda92;">Annual</span>
                                            <h5>Sum Hair Cuts</h5>
                                        </div>
                                        <div class="ibox-content" style="    background-color: #1d1d1d!important;min-height: 100px;padding: 0px 10px 15px 10px;">
                                            <h1 class="no-margins"><?=$hairCutsCount?></h1>
                                            <small>Hair Cuts</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 customMobileFunc" style="margin-top: auto;margin-bottom: auto;">
                                    <div class="ibox impres" style="    margin-bottom: 10px;">
                                        <div class="ibox-title" style="    background-color: #1d1d1d!important;padding: 10px;">
                                            <h5>Barbers</h5>
                                        </div>
                                        <div class="ibox-content" style="    background-color: #1d1d1d!important;    min-height: 100px;padding: 0px 10px 15px 10px;">
                                            <h1 class="no-margins"><?=$clientsBarbersCount?></h1>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 customMobileFunc" style="margin-top: auto;margin-bottom: auto;">
                                    <div class="ibox impres" style="    margin-bottom: 10px;">
                                        <div class="ibox-title" style="    background-color: #1d1d1d!important;padding: 10px;">
                                            <h5>Clients</h5>
                                        </div>
                                        <div class="ibox-content" style="    background-color: #1d1d1d!important;    min-height: 100px;padding: 0px 10px 15px 10px;">
                                            <h1 class="no-margins"><?=$clientsCount?></h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-9 ">
                            <div class="graphHandleButtonsPc">
                                <button id="graphPreviousMonth" class="btn btn-success btn-lg" style="    margin-left: 30px;cursor:pointer;float:left" onclick="changeMonthGraph('prev');"> <i class="fa fa-arrow-left"></i> Previous Month</button>
                                <div id="MonthDisplay" style="height: 40px;position: absolute;top: 70px;left: 100px;z-index: 99;color: #0dda92;font-size: 20px;">Preview Range: </div>
                                <button id="graphNextMonth" class="btn btn-success btn-lg" style="    margin-right: 30px;cursor:pointer;float:right" onclick="changeMonthGraph('next');">Next Month <i class="fa fa-arrow-right"></i></button>
                            </div>
                            <div class="flot-chart">
                                <div class="flot-chart-content" id="flot-dashboard-chart"></div>
                            </div>
                        </div>
                            <div class="col-lg-3" style="margin-top:40px;">
                            <ul class="stat-list">
                                <li>
                                    <h2 class="no-margins dailyEurProf prehiddenInput normalInput">0</h2>
                                    <h2 class="no-margins hiddenInput normalSecInput">* * * * * *.* *</h2>
                                    <small>Daily Profit</small>
                                </li>
                                <li>
                                    <h2 class="no-margins monthlyAvEurProf prehiddenInput normalInput">0</h2>
                                    <h2 class="no-margins hiddenInput normalSecInput">* * * * * *.* *</h2>
                                    <small>Average Profit (per hour)</small>
                                    <div class="stat-percent monthlyAvEurProfPrev">0%</div>
                                    <div class="progress progress-mini">
                                        <div style="width: 0%;" class="progress-bar monthlyAvEurProfPrevBar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins annualEurProf prehiddenInput normalInput">0</h2>
                                    <h2 class="no-margins hiddenInput normalSecInput">* * * * * *.* *</h2>
                                    <small>Monthly Profit</small>
                                    <div class="stat-percent annualEurProfPrev">0%</div>
                                    <div class="progress progress-mini">
                                        <div style="width: 0%;" class="progress-bar annualEurProfPrevBar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins totalMonthlyOrders prehiddenInput normalInput">0</h2>
                                    <h2 class="no-margins hiddenInput normalSecInput">* * * * *</h2>
                                    <small>Total Monthly Haircuts</small>
                                    <div class="stat-percent totalMonthlyOrdersByPrev">0%</div>
                                    <div class="progress progress-mini">
                                        <div style="width: 0%;" class="progress-bar totalMonthlyOrdersByPrevBar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins maxOrdersOnOneDay prehiddenInput normalInput">0</h2>
                                    <h2 class="no-margins hiddenInput normalSecInput">* * * * * </h2>

                                    <small>Max Haircuts on one day</small>
                                    <div class="stat-percent maxOrdersOnOneDayByPrev">0%</div>
                                    <div class="progress progress-mini">
                                        <div style="width: 0%;" class="progress-bar maxOrdersOnOneDayByPrevBar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins annualTimeSpent prehiddenInput normalInput">0</h2>
                                    <h2 class="no-margins hiddenInput normalSecInput">* * * * * *.* *</h2>
                                    <small>Max Appointment Time Spent</small>
                                    <div class="stat-percent annualTimeSpentPrev">0%</div>
                                    <div class="progress progress-mini">
                                        <div style="width: 0%;" class="progress-bar annualTimeSpentPrevBar"></div>
                                    </div>
                                </li>
                                <li>
                                    <h2 class="no-margins totalOrders prehiddenInput normalInput">0</h2>
                                    <h2 class="no-margins hiddenInput normalSecInput">* * * * * *</h2>

                                    <small>Total Completed Haircuts Goal (1k)</small>
                                    <div class="stat-percent totalOrdersByGoal">0%</div>
                                    <div class="progress progress-mini">
                                        <div style="width: 0%;" class="progress-bar totalOrdersByGoalBar"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

       <?php include 'footer.php';?>
      <div id="graphMonthRange"></div>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="js/plugins/flot/jquery.flot.js"></script>
<script src="js/plugins/flot/jquery.flot.tooltip.min.js"></script>
<script src="js/plugins/flot/jquery.flot.spline.js"></script>
<script src="js/plugins/flot/jquery.flot.resize.js"></script>
<script src="js/plugins/flot/jquery.flot.symbol.js"></script>
<script src="js/plugins/flot/jquery.flot.time.js"></script>
<script src="js/inspinia.js"></script>
<script src="js/plugins/pace/pace.min.js"></script>
<script src="js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="js/plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="js/plugins/c3/c3.min.js"></script>
<script src="js/plugins/d3/d3.min.js"></script>
<script src="js/plugins/fullcalendar/moment.min.js"></script>
<script src="js/plugins/daterangepicker/daterangepicker.js"></script>
<script src="js/plugins/daterangepicker/daterangepicker.js"></script>
<script src="js/plugins/fullcalendar/fullcalendar-scheduler.min.js"></script>
<script src="js/plugins/fullcalendar/moment.min.js"></script>
<!-- Morris -->
<script src="js/plugins/morris/raphael-2.1.0.min.js"></script>
<script src="js/plugins/morris/morris.js"></script>
<style>
    tspan {
       fill:white;
    }
</style>
    <script>
        localStorage.clear('sosLocalStorage');
    if ($(window).width() < 769) {
        $('.mainCont').css('display','none');
    }
     $(document).ready(function() {
         calendar_render();
         setTimeout(function(){
             gather_settings_buttons_states();
             setTimeout(function(){
                 load1stGraph('firstLoad',0,1);
                 setTimeout(function(){
                     gather_recent_appointments(18);
                     gather_statistics_donut();
                 },900);
             },700);
         },500);
     });

    var calendar;
    function calendar_render(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {
                action: 'getRecourcesForSchedule',
                from: '',
                to: ''
            },
            dataType: "json",
            success: function (data) {
                var isMobile = detectDevice();
                if(isMobile){
                    var selectedAspectRatio = 0.5;
                    var initialView = 'timeGridDay';
                }else{
                    var selectedAspectRatio = 3;
                    var initialView = 'timeGridWeek';
                }
                var businessHours = data.businessHours;

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
                var calendarEl = document.getElementById('calendar');

                var halfAppointmentStep = parseInt(data.appointmentStep) / 2;
                var totalMinutes = halfAppointmentStep > 10 ? halfAppointmentStep - 10 : halfAppointmentStep;
                var slotDuration = '00:' + (totalMinutes < 10 ? '0' : '') + totalMinutes + ':00';

                calendar = new Calendar(calendarEl, {
                    locale: 'el',
                    slotDuration: slotDuration,
                    schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                    nowIndicator: true,
                    headerToolbar: {
                        right: 'today,prev,next,viewDropdown',
                        center: '',
                        left: 'title'
                    },
                    firstDay: 1,
                    businessHours: workSpec,
                    slotMinTime: workMin,
                    slotMaxTime: workMax,
                    hiddenDays: hideDays,
                    views: {
                        dayGridMonth: { buttonText: 'Monthly View' },
                        timeGridWeek: {
                            buttonText: 'Weekly View',
                            allDaySlot: false
                        },
                        timeGridDay: {
                            buttonText: 'Daily View',
                            allDaySlot: false,
                        },
                        listWeek: { buttonText: 'List View' },
                    },
                    initialView: initialView,
                    contentHeight: 'auto',
                    editable: true,
                    droppable: false,
                    aspectRatio: selectedAspectRatio,
                    events: data.response.events,
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
                        update_date_haircut(formattedDate, id);
                    },
                    eventResize: function(info) {
                        info.revert();
                    },
                    eventClick: function (info) {
                        add_haircut('modals','edit',info.event.id);
                    },
                    dateClick: function(info){
                        var date = new Date(info.dateStr);
                        var timezoneOffset = date.getTimezoneOffset();
                        if (timezoneOffset === -180) {
                            date.setHours(date.getHours() + 3);
                        } else if (timezoneOffset === -120) {
                            date.setHours(date.getHours() + 2);
                        } else {
                            date.setHours(date.getHours() + 1);
                        }
                        var newDateString = date.toISOString().substring(0, 16);
                        add_haircut('modals','add','',newDateString);
                    },
                });
                calendar.render();
                var viewSelect = document.createElement('select');
                viewSelect.id = 'viewDropdown';
                viewSelect.className = 'fc-button-primary';
                var listOption = document.createElement('option');
                listOption.value = 'listWeek';
                listOption.innerHTML = 'List View';

                var weekOption = document.createElement('option');
                weekOption.value = 'timeGridWeek';
                weekOption.innerHTML = 'Weekly View';
                if(!isMobile){
                    weekOption.selected = true;
                }
                var monthOption = document.createElement('option');
                monthOption.value = 'dayGridMonth';
                monthOption.innerHTML = 'Monthly View';
                var dayOption = document.createElement('option');
                dayOption.value = 'timeGridDay';
                dayOption.innerHTML = 'Daily View';
                if(isMobile){
                    dayOption.selected = true;
                }
                viewSelect.appendChild(listOption);
                viewSelect.appendChild(weekOption);
                viewSelect.appendChild(monthOption);
                viewSelect.appendChild(dayOption);
                $('.fc-viewDropdown-button').html(viewSelect).css('padding','0px');
                viewSelect.addEventListener('change', function () {
                    calendar.changeView(this.value);
                });
                var viewDropdown = document.getElementById('viewDropdown');
                viewDropdown.addEventListener('change', addEventsToCalendar);
                var todayButton = document.querySelector('.fc-today-button');
                todayButton.addEventListener('click', addEventsToCalendar);
                var prevButton = document.querySelector('.fc-prev-button');
                prevButton.addEventListener('click', addEventsToCalendar);
                var nextButton = document.querySelector('.fc-next-button');
                nextButton.addEventListener('click', addEventsToCalendar);
                $('#viewDropdown').css('border','none').css('background-color','none');
            }
        });
    }
    var requestedDates = {};
    function addEventsToCalendar() {
        showLoader();
        if (!calendar) {
            console.error('FullCalendar instance not found');
            return;
        }
        /* Get the current view's start and end dates from the FullCalendar instance */
        var view = calendar.view;
        var start = view.activeStart;
        var end = view.activeEnd;
        /* Subtract one day from the end date using the subtract method from moment.js */
        start.setDate(start.getDate() + 1);
        end.setDate(end.getDate());
        /* Convert the start and end dates to the desired format (Y-m-d) */
        var from = start.toISOString().split('T')[0];
        var to = end.toISOString().split('T')[0];
        /* Perform the AJAX call to fetch event data */

        /* Check if the requested dates have already been cached */
        if (requestedDates[from] && requestedDates[from] === to) {
            hideLoader();
            return;
        }
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {
                action: 'getRecourcesForSchedule',
                from: from,
                to: to
            },
            dataType: "json",
            success: function(data) {
                /* Check if events already exist in the calendar */
                var existingEvents = calendar.getEvents();
                /* Loop through the fetched events and add them to the calendar if they are not already present */
                if(data.response.events){
                    data.response.events.forEach(function(event) {
                        var isEventExisting = existingEvents.some(function(existingEvent) {
                            return existingEvent.id === event.id;
                        });
                        if (!isEventExisting) {
                            calendar.addEvent(event);
                        }
                    });
                }
                requestedDates[from] = to;
                hideLoader();
            },
            error: function() {
                /* Handle the error case if the AJAX call fails */
                console.log("Failed to fetch event data.");
                hideLoader();
            }
        });
    }

    function showLoader() {
        $('#calendar-loader').css('display','flex');
    }

    function hideLoader() {
        $('#calendar-loader').css('display','none');
    }

    function gather_statistics_donut(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'gather_hours_stats'},
            dataType: "json",
            success: function (data) {
                if(data.noClientHours < 0){ data.noClientHours = 0; }
                if(data.busyHours < 0){ data.busyHours = 0; }
                if(data.workedHours < 0){ data.workedHours = 0; }
                Morris.Donut({
                    element: 'morris-donut-chart',
                    data: [{ label: "No Client Hours", value: data.noClientHours },
                        { label: "Busy Hours", value: data.busyHours },
                        { label: "Worked Hours", value: data.workedHours } ],
                    resize: true,
                    colors: ['#d69709', '#1d1d1d','#138f63'],
                });
            }
        });
    }
</script>
</body>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js')
            .then(function(reg){
            }).catch(function(err) {
        });
    }
</script>
</html>
