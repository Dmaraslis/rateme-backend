<!DOCTYPE html>
<html>
<?php
require_once('./include/admin-load.php');
$pagename = 'Account Settings';
$gatherAdminInfos = $user->gather_admin_by_id_for_edit();
include 'header.php'; ?>
<link href="css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
<!-- Ladda -->
<link href="css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

<style>
    .avatar-upload {
        position: relative;
        max-width: 205px;
        margin: 50px auto;
    }
    .avatar-upload .avatar-edit {
        position: absolute;
        right: 12px;
        z-index: 1;
        top: 10px;
    }
    .avatar-upload .avatar-edit input {
        display: none;
    }
    .avatar-upload .avatar-edit input + label {
        display: inline-block;
        width: 34px;
        height: 34px;
        margin-bottom: 0;
        border-radius: 100%;
        background: #38ab72;
        border: 1px solid transparent;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);
        cursor: pointer;
        font-weight: normal;
        transition: all 0.2s ease-in-out;
    }
    .avatar-upload .avatar-edit input + label:hover {
        background: #38ab72;
        border-color: #d6d6d6;
    }
    .avatar-upload .avatar-edit input + label:after {
        content: "\f040";
        font-family: 'FontAwesome';
        color: #fff;
        position: absolute;
        top: 10px;
        left: 0;
        right: 0;
        text-align: center;
        margin: auto;
    }
    .avatar-upload .avatar-preview {
        width: 192px;
        height: 192px;
        position: relative;
        border-radius: 100%;
        border: 6px solid #38ab72;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.1);
    }
    .avatar-upload .avatar-preview > div {
        width: 100%;
        height: 100%;
        border-radius: 100%;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }
    .form-control:focus {
        color: #38aa72!important;
    }
    .customSwallClass {
        color: black!important;
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
                    <a href="index.php">Home</a>
                </li>
                <li class="breadcrumb-item active">
                    <strong><span style='color:#a9ff7d'>Account Settings</span></strong>
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
                                <div id="contact-1" class="tab-pane active" style="width:100%;">
                                    <div class="row m-b-lg">
                                        <div class="col-lg-12 text-center">
                                            <?php if ($gatherAdminInfos['image']){?>
                                                <div class="avatar-upload">
                                                    <div class="avatar-edit">
                                                        <input type='file' id="imageUpload" accept=".png, .jpg, .jpeg" />
                                                        <label for="imageUpload"></label>
                                                    </div>
                                                    <div class="avatar-preview">
                                                        <div id="imagePreview" style="background-image: url(images/admph/large-<?=$gatherAdminInfos['image']?>);"></div>
                                                        <div id="imgForSave" style="display:none;"></div>
                                                    </div>
                                                </div>
                                            <?php }else{ ?>
                                                <p><i style="color:#6e0e0b;font-size: 100px;" class="fa fa-4x fa-user-circle" aria-hidden="true"></i></p>
                                            <?php } ?>
                                            <p><h2 style="text-transform: capitalize"><input class="input form-control" style="font-size: 20px;text-align: center;" type="text" value="<?=$gatherAdminInfos['nickname']?>" id="user-nickname" placeholder="Place user Nickname"></h2></p>
                                        </div>
                                    </div>
                                    <div class="row" style="margin: 0px;">
                                        <div class="col-lg-12" style="text-align: center;">
                                            <p><h2>User Type<h2> <i style="font-size: 17px;"><?=$gatherAdminInfos['userAccessLevel']?></i></p>
                                        </div>
                                    </div><hr>
                                    <div class="full-height-scroll">
                                        <strong>User Infos</strong>
                                        <ul class="list-group clear-list">
                                            <li class="list-group-item"><span class="float-right" id="user-username"><?=$gatherAdminInfos['username']?></span> USERNAME: </li>
                                            <li class="list-group-item"><button type="button" onclick="editPAssword()" class="float-right btn btn-primary btn-sm btn-block col-lg-2" style="margin-top: 0px!important;margin-left: 5px;"><i class="fa fa-pencil" aria-hidden="true"></i> Change</button><button type="button" onclick="viewPAssword(this);" class="float-right btn btn-primary btn-sm btn-block col-lg-2" style="margin-top: 0px!important;margin-top:0px;margin-left: 20px;"><i class="fa fa-eye" aria-hidden="true"></i> <span id="buttonText">View</span></button><span class="float-right" id="user-password"><?=$gatherAdminInfos['password']?></span> PASSWORD: </li>
                                        </ul>
                                        <hr/>
                                    </div>
                                    <hr><button type="button" class="ladda-button custom-ladda lada-save btn btn-primary btn btn-primary btn-sm btn-block saveChanges" style="font-size: 20px;" data-style="expand-right"><i class="fa  fa-floppy-o" aria-hidden="true"></i> Save Changes</button>
                                </div>
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
    $( '.lada-save' ).ladda();

    $('.saveChanges').on('click', function() { saveuser(); });

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').css('background-image', 'url('+e.target.result +')');
                $('#imgForSave').attr('data-img', e.target.result);
                $('#imagePreview').hide();
                $('#imagePreview').fadeIn(650);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imageUpload").change(function() {
        readURL(this);
    });

    function viewPAssword(elem){
        var currentText =  $('#buttonText').html();
        if (currentText === 'View') {
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {action: 'viewadps'},
                dataType: "json",
                success: function (data) {
                    $('#user-password').html(data.response);
                    var timeleft = 4;
                    $('#buttonText').html('5s');
                    var downloadTimer = setInterval(function () {
                        $('#buttonText').html(timeleft + 's');
                        if (timeleft <= 0) {
                            clearInterval(downloadTimer);
                            $('#user-password').html('••••••••••');
                            $('#buttonText').html('View');
                            $(elem).attr('disabled="none"');

                        }
                        timeleft -= 1;
                    }, 1000)

                }
            });
        }


    }

    function editPAssword(){
        swal({
            title: "Type new password",
            type: "input",
            showCancelButton: true,
            closeOnConfirm: false,
            confirmButtonColor: "#38ab72",
            confirmButtonText: "Confirm",
            inputPlaceholder: "New password here",
            customClass: "customSwallClass",
        }, function (inputValue) {
            if (inputValue === "") {
                swal.showInputError("You need to write something! or press cancel");
                return false
            }
            swal({
                title: "Confirm new password",
                type: "input",
                showCancelButton: true,
                closeOnConfirm: false,
                confirmButtonColor: "#38ab72",
                confirmButtonText: "Confirm",
                inputPlaceholder: "New password here",
                customClass: "customSwallClass",
            },function (inputValue2) {
                if (inputValue === false) return false;
                if (inputValue === "") {
                    swal.showInputError("You need to write something! or press cancel");
                    return false
                }
                if (inputValue === inputValue2) {
                    $.ajax({
                        url: "AJAXPOSTS",
                        type: "POST",
                        data: {action: 'chngamdps',newpw:inputValue},
                        dataType: "json",
                        success: function (data) {
                            swal({
                                title: "Successfully changed",
                                type: "success",
                                confirmButtonColor: "#38ab72",
                                confirmButtonText: "Confirm",
                                customClass: "customSwallClass",
                            });
                        }
                    });
                }else{
                    swal.showInputError("Passwords not match!");
                    return false
                }
            });
        });
    }

    function saveuser(){
        swal({
            title: "Save Changes?",
            text: "Are you sure to save changes?",
            type: "success",
            showCancelButton: true,
            confirmButtonColor: "#38ab72",
            confirmButtonText: "Yes, proceed!",
            customClass: "customSwallClass",
            closeOnConfirm: true
        }, function () {
            $( '.lada-save' ).ladda('start');
            $('.custom-ladda').html('Saving');
            var image = $('#imgForSave').attr('data-img');
            var nickname = $('#user-nickname').val();
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {
                    action: 'updtAdmInf',
                    nickname:nickname,
                    image:image
                },
                dataType: "json",
                success: function (data) {
                    if (data.response){
                        toastr.success(data.response, 'Account Settings');
                        $( '.lada-save' ).ladda('stop');
                        $('.custom-ladda').html('<i class="fa fa-floppy-o" aria-hidden="true"></i>Saving Changes');
                    }else{
                        toastr.error(data.errorRespo, 'Account Settings');
                    }
                }
            });
        });
    }

    $('#dashLoad').css('display','none');

</script>
<script src="js/plugins/idle-timer/idle-timer.min.js"></script>
</body>
</html>