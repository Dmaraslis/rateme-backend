<!DOCTYPE html>
<html>
<?php
$pagename = ucfirst($_GET['short']); include 'header.php'; ?>
<link href="css/parl.css?ver=<?=time()?>" rel="stylesheet">
<body>
    <div id="wrapper">
        <?php include 'menu.php'; ?>
            <div class="row wrapper border-bottom white-bg page-heading" style="padding: 0px;">
                <div class="col-xl-4">
                    <h2><?=$pagename?></h2>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php">Home</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <strong><?=$pagename?></strong>
                        </li>
                    </ol>
                </div>
                <div class="col-xl-8 customQ">
                    <div id="smsQuanityLeft"></div>
                </div>
            </div>
        <div class="wrapper wrapper-content animated fadeInRight ecommerce">
            <div class="row">
                <div class="col-lg-12 swapResp2">
                    <div class="ibox">
                        <div id="upsideDate" style="display:none;"></div>
                        <div class="ibox-content swapResp" style="overflow-x: auto;">
                            <div class="flex-center newLoaderContainer"> Loading... </div>
                        </div>
                    </div>
                </div>
            </div>
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
        <?php include 'footer.php'; ?>
        </div>



    <!-- Page-Level Scripts -->
    <script>


        $(document).ready(function(){
            if(screen.width > '700'){
                $('.autoSwapMobile').remove();
                $('.seachSwapMobile').remove();
                $('.autoSwapPC').css('display','initial');
                $('.seachSwapPC').css('display','flex');
            } else{
                $('.seachSwapPC').remove();
                $('.autoSwapPC').remove();
                $('.autoSwapMobile').css('display','initial');
                $('.seachSwapMobile').css('display','flex');
            }
            gather_swaps('firstLoad',18);

        });

        function gather_swaps(type,previewPerPagePreset) {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            var currentPage = parseInt(urlParams.get('page'));
            var categoryType = urlParams.get('short');
            var previewType = urlParams.get('type');
            if (type === 'firstLoad') {
                checkCookie('swapsPerPage', previewPerPagePreset);
            }
            var previewPerPage = getCookie('swapsPerPage');
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {
                    action: 'gatherBulk',
                    limit: previewPerPage,
                    currentPage: currentPage,
                    categoryType: categoryType,
                    previewType: previewType
                },
                dataType: "json",
                success: function (data) {
                    var responsePagination = '';
                    var pages = data.pages;
                    var responseLength = (data.response.length) - 1;
                    if (pages) {
                        responsePagination += '<ul class="pagination float-left">';
                        if (pages > 1 && currentPage > 1) {
                            responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change(' + 1 + ',' + previewPerPage + ');return false;">«</a></li>';
                        } else {
                            responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">«</a></li>';
                        }
                        if (currentPage > 1) {
                            responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change(' + (currentPage - 1) + ',' + previewPerPage + ');return false;">‹</a></li>';
                        } else {
                            responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">‹</a></li>';
                        }
                        var meter1 = 0;
                        for (var j = currentPage; j <= pages; j++) {
                            if (meter1 <= 4) {
                                if (currentPage == j) {
                                    responsePagination += '<li class="footable-page active"><a href="JavaScript:Void(0)" style="background-color: #00c383!important;">' + currentPage + '</a></li>';
                                } else {
                                    responsePagination += '<li class="footable-page"><a href="#" onclick="page_change(' + j + ',' + previewPerPage + ');return false;">' + j + '</a></li>';
                                }
                            } else if (meter1 > 4 && j < (pages - 4)) {
                                if (meter1 === 6) {
                                    responsePagination += '<li class="footable-page"><a href="JavaScript:Void(0)">...</a></li>';
                                }
                                if (currentPage > 7) {
                                    if (meter1 > Math.round(pages / 2) && meter1 < (Math.round(pages / 2)) + 4) {
                                        responsePagination += '<li class="footable-page"><a href="#" onclick="page_change(' + j + ',' + previewPerPage + ');return false;">' + j + '</a></li>';
                                    }
                                    if (meter1 === (Math.round(pages / 2)) + 4) {
                                        responsePagination += '<li class="footable-page"><a href="JavaScript:Void(0)">...</a></li>';
                                    }
                                }
                            } else if (j >= (pages - 1)) {
                                responsePagination += '<li class="footable-page"><a href="#" onclick="page_change(' + j + ',' + previewPerPage + ');return false;">' + j + '</a></li>';
                            }
                            meter1++;
                        }
                        if (currentPage < pages) {
                            responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change(' + parseInt(currentPage + 1) + ',' + previewPerPage + ');return false;">›</a></li>';
                        } else {
                            responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">›</a></li>';
                        }
                        if (pages > 1 && currentPage != pages) {
                            responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change(' + pages + ',' + previewPerPage + ');return false;">»</a></li>';
                        } else {
                            responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">»</a></li>';
                        }
                        responsePagination += '</ul>';
                    } else {
                        responsePagination += '<ul class="pagination float-right"></ul>';
                    }
                    var responsePagination2 = '<ul class="pagination float-right" style="color: grey;">Previewing ' + (currentPage * previewPerPage) + ' of ' + data.swaps + ' ' + categoryType + '</ul>';


                    if (categoryType === 'clients') {
                        var response = '<table class="footable table table-stripped toggle-arrow-tiny">' +
                            '                                <thead>' +
                            '                                <tr>' +
                            '                                    <th>Action</th>' +
                            '                                    <th data-hide="exchanger">Name</th>' +
                            '                                    <th data-hide="exchanger">Surname</th>' +
                            '                                    <th data-hide="exchanger">Phone</th>' +
                            '                                    <th data-hide="exchanger">Email</th>' +
                            '                                    <th data-hide="exchanger">Referrer</th>' +
                            '                                    <th data-hide="exchanger">Note</th>' +
                            '                                    <th data-hide="exchanger">Date Added</th>' +
                            '                                </tr>' +
                            '                                </thead>' +
                            '                                <tbody style="color:white;">';

                        var stringREESP = '';
                        for (var i = 0; i <= responseLength; i++) {
                            var trResp = '<tr style="height:50px;" class="swaps">';
                            response += trResp +
                            '<td><button id="customModal-shop-dam-info-button" class="customModal-shop-dam-info-button" data-clientid="'+ data.response[i].id +'"><i class="fa fa-info-circle" aria-hidden="true"></i></button></td>' +
                                '<td onclick="add_client(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].name + '</span></td>' +
                                '<td onclick="add_client(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].surname + '</span></td>' +
                                '<td><a href="tel:' + data.response[i].phone + '"><i class="customFA fa fa-phone-square" style="font-size: 40px" aria-hidden="true"></i></a> ' + data.response[i].phone + '</td>' +
                                '<td onclick="add_client(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].email + '</span></td>' +
                                '<td onclick="add_client(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].referrerInfos.name + ' ' + data.response[i].referrerInfos.surname + '</span></td>' +
                                '<td onclick="add_client(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].note + '</span></td>' +
                                '<td onclick="add_client(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].dateTimeCreated + '</span></td>' +
                                '</tr>';
                        }
                        response += '  </tbody>' +
                            '                                <tfoot>' +
                            '                                <tr>' +
                            '                                    <td colspan="9">' + responsePagination + responsePagination2 +
                            '                                    </td>' +
                            '                                </tr>' +
                            '                                </tfoot>' +
                            '                            </table>';
                    }

                    if (categoryType === 'haircuts') {
                        var extraIconsData = '' +
                            '<div class="impres" style="float:right;display:flex;height:60px;    margin-right: 5px;">' +
                                '<div style="padding: 10px;margin: auto;border: 1px solid #1d1d1d;">' +
                                    '<div style="width:15px;height:15px;border-radius: 100%;background-color: #139623;margin: auto;"></div>Accepted' +
                                '</div>' +
                                '<div style="padding: 10px;margin: auto;border: 1px solid #1d1d1d;">' +
                                    '<div style="width:15px;height:15px;border-radius: 100%;background-color: #ff0900;margin: auto;"></div>Declined' +
                                '</div>' +
                                '<div style="padding: 10px;margin: auto;border: 1px solid #1d1d1d;">' +
                                    '<div style="width:15px;height:15px;border-radius: 100%;background-color: orange;margin: auto;"></div>Pending Apporival' +
                                '</div>' +
                                '<div style="padding: 10px;margin: auto;border: 1px solid #1d1d1d;">' +
                                    '<div style="width:15px;height:15px;border-radius: 100%;background-color: #ff6500;margin: auto;"><i style="position:relative;top: -1px;left: 4px;font-size: 17px;color: #000000;" class="fa fa-info" aria-hidden="true"></i></div> Pending price and commission to close appointment' +
                                '</div>' +
                                '<div style="padding: 10px;margin: auto;border: 1px solid #1d1d1d;">' +
                                    '<div style="width:15px;height:15px;border-radius: 100%;background-color: #f8fff4;margin: auto;"><i style="position:relative;top:-2px;left:-1px;font-size: 20px;color: #2d2d2d" class="fa fa-check-circle-o" aria-hidden="true"></i></div>Completed' +
                                '</div>' +
                                '<div style="padding: 10px;margin: auto;border: 1px solid #1d1d1d;">' +
                                    '<div style="width:15px;height:15px;border-radius: 100%;background-color: rebeccapurple;margin: auto;"><i style="position:relative;top:-2px;left:-1px;font-size: 20px;" class="fa fa-clock-o" aria-hidden="true"></i></div> On Progress' +
                                '</div>' +
                            '</div>';
                        var buttonsData = '';
                        if (previewType === 'appointments') {
                            buttonsData = '<button class="custom-button-tab" onclick="handleUrl(\'all\')">All</button><button class="custom-button-tab selected">Appointments</button><button class="custom-button-tab" onclick="handleUrl(\'reserved\')">Reserved Hours</button>'+extraIconsData;
                        } else if (previewType === 'reservedHours') {
                            buttonsData = '<button class="custom-button-tab" onclick="handleUrl(\'all\')">All</button><button class="custom-button-tab" onclick="handleUrl(\'appointments\')">Appointments</button><button class="custom-button-tab selected">Reserved Hours</button>';
                        } else {
                            buttonsData = '<button class="custom-button-tab selected">All</button><button class="custom-button-tab " onclick="handleUrl(\'appointments\')">Appointments</button><button class="custom-button-tab" onclick="handleUrl(\'reserved\')">Reserved Hours</button>'+extraIconsData;
                        }
                        if (previewType === 'reservedHours') {
                            var response = buttonsData + '<table class="footable table table-stripped toggle-arrow-tiny">' +
                                '                                <thead>' +
                                '                                <tr>' +
                                '                                    <th data-hide="exchanger"> </th>' +
                                '                                    <th data-hide="exchanger"> </th>' +
                                '                                    <th data-hide="exchanger">on date</th>' +
                                '                                    <th data-hide="exchanger">Note</th>' +
                                '                                </tr>' +
                                '                                </thead>' +
                                '                                <tbody style="color:white;">';
                        }else{
                            var response = buttonsData + '<table class="footable table table-stripped toggle-arrow-tiny">' +
                                '                                <thead>' +
                                '                                <tr>' +
                                '                                    <th data-hide="exchanger">State</th>' +
                                '                                    <th data-hide="exchanger">Client</th>' +
                                '                                    <th data-hide="exchanger">Phone</th>' +
                                '                                    <th data-hide="exchanger">Services</th>' +
                                '                                    <th data-hide="exchanger">on date</th>' +
                                '                                    <th data-hide="exchanger">Note</th>' +
                                '                                    <th data-hide="exchanger">Barber</th>' +
                                '                                    <th data-hide="exchanger">Execution Time</th>' +
                                '                                    <th data-hide="exchanger">Price</th>' +
                                '                                </tr>' +
                                '                                </thead>' +
                                '                                <tbody style="color:white;">';
                        }
                        var stringREESP = '';
                        for (var i = 0; i <= responseLength; i++) {
                            var selectedServices = '';
                            for (var pk = 0; pk <= data.response[i].servicesInfos.length - 1; pk++) {
                                selectedServices += '<li class="search-choice2"><span>' + data.response[i].servicesInfos[pk].name + '</span></li>';
                            }

                            if (data.response[i].customerId === '0') {
                                response += '<tr style="height:50px;background-color:#ff6557" class="swaps" onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')">' +
                                    '<td><span></span></td>' +
                                    '<td><span></span></td>' +
                                    '<td><span> Reserved Appointment</span></td>' +
                                    '<td><span>' + data.response[i].dateTimeExecuted + '</span></td>' +
                                    '<td><span>' + data.response[i].note + '</span></td>' +
                                    '</tr>';
                            } else {
                                response += '<tr style="height:50px;" class="swaps">'+
                                    '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')">'+ data.response[i].state.icon +'</td>' +
                                    '<td>' +
                                    '       <button id="customModal-shop-dam-info-button" class="customModal-shop-dam-info-button-small" data-clientid="'+ data.response[i].id +'"><i class="fa fa-info-circle" aria-hidden="true"></i></button>' +
                                    '       <span onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')">' + data.response[i].clientInfos.name + ' ' + data.response[i].clientInfos.surname + '</span>' +
                                    '</td>' +
                                    '<td><a href="tel:' + data.response[i].clientInfos.phone + '"><i class="customFA fa fa-phone-square" style="font-size: 40px" aria-hidden="true"></i></a> ' + data.response[i].clientInfos.phone + '</td>' +
                                    '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><div class="chosen-container chosen-container-multi chosen-container-active"><ul class="chosen-choices">' + selectedServices + '</ul></td></div>' +
                                    '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].dateTimeExecuted + '</span></td>' +
                                    '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].note + '</span></td>' +
                                    '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].barberInfos.name + '</span></td>' +
                                    '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].executionTime + '</span></td>' +
                                    '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].commission + '</span></td>' +
                                    '</tr>';
                            }
                        }
                        response += '  </tbody>' +
                            '                                <tfoot>' +
                            '                                <tr>' +
                            '                                    <td colspan="9">' + responsePagination + responsePagination2 +
                            '                                    </td>' +
                            '                                </tr>' +
                            '                                </tfoot>' +
                            '                            </table>';
                    }

                    if (categoryType === 'smslog') {
                        var response = '<table class="footable table table-stripped toggle-arrow-tiny">' +
                            '                                <thead>' +
                            '                                <tr>' +
                            '                                    <th data-hide="exchanger">Client</th>' +
                            '                                    <th data-hide="exchanger">Phone</th>' +
                            '                                    <th data-hide="exchanger">Title</th>' +
                            '                                    <th data-hide="exchanger">Message</th>' +
                            '                                    <th data-hide="exchanger">on date</th>' +
                            '                                    <th data-hide="exchanger">Sender</th>' +
                            '                                </tr>' +
                            '                                </thead>' +
                            '                                <tbody style="color:white;">';

                        var stringREESP = '';
                        for (var i = 0; i <= responseLength; i++) {
                            if(data.response[i].createdFromExternalUserId === 'notificationSystem'){
                                var sendedUsername = 'System';
                                var sendeduserPicture = 'server.png';
                            }else{
                                var sendedUsername = data.response[i].userName;
                                var sendeduserPicture = data.response[i].userPicture;
                            }
                            response += '<td><span>' + data.response[i].patientName + '</span></td>' +
                                '<td><a href="tel:' + data.response[i].phone + '"><i class="customFA fa fa-phone-square" style="font-size: 40px" aria-hidden="true"></i></a> ' + data.response[i].phone + '</td>' +
                                '<td><span>' + data.response[i].from + '</span></td>' +
                                '<td><span>' + data.response[i].message + '</span></td>' +
                                '<td><span>' + data.response[i].dateTimeSent + '</span></td>' +
                                '<td><span>' + sendedUsername + '<img style="width: 35px;background-color: #ffffff;border-radius: 30px;padding: 5px;margin-left: 10px;" src="/admin/images/admph/' + sendeduserPicture + '"></span></td>' +
                                '</tr>';
                        }
                        response += '  </tbody>' +
                            '                                <tfoot>' +
                            '                                <tr>' +
                            '                                    <td colspan="9">' + responsePagination + responsePagination2 +
                            '                                    </td>' +
                            '                                </tr>' +
                            '                                </tfoot>' +
                            '                            </table>';
                        var exp  = data.smsLeft+'<br><small style="font-size: 8px;">SMS Left</small>';
                        if(data.smsLeft < 400){
                            $('#smsQuanityLeft').addClass('midSmsLeft');
                        }else if (data.smsLeft < 100){
                            $('#smsQuanityLeft').addClass('lowSmsLeft');
                        }
                        $('#smsQuanityLeft').html(exp);
                    }
                    $('.swapResp').html(response);
                    $('#upsideDate').html(responsePagination + responsePagination2).css('display', 'block');
                },
            });
        }


        function addQueryToUrl(url, key, value) {
            const urlObject = new URL(url);
            const params = new URLSearchParams(urlObject.search);
            params.set(key, value);
            urlObject.search = params.toString();
            return urlObject.toString();
        }

        function removeURLParameter(url, parameter) {
            var urlParts = url.split('?');
            if (urlParts.length >= 2) {
                var parameters = urlParts[1].split('&');
                for (var i = parameters.length - 1; i >= 0; i--) {
                    if (parameters[i].startsWith(parameter + '=')) {
                        parameters.splice(i, 1);
                    }
                }
                var newParameters = parameters.join('&');
                return urlParts[0] + (newParameters.length > 0 ? '?' + newParameters : '');
            }
            return url;
        }

        function handleUrl(type) {
            const url = window.location.href;
            if(type === 'appointments'){
                window.location.href = addQueryToUrl(url,'type','appointments');
            }else if (type === 'reserved'){
                window.location.href = addQueryToUrl(url,'type','reservedHours');
            }else if (type === 'all'){
                window.location.href = removeURLParameter(url,'type');
            }
        }

    </script>
    <!-- FooTable -->
    <script src="js/plugins/footable/footable.all.min.js"></script>
</body>

</html>
