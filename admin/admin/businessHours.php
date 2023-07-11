<!DOCTYPE html>
<html>
<?php
require_once('./include/admin-load.php');
$pagename = 'Business Hours';

include 'header.php'; ?>
<link href="css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<!-- Ladda -->
<link href="css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

<style>
    .form-control:focus {
        color: #38aa72!important;
    }

    .container {
        width: 80%;
        margin: auto;
    }

    h2 {
        text-align: center;
    }

    .dayContainer {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .dayContainer input[type="time"] {
        margin-right: 10px;
    }

</style>
<body>
<div id="wrapper">
    <?php include 'menu.php'; ?>
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-10">
            <h2><?=$pagename?></h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index">Home</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong><span style='color:#a9ff7d'><?=$pagename?></span></strong>
                </li>
            </ol>
        </div>
        <div class="col-lg-2"></div>
    </div>
    <div class="wrapper wrapper-content  animated fadeInRight">
        <div class="row">
            <div class="flex-center">
                <div class="col-lg-6">
                    <div class="ibox selected">
                        <div class="ibox-content" id="userInfoResults" style="height: auto;">
                            <h2>Business Hours</h2>
                            <div id="businessHoursContainer"></div>
                            <button id="saveChanges" class="btn btn-lg customButton" style="border-radius: 0px;width: 100%;height:40px;">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</div>
<!-- Mainly scripts -->
<script src="js/jquery-3.1.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<!-- Custom and plugin javascript -->
<script src="js/inspinia.js"></script>
<script src="js/plugins/pace/pace.min.js"></script>
<script src="js/plugins/toastr/toastr.min.js"></script>
<!-- Data picker -->
<script src="js/plugins/datapicker/bootstrap-datepicker.js"></script>
<!-- Ladda -->
<script src="js/plugins/ladda/spin.min.js"></script>
<script src="js/plugins/ladda/ladda.min.js"></script>
<script src="js/plugins/ladda/ladda.jquery.min.js"></script>
<!-- FooTable -->
<script src="js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="js/plugins/summernote/summernote-bs4.js"></script>
<!-- Page-Level Scripts -->
<script>

    function generateHours(hours, sosHours, containerId) {
        var container = $('#' + containerId);
        $.each(hours, function(index, hour) {
            var sosHour = sosHours[index];

            var dayContainer = $('<div>').addClass('dayContainer');

            var dayLabel = $('<div class="day">').text(hour.day);
            dayContainer.append(dayLabel);

            var sosStartTimeContainer = $('<div>').addClass('sos-hours');
            var sosLabel = $('<div>').text('SOS Start Time');
            sosStartTimeContainer.append(sosLabel);

            var sosStartTimeInput = $('<input>').attr('type', 'time').val(sosHour.startTime).addClass('form-control');
            sosStartTimeContainer.append(sosStartTimeInput);

            dayContainer.append(sosStartTimeContainer);

            var normalHoursContainer = $('<div>').addClass('normal-hours');
            var normalLabel = $('<div>').text('Normal Hours');
            normalHoursContainer.append(normalLabel);

            var startTimeInput = $('<input>').attr('type', 'time').val(hour.startTime).addClass('form-control');
            normalHoursContainer.append(startTimeInput);

            var endTimeInput = $('<input>').attr('type', 'time').val(hour.endTime).addClass('form-control');
            normalHoursContainer.append(endTimeInput);

            dayContainer.append(normalHoursContainer);

            var sosEndTimeContainer = $('<div>').addClass('sos-hours');
            var sosEndTimeLabel = $('<div>').text('SOS End Time');
            sosEndTimeContainer.append(sosEndTimeLabel);

            var sosEndTimeInput = $('<input>').attr('type', 'time').val(sosHour.endTime).addClass('form-control');
            sosEndTimeContainer.append(sosEndTimeInput);

            dayContainer.append(sosEndTimeContainer);

            var activeCheckbox = $('<input>').attr('type', 'checkbox').prop('checked', hour.active).addClass('form-control');
            dayContainer.append(activeCheckbox);

            container.append(dayContainer);
        });
    }
    function generate_business_hours(){
        $.ajax({
            type: "POST",
            url: "AJAXPOSTS",
            data: {action: 'getBusinessAndSosHours'},
            success: function(response) {
                var data = JSON.parse(response);

                var businessHours = data.businessHours.map(function(hour) {
                    return {
                        day: hour.nameEn,
                        startTime: hour.startTime,
                        endTime: hour.endTime,
                        active: hour.active === '1'
                    };
                });

                var sosHours = data.sosHours.map(function(hour) {
                    return {
                        day: hour.name,
                        startTime: hour.startTime,
                        endTime: hour.endTime,
                        active: hour.active === '1'
                    };
                });

                generateHours(businessHours, sosHours, 'businessHoursContainer');
            },
        });
    }
    $(document).ready(function(){
        generate_business_hours();
        $('#saveChanges').click(function() {
            var businessHours = [];
            var sosHours = [];

            $('#businessHoursContainer .dayContainer').each(function() {
                var dayContainer = $(this);
                var day = dayContainer.find('.day').text();
                var startTime = dayContainer.find('.normal-hours input[type="time"]:first').val();
                var endTime = dayContainer.find('.normal-hours input[type="time"]:last').val();
                var active = dayContainer.find('input[type="checkbox"]').is(':checked');
                businessHours.push({day: day, startTime: startTime, endTime: endTime, active: active});
            });

            $('#sosHoursContainer .dayContainer').each(function() {
                var dayContainer = $(this);
                var day = dayContainer.find('.day').text();
                var startTime = dayContainer.find('.sos-hours input[type="time"]:first').val();
                var endTime = dayContainer.find('.sos-hours input[type="time"]:last').val();
                var active = dayContainer.find('input[type="checkbox"]').is(':checked');
                sosHours.push({day: day, startTime: startTime, endTime: endTime, active: active});
            });

            var data = {
                businessHours: businessHours,
                sosHours: sosHours
            };

            $.ajax({
                type: "POST",
                url: "AJAXPOSTS",
                data: {
                    action: 'saveBusinessHours',
                    form: $('#scheduleForm').serializeArray(),
                    hours: data
                },
                success: function (response) {
                    swal({
                        title: "Business Hours saved",
                        type: "success",
                        showCancelButton: false,
                        showConfirmButton: true,
                        showConfirmButtonText: 'OK',
                        customClass: "customSwallClass"
                    });
                },
                error: function (error) {
                    swal({
                        title: "Business Hours failed to save",
                        type: "error",
                        showCancelButton: false,
                        showConfirmButton: true,
                        showConfirmButtonText: 'OK',
                        customClass: "customSwallClass"
                    });
                }
            });
        });
    });
</script>
<script src="js/plugins/idle-timer/idle-timer.min.js"></script>
</body>
</html>