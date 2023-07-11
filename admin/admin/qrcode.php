<!DOCTYPE html>
<html>
<?php $pagename = 'QRCode'; include 'header.php';
include "phpqrcode-master/qrlib.php";
QRcode::png($setting['website_url'], 'qrcodeImage/business.png', 'L', 4, 2); ?>
<link href="css/plugins/fullcalendar/fullcalendar.css" rel="stylesheet">
<link href="css/plugins/fullcalendar/fullcalendar.print.css" rel='stylesheet' media='print'>
<link href="css/plugins/fullcalendar/fullcalendar-scheduler.min.css" rel="stylesheet">
<link href="css/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet">
<style>
    .fc .fc-day-today {
        background: none!important;
    }

</style>
<body>
<div id="wrapper">
    <?php include 'menu.php'; ?>
    <link href="css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="css/jquery-ui.min.css" rel="stylesheet">
    <link href="css/plugins/morris/morris-0.4.3.min.css" rel="stylesheet">
    <div class="wrapper wrapper-content row">
        <div style=" display: flex;width: 100%;">
            <span style="margin: auto;font-size: 30px; margin-bottom: 40px;">Scan the QRCode to Book An Appointment</span>
        </div>
        <div style=" display: flex;width: 100%;">
            <img src="qrcodeImage/business.png" style=" width: 200px; height: 200px;margin: auto;">
        </div>
    </div>


       <?php include 'footer.php';?>
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


            <script>
        if ($(window).width() < 769) {
            $('.mainCont').css('display','none');
        }
        /* $(document).ready(function() {
             load1stGraph('firstLoad',0,1);

         });*/


        document.addEventListener('DOMContentLoaded', function() {
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {action: 'getRecourcesForSchedule'},
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
                    const workSpec = [
                        {
                            daysOfWeek: [0, 1, 2, 3, 4, 5, 6],
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
                    var calendar = new Calendar(calendarEl, {
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
                        editable: false,
                        droppable: false,
                        aspectRatio: selectedAspectRatio,
                        events: data.events,
                        eventClassNames: 'takis',
                        eventClick: function (info) {
                            add_haircut('modals','edit',info.event.id);
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
                    calendar.setOption('header', {
                        right: 'viewDropdown'
                    });
                    $('.fc-viewDropdown-button').html(viewSelect).css('padding','0px');
                    viewSelect.addEventListener('change', function () {
                        calendar.changeView(this.value);
                    });
                    $('#viewDropdown').css('border','none').css('background-color','none');
                }
            });
        });

        function detectDevice() {
            if (window.innerWidth < 768) {
                return true;
            }else{
                return false;
            }
        }



        /* pairsChecker(25);
   profitsByAdmins();*/
      /* $(document).ready(function() {

           $('input[name="daterange"]').daterangepicker({
               format: 'DD/MM/YYYY',
               startDate: moment().startOf('month'),
               endDate: moment().endOf('month'),
               dateLimit: { days: 60 },
               showDropdowns: true,
               showWeekNumbers: true,
               timePicker: true,
               timePickerIncrement: 1,
               timePicker12Hour: false,
               ranges: {
                   'Today': [moment(), moment()],
                   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                   'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
               },
               opens: 'left',
               drops: 'down',
               buttonClasses: ['btn', 'btn-sm'],
               applyClass: 'btn-primary',
               cancelClass: 'btn-default',
               separator: ' to ',
               locale: {
                   applyLabel: 'Submit',
                   cancelLabel: 'Cancel',
                   fromLabel: 'From',
                   toLabel: 'To',
                   customRangeLabel: 'Custom',
                   daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
                   monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                   firstDay: 1
               }
           }, function(start, end, label) {
               $('input[name="daterange"]').html(start.format('D MMMM, YYYY') + ' - ' + end.format('D MMMM, YYYY'));
           });
           load1stGraph('firstLoad',0,1);
           loadActivePairsGraph();
           setTimeout(function(){
               gather_wallets_volume();
               getAPIStats('firstLoad');
               gather_kyt_remaining_checks();
           },1000);
           checkExchangerApiStatus('firstLoad');
           setTimeout(function() {
              /!* setInterval(function () {
                   /!*load1stGraph(); *!/
                  /!* getAPIStats();
                   checkExchangerApiStatus(); *!/
           /!*    }, 3000); *!/
               $('#dashLoad').removeClass('fadeIn').addClass('fadeOut').css('display','none');
           }, 3000);
   });*/


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
