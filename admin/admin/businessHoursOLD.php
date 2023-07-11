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
                <div class="col-lg-5">
                    <div class="ibox selected">
                        <div class="ibox-content" id="userInfoResults" style="height: auto;">
                            <div class="col-lg-12 flex-center">
                                <div id="BusinessHoursBoard" class="tab-pane active" style="width:100%;"></div>
                            </div>
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
    function generate_business_hours(){
        $.ajax({
            type: "POST",
            url: "AJAXPOSTS",
            data: {action: 'getBusinessHours'},
            success: function(response) {
                var exportedHtml = ' <div class="form-group">' +
                    '                       <span class="tooltiptext" style="font-size: 15px;">Select the days of the week that your business is open and provide the start and end times for each selected day.</span>' +
                    '                       <hr>' +
                    '                       <form id="scheduleForm" method="javascript:void(0)" >';
                var response = JSON.parse(response);
                for (var i=0;i<= response.length-1;i++){
                    var checkboxChecked = '';
                    if(response[i].active === '1'){ checkboxChecked = 'checked'; }
                    var date = new Date();
                    date.setHours(response[i].startTime.split(':')[0]);
                    date.setMinutes(response[i].startTime.split(':')[1]);
                    var formattedTimeStart = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    var formattedTimeStart = formattedTimeStart.replace(/[^\d:]/g, '');
                    var date2 = new Date();
                    date2.setHours(response[i].endTime.split(':')[0]);
                    date2.setMinutes(response[i].endTime.split(':')[1]);
                    var formattedTimeEnd = date2.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    var formattedTimeEnd = formattedTimeEnd.replace(/[^\d:]/g, '');
                    exportedHtml += '<div class="form-group row">' +
'                                          <div class="col-sm-2 custom-control custom-checkbox">' +
'                                              <input type="checkbox" class="custom-control-input" '+ checkboxChecked +' id="'+ (response[i].name).toLowerCase() +'Checkbox" name="'+ (response[i].name).toLowerCase() +'Checkbox">' +
'                                              <label class="custom-control-label" for="'+ (response[i].name).toLowerCase() +'Checkbox"></label>' +
'                                          </div>' +
'                                          <label for="'+ (response[i].name).toLowerCase() +'Start" class="col-sm-2 col-form-label">'+response[i].name+'</label>' +
'                                          <div class="col-sm-4">' +
'                                              <input type="time" value="'+formattedTimeStart+'" class="form-control" id="'+ (response[i].name).toLowerCase() +'Start" name="'+ (response[i].name).toLowerCase() +'Start">' +
'                                          </div>' +
'                                          <div class="col-sm-4">' +
'                                              <input type="time" value="'+formattedTimeEnd+'" class="form-control" id="'+ (response[i].name).toLowerCase() +'End" name="'+ (response[i].name).toLowerCase() +'End">' +
'                                          </div>' +
'                                      </div>';
                }
                exportedHtml += '<div class="form-group row">' +
                    '               <div class="col-sm-12">' +
                    '                    <button type="button" class="lada-save btn btn-primary btn btn-primary btn-sm btn-block saveChanges" onclick="save_form()" style="font-size: 20px;" data-style="expand-right"><i class="fa  fa-floppy-o" aria-hidden="true"></i> Save Changes</button>' +
                    '               </div>' +
                    '            </div>' +
                    '         </form>' +
                    '       </div>';
                $('#BusinessHoursBoard').html(exportedHtml);
            },
        });
    }
    function save_form(){
        $.ajax({
            type: "POST",
            url: "AJAXPOSTS",
            data: {
                action: 'saveBusinessHours',
                form: $('#scheduleForm').serialize()
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
    }
    $(document).ready(function(){
        generate_business_hours();
    });
</script>
<script src="js/plugins/idle-timer/idle-timer.min.js"></script>
</body>
</html>