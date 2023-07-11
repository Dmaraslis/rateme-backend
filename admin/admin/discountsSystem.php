<!DOCTYPE html>
<html>
<?php $pagename = 'Discounts System';
include 'header.php'; ?>
<body>
 <div id="wrapper">
   <?php  include 'menu.php'?>

            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2><?php echo $pagename; ?></h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.html">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a>Settings</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <strong><?php echo $pagename; ?></strong>
                        </li>
                    </ol>
                </div>
                <div class="col-lg-2">

                </div>
            </div>
        <div class="wrapper wrapper-content animated fadeInRight">
            <div class="container">
                <h1>Discount System Management</h1>
                <form id="discountForm">
                    <div class="form-group">
                        <label for="toggleDiscounts">Enable Discounts System:</label>
                        <div class="switch">
                            <div class="onoffswitch">
                                <input type="checkbox" name="toggleDiscounts" class="onoffswitch-checkbox" id="toggleDiscounts">
                                <label class="onoffswitch-label" for="toggleDiscounts">
                                    <span class="onoffswitch-inner"></span>
                                    <span class="onoffswitch-switch"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="discountMode">Discount Mode:</label>
                        <select class="form-control" id="discountMode" name="discountMode">
                            <option value="">Select Discount Mode</option>
                            <option value="auto">Auto Reward System Rule</option>
                            <option value="manual">Manual Discount Code Rule</option>
                        </select>
                    </div>
                    <div id="selectionPanel" >
                        <div class="form-group" id="autoRewardFields" style="display: none">
                            <h4>Auto Reward System Rule</h4>
                            <div class="form-group">
                                <label for="clientType">Client Type:</label>
                                <select class="form-control" id="clientType" name="clientType">
                                    <option value="">Select Client Type</option>
                                    <option value="new">New Client</option>
                                    <option value="existing">Existing Client</option>
                                    <option value="both">Both Type of Clients</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="rangeType">Range Type:</label>
                                <select class="form-control" id="rangeType" name="rangeType">
                                    <option value="">Select Range Type</option>
                                    <option value="step">By Step</option>
                                    <option value="range">By Range</option>
                                </select>
                            </div>
                            <div id="typeSelectionPanel" style="margin-bottom: 20px;">
                                <div class="form-group" id="stepCategoryContainer" style="display: none">
                                    <label for="stepCategory">Step Category:</label>
                                    <select class="form-control" id="stepCategory" name="stepCategory">
                                        <option value="">Select Step Category</option>
                                        <option value="price">Price Step</option>
                                        <option value="time">Time Step</option>
                                        <option value="appointment">Appointment Step</option>
                                        <option value="combined">Combined Step</option>
                                    </select>
                                </div>
                                <div class="form-group" id="rangeCategoryContainer" style="display: none">
                                    <label for="rangeCategory">Range Category:</label>
                                    <select class="form-control" id="rangeCategory" name="rangeCategory">
                                        <option value="">Select Range Category</option>
                                        <option value="price">Price Range</option>
                                        <option value="time">Time Range</option>
                                        <option value="appointment">Appointment Range</option>
                                        <option value="combined">Combined Range</option>
                                    </select>
                                </div>
                            </div>
                            <div id="stepContainer" style="margin-bottom: 20px;">
                                <div class="form-group range-fields" id="priceStepFields" style="display: none">
                                    <label for="priceStep" style="font-size: 15px;font-weight: 900">Recurring Price Sum Step:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-6">
                                            <label for="priceStepFrom">Recurring Price Step:</label>
                                            <input type="text" class="form-control" id="priceStepFrom" name="priceStepFrom">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group range-fields" id="timeStepFields" style="display: none">
                                    <label for="timeStep" style="font-size: 15px;font-weight: 900">Recurring Time Sum Step:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-6">
                                            <label for="timeStepFrom">Recurring Minutes Step:</label>
                                            <input type="text" class="form-control" id="timeStepFrom" name="timeStepFrom">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group range-fields" id="appointmentStepFields" style="display: none">
                                    <label for="appointmentStep" style="font-size: 15px;font-weight: 900">Recurring Appointments Sum Step:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-6">
                                            <label for="appointmentStepFrom">Recurring Sum Step:</label>
                                            <input type="text" class="form-control" id="appointmentStepFrom" name="appointmentStepFrom">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group range-fields" id="combinedStepFields" style="display: none">
                                    <label for="combinedStep">Recurring Combined Step:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-12">
                                            <label for="priceStep" style="font-size: 15px;font-weight: 900">Price Sum Step:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="priceStepFrom">Recurring Sum Price Step:</label>
                                                    <input type="text" class="form-control" id="priceStepFrom" name="priceStepFrom">
                                                </div>
                                            </div>
                                            <label for="timeStep" style="font-size: 15px;font-weight: 900;margin-top: 10px">Time Sum Step:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="timeStepFrom">Recurring Minutes Step:</label>
                                                    <input type="text" class="form-control" id="timeStepFrom" name="timeStepFrom">
                                                </div>
                                            </div>
                                            <label for="appointmentStep" style="font-size: 15px;font-weight: 900;margin-top: 10px">Appointments Step:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="appointmentStepFrom">Recurring Num Step:</label>
                                                    <input type="text" class="form-control" id="appointmentStepFrom" name="appointmentStepFrom">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="rangeContainer" style="margin-bottom: 20px;">
                                <div class="form-group range-fields" id="priceRangeFields" style="display: none">
                                    <label for="priceRange" style="font-size: 15px;font-weight: 900">Price Sum Range:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-6">
                                            <label for="priceRangeFrom">Start Sum Price:</label>
                                            <input type="text" class="form-control" id="priceRangeFrom" name="priceRangeFrom">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="priceRangeTo">End Sum Price:</label>
                                            <input type="text" class="form-control" id="priceRangeTo" name="priceRangeTo">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group range-fields" id="timeRangeFields" style="display: none">
                                    <label for="timeRange" style="font-size: 15px;font-weight: 900">Time Sum Range:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-6">
                                            <label for="timeRangeFrom">Start Sum Time (minutes):</label>
                                            <input type="text" class="form-control" id="timeRangeFrom" name="timeRangeFrom">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="timeRangeTo">End Sum Time (minutes):</label>
                                            <input type="text" class="form-control" id="timeRangeTo" name="timeRangeTo">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group range-fields" id="appointmentRangeFields" style="display: none">
                                    <label for="appointmentRange" style="font-size: 15px;font-weight: 900">Appointments Sum Range:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-6">
                                            <label for="appointmentRangeFrom">Start Range Sum:</label>
                                            <input type="text" class="form-control" id="appointmentRangeFrom" name="appointmentRangeFrom">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="appointmentRangeTo">End Range Sum:</label>
                                            <input type="text" class="form-control" id="appointmentRangeTo" name="appointmentRangeTo">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group range-fields" id="combinedRangeFields" style="display: none">
                                    <label for="combinedRange">Combined Range:</label>
                                    <div class="row" style=" background-color: #09e2ff26;padding: 10px 5px 15px 5px;border-radius: 10px;">
                                        <div class="col-md-12">
                                            <label for="priceRange" style="font-size: 15px;font-weight: 900">Price Sum Range:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="priceRangeFrom">Start Sum Price:</label>
                                                    <input type="text" class="form-control" id="priceRangeFrom" name="priceRangeFrom">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="priceRangeTo">End Sum Price:</label>
                                                    <input type="text" class="form-control" id="priceRangeTo" name="priceRangeTo">
                                                </div>
                                            </div>
                                            <label for="timeRange" style="font-size: 15px;font-weight: 900;margin-top: 10px">Time Sum Range:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="timeRangeFrom">Start Sum Time (minutes):</label>
                                                    <input type="text" class="form-control" id="timeRangeFrom" name="timeRangeFrom">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="timeRangeTo">End Sum Time (minutes):</label>
                                                    <input type="text" class="form-control" id="timeRangeTo" name="timeRangeTo">
                                                </div>
                                            </div>
                                            <label for="appointmentRange" style="font-size: 15px;font-weight: 900;margin-top: 10px">Appointments Sum Range:</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="appointmentRangeFrom">Start Range Sum:</label>
                                                    <input type="text" class="form-control" id="appointmentRangeFrom" name="appointmentRangeFrom">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="appointmentRangeTo">End Range Sum:</label>
                                                    <input type="text" class="form-control" id="appointmentRangeTo" name="appointmentRangeTo">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="discountType">Discount Type:</label>
                                <select class="form-control" id="discountType" name="discountType">
                                    <option value="">Select Discount Type</option>
                                    <option value="flat">Flat Discount</option>
                                    <option value="percentage">Percentage Discount</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="discountValue">Discount Value:</label>
                                <input type="text" class="form-control" id="discountValue" name="discountValue">
                            </div>
                        </div>
                        <div class="form-group" id="manualDiscountFields" style="display: none">
                            <h4>Manual Discount Code</h4>

                            <div class="form-group">
                                <label for="clientType">Client Type:</label>
                                <select class="form-control" id="clientType" name="clientType">
                                    <option value="">Select Client Type</option>
                                    <option value="new">New Client</option>
                                    <option value="existing">Existing Client</option>
                                    <option value="both">Both Type of Clients</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="discountType">Discount Type:</label>
                                <select class="form-control" id="discountType" name="discountType">
                                    <option value="">Select Discount Type</option>
                                    <option value="flat">Flat Discount</option>
                                    <option value="percentage">Percentage Discount</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="discountValue">Discount Value:</label>
                                <input type="text" class="form-control" id="discountValue" name="discountValue">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><i class="customFA fa fa-plus" aria-hidden="true"></i>&nbsp;Add Rule</button>
                    </div>
                </form>
            </div>
            <script>
                $(document).ready(function () {
                    var holdedAuto = $('#autoRewardFields').html();
                    var holdedManual = $('#manualDiscountFields').html();
                    $("#discountMode").change(function () {
                        $('#selectionPanel').html('');
                        var selectedMode = $(this).val();
                        if (selectedMode === "auto") {
                            $('#selectionPanel').append(holdedAuto);
                            $("#autoRewardFields").show();
                            $("#manualDiscountFields").hide();
                        } else if (selectedMode === "manual") {
                            $('#selectionPanel').append(holdedManual);
                            $("#autoRewardFields").hide();
                            $("#manualDiscountFields").show();
                        }else {
                            $('#selectionPanel').html('');
                        }


                        var holdedstepCategoryContainer = $('#stepCategoryContainer').html();
                        var holdedrangeCategoryContainer = $('#rangeCategoryContainer').html();
                        var holdedpriceRangeFields = $('#priceRangeFields').html();
                        var holdedtimeRangeFields = $('#timeRangeFields').html();
                        var holdedappointmentRangeFields = $('#appointmentRangeFields').html();
                        var holdedcombinedRangeFields = $('#combinedRangeFields').html();

                        var holdedpriceStepFields = $('#priceStepFields').html();
                        var holdedtimeStepFields = $('#timeStepFields').html();
                        var holdedappointmentStepFields = $('#appointmentStepFields').html();
                        var holdedcombinedStepFields = $('#combinedStepFields').html();
                        $("#rangeType").change(function () {
                            $('#rangeContainer').html('');
                            $('#stepContainer').html('');
                            var selectedRangeType = $(this).val();
                            $('#typeSelectionPanel').html('');
                            $(".range-fields").hide();
                            $("#" + selectedRangeType + "RangeFields").show();
                            if (selectedRangeType === "range") {
                                $('#typeSelectionPanel').append(holdedrangeCategoryContainer);
                                $("#stepCategoryContainer").hide();
                                $("#rangeCategoryContainer").show();
                            } else if(selectedRangeType === "step"){
                                $('#typeSelectionPanel').append(holdedstepCategoryContainer);
                                $("#stepCategoryContainer").show();
                                $("#rangeCategoryContainer").hide();
                            }else {
                                $('#typeSelectionPanel').html('');
                            }


                            $("#rangeCategory").change(function () {
                                $('#rangeContainer').html('');
                                $('#stepContainer').html('');
                                var selectedrangeCategory = $(this).val();
                                $(".range-fields").hide();
                                $("#" + selectedrangeCategory + "RangeFields").show();
                                if (selectedrangeCategory === "time") {
                                    $('#rangeContainer').append(holdedtimeRangeFields);
                                    $("#appointmentRangeFields").hide();
                                    $("#combinedRangeFields").hide();
                                } else if (selectedrangeCategory === "price") {
                                    $('#rangeContainer').append(holdedpriceRangeFields);
                                    $("#timeRangeFields").hide();
                                    $("#appointmentRangeFields").hide();
                                    $("#combinedRangeFields").hide();
                                } else if (selectedrangeCategory === "appointment") {
                                    $('#rangeContainer').append(holdedappointmentRangeFields);
                                    $("#timeRangeFields").hide();
                                    $("#combinedRangeFields").hide();
                                } else if (selectedrangeCategory === "combined") {
                                    $('#rangeContainer').append(holdedcombinedRangeFields);
                                    $("#timeRangeFields").hide();
                                    $("#appointmentRangeFields").hide();
                                    $("#combinedRangeFields").show();
                                }
                            });


                            $("#stepCategory").change(function () {
                                $('#rangeContainer').html('');
                                $('#stepContainer').html('');
                                var selectedstepCategory = $(this).val();
                                $(".step-fields").hide();
                                $("#" + selectedstepCategory + "RangeFields").show();
                                if (selectedstepCategory === "time") {
                                    $('#stepContainer').append(holdedtimeStepFields);
                                    $("#appointmentRangeFields").hide();
                                    $("#combinedRangeFields").hide();
                                } else if (selectedstepCategory === "price") {
                                    $('#stepContainer').append(holdedpriceStepFields);
                                    $("#timeRangeFields").hide();
                                    $("#appointmentRangeFields").hide();
                                    $("#combinedRangeFields").hide();
                                } else if (selectedstepCategory === "appointment") {
                                    $('#stepContainer').append(holdedappointmentStepFields);
                                    $("#timeRangeFields").hide();
                                    $("#combinedRangeFields").hide();
                                } else if (selectedstepCategory === "combined") {
                                    $('#stepContainer').append(holdedcombinedStepFields);
                                    $("#timeRangeFields").hide();
                                    $("#appointmentRangeFields").hide();
                                    $("#combinedRangeFields").show();
                                }
                            });
                        });
                    });
                    $("#discountForm").submit(function (event) {
                        event.preventDefault();

                        var formData = $(this).serialize();
                        $.ajax({
                            type: "POST",
                            url: "AJAXPOSTS",
                            data: {
                                action: 'setupDiscountRule',
                                formData:formData,
                                type: 'add'
                            },
                            success: function (response) {
                                console.log(response);
                            },
                            error: function (xhr, status, error) {
                                console.log(error);
                            }
                        });
                    });


                });
            </script>

            </div>
        <?php include 'footer.php';?>

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
    <script src="js/bootstrap-switch.min.js"></script>


 <!-- iCheck -->
    <script src="js/plugins/iCheck/icheck.min.js"></script>
        <script>
            $(document).ready(function () {
                $('.i-checks').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green',
                });
            });
        </script>
</body>

</html>
