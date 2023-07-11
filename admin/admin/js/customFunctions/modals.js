$(document).on("click", ".saveNotifyButt", function() {  force_sms_notify(this);});

function add_client(elementId,type,preSetId,cameFrom=''){
    if(cameFrom === 'fromHaircut'){
        var modalRenderInfos = {
            barberId: $('#selectedBarberId').html(),
            serviceId: $('#selectedServiceId').html(),
            appointment: $('#clientAppointmentDate').val(),
            note: $('#clientNoteInput').val(),
            execTime: $('#haircutTime').val(),
            haircutPrice: $('#haircutPrice').val(),
        };
        deleteCookie('prevModalInfos');
        setCookie('prevModalInfos',JSON.stringify(modalRenderInfos),'1');
    }
    window.scrollTo(0, 0);
    $('body').css('overflow','hidden');
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {
            action: 'gather_created_clients',
            type: type,
            preSetId: preSetId
        },
        dataType: "json",
         success: function (data) {
            var selectOptions = '';
            for (var i=0; i<=data.customers.length-1;i++){
                if(typeof data.selectedCustomer !=="undefined" && data.selectedCustomer.referrer === data.customers[i].id){
                    selectOptions +='<option selected value="'+ data.customers[i].id +'">'+ data.customers[i].name +' '+ data.customers[i].surname +'</option>';
                }else{
                    selectOptions +='<option value="'+ data.customers[i].id +'">'+ data.customers[i].name +' '+ data.customers[i].surname +'</option>';
                }
            }
            if(type === 'edit'){
                var preSetName = data.selectedCustomer.name;
                var preSetSurname = data.selectedCustomer.surname;
                var preSetPhone = data.selectedCustomer.phone;
                var preSetEmail = data.selectedCustomer.email;
                var preSetNote = data.selectedCustomer.note;
                var preSetReferrer = data.selectedCustomer.referrer;
            }else{
                if(cameFrom === 'fromHaircut'){
                    var preSetName = $('#modals > div > div.row > div:nth-child(2) > div > div > div > input[type=text]').val();
                    var preSetSurname = $('#modals > div > div.row > div:nth-child(2) > div > div > div > input[type=text]').val();
                }else{
                    var preSetName = '';
                    var preSetSurname = '';
                }
                var preSetPhone = '';
                var preSetEmail = '';
                var preSetNote = '';
                var preSetReferrer = '';

            }
             if(cameFrom !== 'fromHaircut'){
                 var closeButton = '<button id="customModal-shop-dam-info-button" class="customModal-shop-dam-info-button" data-clientid="'+ preSetId +'"><i class="fa fa-info-circle" aria-hidden="true"></i></button><button id="customModal-shop-dam-close-button" class="customModal-shop-dam-close-button"><i class="fa fa-times" aria-hidden="true"></i></button>';
             }else{
                 var closeButton = '<div style="height: 50px;"></div>';
             }
            var extraHtml = '<div class="newModal"><div class="modalIcon customFA fa fa-address-book" aria-hidden="true"></i></div>' +
                '        <div class="customModal-header" id="modalino">'+ type.toUpperCase() +' Client </div>';
            extraHtml +='<div class="customModal-close-container">' +closeButton+ '</div>';
            extraHtml += '<div class="row" style="margin:0px;margin-top: -50px;">' +
                '<div id="modalMainInfos"><div style="padding: 30px;" class="loaderCont"><span class="loader"></span></div></div>';
            extraHtml += '<div style="width: 100%;padding-left: 20px;">Client Infos</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;"><span style="color:white;">Name: </span> <input class="changeSize2" type="text" value="'+ preSetName +'" id="clientNameInput" placeholder="Input name here..." style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;    width: 85%;"></div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;"><span  style="color:white;">Surname: </span> <input class="changeSize2" type="text" value="'+ preSetSurname +'" id="clientSurnameInput" placeholder="Input surname here..." style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;    width: 80%;"></div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;"><span style="color:white;">Phone: </span> <input class="changeSize2" type="number" value="'+ preSetPhone +'" id="clientPhoneInput" placeholder="Input phone here..." style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;    width: 85%;"></div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;"><span style="color:white;">Email: </span> <input class="changeSize2" type="email" value="'+ preSetEmail +'" id="clientEmailInput" placeholder="Input email here..." style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;    width: 85%;"></div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;">' +
                '  <select style="background-color: #ff000000!important;color: white!important;font-size: 15px;padding-left: 10px;border: 0px;width: 80%;" data-placeholder="Select referrer User" class="chosen-select chosen-custom changeSize" name="form-referrerUser"  tabindex="2">' +
                '        <option value="0">Search referrer User</option>' +
                selectOptions    +
                '  </select>' +
                '</div>';
            extraHtml += '<div id="preSelectedId" style="display:none;">'+preSetId+'</div>';
            extraHtml += '<div id="selectedRefererId" style="display:none;">'+ preSetReferrer +'</div>';
            extraHtml += '<div style="width: 100%; margin-top: 30px;padding-left: 20px;">Write down a note for client</div>';
            extraHtml += '<textarea style="width: 100%;border-radius: 5px; height: 100px;text-align: start; color: white;" id="clientNoteInput"  class="customModal-shop-dam" placeholder="Write down note (optional)">'+ preSetNote +'</textarea>';
             if(type === 'edit'){
                 extraHtml += '<button class="customModal-shop-dam x2 flex-center standard" onclick="deactivateUser('+ preSetId +',\''+ elementId +'\')" data-clientId="'+ preSetId +'" style="margin-top: 30px;text-align: start;width: 45%;color: red;border: 1px solid red;">Deactivate client</button>';
                 extraHtml += '<div onclick="save_client(\''+type+'\',\''+elementId+'\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 45%;">Update client</div>';
             }else{
                 if(cameFrom === 'fromHaircut'){
                     extraHtml += '<div style="margin-top:30px;width: 100%;display: flex;"><div onclick="render_prev_modal(\'addHaircut\',\''+elementId+'\')" class="customModal-shop-dam x2 flex-center standard" style="text-align: start;width: 50%;"><i class="customFa fa fa-chevron-left" aria-hidden="true"></i>&nbsp;Back to haircut</div><div onclick="save_client(\''+type+'\',\''+elementId+'\',\'fromHaircut\')" class="customModal-shop-dam x2 flex-center standard" style="text-align: start;width: 50%;">Save new client</div></div>';
                 }else{
                     extraHtml += '<div onclick="save_client(\''+type+'\',\''+elementId+'\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 100%;">Save new client</div>';
                 }
             }
             extraHtml += '</div></div></div>';
             $('#'+elementId).html(extraHtml).css('display','flex');
             $('#customModal-shop-dam-close-button').on('click',function(){
                 $('body').css('overflow','auto');
                 $('.newModal').remove();
                 $('#'+elementId).css('display','none');
             });
             $('#customModal-shop-dam-info-button').on('click',function(){
                window.location.href = 'client?id='+ $(this).data('clientid');
             });
             $('.chosen-select').chosen({width: "100%"});
             $('.chosen-custom').on('change', function(evt, params) {
                     $('#selectedRefererId').html(params['selected']);
             });
         }});
}

function render_prev_modal(modalRenderType,elementId){
    if(modalRenderType === 'addHaircut'){
        $('#modalMainInfos').css('display','block');
        setTimeout(function(){
            add_haircut(elementId,'edit',0);
        },500);
    }
}

function add_haircut(elementId,type,preSetId,selectedDate) {
    window.scrollTo(0, 0);
    $('body').css('overflow', 'hidden');
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {
            action: 'gather_haircut_infos',
            type: type,
            preSetId: preSetId
        },
        dataType: "json",
        success: function (data) {
            if (type === 'edit' && preSetId > 0) {
                var preSetName = data.selectedCustomer.name;
                var preSetClientId = data.selectedCustomer;
                var preSetBarberId = data.selectedBarber;
                var preSetNote = data.haircutInfo.note;
                var preSetExTime = data.haircutInfo.executionTime;
                var preSetPrice = data.haircutInfo.commission;
                var preSetDatetime = data.haircutInfo.dateTimeExecuted;
                var preSetServices = data.haircutInfo.serviceId;
                var preSetDiscount = data.haircutInfo.discountPercentage;
            }else if (type === 'edit' && preSetId <= 0) {
                var modalPreRenderInfos = JSON.parse(getCookie('prevModalInfos'));
                for (let i = 0; i < data.customers.length; i++) {
                    if (data.customers[i].name === modalPreRenderInfos.clientName && data.customers[i].surname === modalPreRenderInfos.clientSurname) {
                        var preSetClientId = data.customers[i].id;
                    }
                }
                var preSetName = '';
                var preSetBarberId = modalPreRenderInfos.barberId;
                var preSetNote = modalPreRenderInfos.note;
                var preSetExTime = modalPreRenderInfos.execTime;
                var preSetPrice = modalPreRenderInfos.haircutPrice;
                var preSetDatetime = modalPreRenderInfos.appointment;
                var preSetServices = modalPreRenderInfos.serviceId;
                var preSetDiscount = modalPreRenderInfos.discountPercentage;
            } else {
                var preSetName = '';
                var preSetClientId = '';
                if (data.barbers.length > 1) {
                    var preSetBarberId = '';
                } else {
                    var preSetBarberId = data.barbers[0].id;
                }
                var preSetNote = '';
                var preSetExTime = '';
                var preSetPrice = '';
                var preSetDiscount = 0;
                if(selectedDate !== ''){
                    var preSetDatetime = selectedDate;
                }else{
                    var preSetDatetime = '';
                }
                var preSetServices = '["1"]';
            }
            var selectOptions = '';
            for (var i = 0; i <= data.customers.length - 1; i++) {
                if (data.customers[i].phone === '') {
                    var selPhone = '';
                    var staticPhone = '';

                } else {
                    var selPhone = ' ( ' + data.customers[i].phone + ' )';
                    var staticPhone = data.customers[i].phone;
                }
                if (typeof data.selectedCustomer !== "undefined" && data.selectedCustomer === data.customers[i].id || preSetClientId === data.customers[i].id) {
                    selectOptions += '<option selected value="' + data.customers[i].id + '" data-clientname="' + data.customers[i].name + '" data-clientsurname="' + data.customers[i].surname + '" data-phoneNum="'+ staticPhone +'">' + data.customers[i].name + ' ' + data.customers[i].surname + selPhone + '</option>';
                } else {
                    selectOptions += '<option data-clientname="' + data.customers[i].name + '" data-clientsurname="' + data.customers[i].surname + '" data-phoneNum="'+ staticPhone +'" value="' + data.customers[i].id + '">' + data.customers[i].name + ' ' + data.customers[i].surname + selPhone + '</option>';
                }
            }
            var selectOptionsServices = '';
            for (var i = 0; i <= data.services.length - 1; i++) {
                if (typeof data.selectedService !== "undefined") {
                    var found = false;
                    for (var pkk = 0; pkk <= data.selectedService.length - 1; pkk++) {
                        if (data.selectedService[pkk].id === data.services[i].id) {
                            found = true;
                            selectOptionsServices += '<option selected value="' + data.services[i].id + '">' + data.services[i].name + '</option>';
                        }
                    }
                    if (!found) {
                        selectOptionsServices += '<option value="' + data.services[i].id + '">' + data.services[i].name + '</option>';
                    }
                } else {
                    if (data.services[i].id === '1') {
                        selectOptionsServices += '<option selected value="' + data.services[i].id + '">' + data.services[i].name + '</option>';
                    } else {
                        selectOptionsServices += '<option value="' + data.services[i].id + '">' + data.services[i].name + '</option>';
                    }
                }
            }
            var selectOptionsBarbers = '';
            for (var i = 0; i <= data.barbers.length - 1; i++) {
                if (typeof data.selectedBarber !== "undefined" && data.selectedBarber === data.barbers[i].id || preSetBarberId === data.barbers[i].id) {
                    selectOptionsBarbers += '<option selected value="' + data.barbers[i].id + '">' + data.barbers[i].name + '</option>';
                } else {
                    selectOptionsBarbers += '<option value="' + data.barbers[i].id + '">' + data.barbers[i].name + '</option>';
                }
            }
            if (type === 'edit' && preSetId > 0) {
                var selectedType = type.toUpperCase();
            } else if (type === 'edit' && preSetId <= 0) {
                var selectedType = 'ADD';
            } else {
                var selectedType = 'ADD';
            }
            if (type === 'edit' && preSetId > 0){
                if(data.isSendedSMSManualNotification){
                    var forceSmSNotificationUi = '<div class="notifyButtCont"><div style="width: 140px;margin-left: auto;text-align: left;" class="customModal-shop-dam saveNotifyButt" data-appointmentid="'+preSetId+'"><button class="notifMessageButt">SMS Notify</button><i style="font-size: 50px;position: absolute;top: -2px;" class="customFA fa fa-mobile" aria-hidden="true"></i></div></div>';
                }else{
                    var forceSmSNotificationUi = '<div class="notifyButtCont disabledMessageNotify"><div style="width: 140px;margin-left: auto;text-align: left;" class="customModal-shop-dam"><button class="notifMessageButt">SMS Notify</button><i style="font-size: 50px;position: absolute;top: -2px;" class="customFA fa fa-mobile" aria-hidden="true"></i></div></div>';
                }
            }else{
                var forceSmSNotificationUi = '';
            }
            var extraHtml = '<div class="newModal"><div class="modalIcon customFA fa fa-scissors" aria-hidden="true"></i></div>' +
                '        <div class="customModal-header" id="modalino">' + selectedType + ' Haircut </div>';
            extraHtml += '<div class="customModal-close-container"><button id="customModal-shop-dam-info-button" class="customModal-shop-dam-info-button" data-clientid="'+ preSetId +'"><i class="fa fa-info-circle" aria-hidden="true"></i></button><button id="customModal-shop-dam-close-button" class="customModal-shop-dam-close-button"><i class="fa fa-times" aria-hidden="true"></i></button></div>';
            extraHtml += '<div class="row" style="margin:0px;margin-top: -50px;">';
            extraHtml += '<div style="width: 100%;padding-left: 20px;">Client Infos</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;">' +
                '  <select style="background-color: #ff000000!important;color: white!important;font-size: 15px;padding-left: 10px;border: 0px;width: 80%;" data-placeholder="Select Client" class="chosen-select chosen-custom changeSize chosen-client" name="form-client"  tabindex="2">' +
                '        <option value="0" id="0">Select Client</option>' +
                selectOptions +
                '  </select>' +
                '</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;">' +
                '  <select style="background-color: #ff000000!important;color: white!important;font-size: 15px;padding-left: 10px;border: 0px;width: 80%;" data-placeholder="Select Barber" class="chosen-select chosen-custom changeSize chosen-barber" name="form-barber"  tabindex="2">' +
                '        <option value="0" id="0">Select Barber</option>' +
                selectOptionsBarbers +
                '  </select>' +
                '</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;">' +
                '  <select style="background-color: #ff000000!important;color: white!important;font-size: 15px;padding-left: 10px;border: 0px;width: 80%;" data-placeholder="Select Service" multiple class="chosen-select chosen-custom changeSize chosen-service" name="form-service"  tabindex="2">' +
                '        <option value="0" disabled>Select Service</option>' +
                selectOptionsServices +
                '  </select>' +
                '</div>';
            extraHtml += '<div id="preSelectedId" style="display:none;">' + preSetId + '</div>';
            extraHtml += '<div id="selectedServiceId" style="display:none;">' + preSetServices + '</div>';
            extraHtml += '<div id="selectedBarberId" style="display:none;">' + preSetBarberId + '</div>';
            extraHtml += '<div id="selectedClientId" style="display:none;">' + preSetClientId + '</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;"><span style="color:white;">Appointment: </span> <input type="datetime-local" id="clientAppointmentDate" value="' + preSetDatetime + '" style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;width: 60%;"></div>';
            extraHtml += '<div style="width: 100%; margin-top: 10px;padding-left: 20px;" id="forceSmSNotificationUi">'+ forceSmSNotificationUi +'</div>';
            extraHtml += '<div style="width: 100%; margin-top: 10px;padding-left: 20px;">Write down a note for haircut</div>';
            extraHtml += '<textarea style="width: 100%;border-radius: 5px; height: 100px;text-align: start; color: white;" id="clientNoteInput"  class="customModal-shop-dam" placeholder="Write down note (optional)">' + preSetNote + '</textarea>';
            extraHtml += '<div style="width: 100%;margin-top: 30px;padding-left: 15px;margin-bottom: 10px;font-size: 18px;color: orange;">After Haircut infos</div>';
            extraHtml += '<div class="col-lg-12">';
            extraHtml += '  <div class="row" style="margin-left: 0px;">';
            extraHtml += '      <div style="width:33.3%;"><span style="color:white;">Execution Time: </span></div>';
            extraHtml += '      <div style="width:33.3%;"><span style="color:white;">Price: </span></div>';
            extraHtml += '      <div style="width:33.3%;"><span style="color:white;">% Discount: </span></div>';
            extraHtml += '  </div>';
            extraHtml += '  <div class="row">';
            extraHtml += '      <div class="customModal-shop-dam" style="text-align: start;width:27%;border: 1px solid darkorange;"> <input type="number" value="' + preSetExTime + '" id="haircutTime" placeholder="Minutes" style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;width: 100%;"></div>';
            extraHtml += '      <div class="customModal-shop-dam" style="text-align: start;width:27%;border: 1px solid darkorange;"> <input type="number" value="' + preSetPrice + '" id="haircutPrice" placeholder="Eur" style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;width: 100%;"></div>';
            extraHtml += '      <div class="customModal-shop-dam" style="text-align: start;width:27%;border: 1px solid darkorange;"> <input type="number" min="0" max="100" value="' + preSetDiscount + '" id="haircutDiscount" placeholder="% disc" style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;width: 100%;"></div>';
            extraHtml += '  </div>';
            extraHtml += '</div>';
            if (type === 'edit' && preSetId > 0) {
                extraHtml += '<button class="customModal-shop-dam x2 flex-center standard" onclick="deactivateHaircut(' + preSetId + ',\'' + elementId + '\',\''+ preSetBarberId +'\',\''+ preSetClientId +'\')" data-clientId="' + preSetId + '" style="margin-top: 30px;text-align: start;width: 45%;color: red;border: 1px solid red;">Deactivate Haircut</button>';
                extraHtml += '<div onclick="save_haircut(\'' + type + '\',\'' + elementId + '\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 45%;">Update haircut</div>';
            } else if (type === 'edit' && preSetId <= 0) {
                extraHtml += '<div onclick="save_haircut(\'add\',\'' + elementId + '\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 100%;">Save new haircut</div>';
            } else {
                extraHtml += '<div onclick="save_haircut(\'' + type + '\',\'' + elementId + '\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 100%;">Save new haircut</div>';
            }
            extraHtml += '</div></div></div>';
            $('#' + elementId).html(extraHtml).css('display', 'flex');
            $('#customModal-shop-dam-close-button').on('click', function () {
                $('body').css('overflow', 'auto');
                $('.newModal').remove();
                $('#' + elementId).css('display', 'none');
            });
            $('.chosen-client').chosen({
                width: "100%",
                no_results_text: '<button type="button" onclick="add_client(\'modals\',\'add\',\'\',\'fromHaircut\')" class="x2 flex-center standard lada-save btn btn-primary btn btn-primary btn-sm btn-block saveChanges" style="font-size: 20px;" data-style="expand-right"><i class="fa  fa-address-book" aria-hidden="true"></i>&nbsp;Add Client</button></div>Client not found: '
            });
            $('.chosen-barber').chosen({width: "100%"});
            if (type === 'edit' && preSetId <= 0) {
                var preSetServices2 = JSON.parse(preSetServices);
                $('.chosen-service').chosen({width: "100%", multiple: true});
                $('.chosen-service').val(preSetServices2).trigger("chosen:updated");
            } else {
                $('.chosen-service').chosen({width: "100%"});
            }
            $('.chosen-service').on('change', function (evt, params) {
                $('#selectedServiceId').html('');
                $('.search-choice span').map(function (selectedServiceId, totalServices, i, p) {
                    var found = totalServices.find(function (selectedServiceName, serviceObj) {
                        return selectedServiceName === serviceObj.name;
                    }.bind(null, p.innerText));
                    var previewsText = $('#selectedServiceId').html();
                    if (previewsText === '') {
                        $('#selectedServiceId').html('"' + found.id + '"');
                    } else {
                        $('#selectedServiceId').html(previewsText + ',"' + found.id + '"');
                    }
                }.bind(null, params['selected'], data.services));
                $('#selectedServiceId').html('[' + $('#selectedServiceId').html() + ']');
            });
            $('.chosen-barber').on('change', function (evt, params) {
                $('#selectedBarberId').html(params['selected']);
            });
            $('.chosen-client').on('change', function (evt, params) {
                $('#selectedClientId').html(params['selected']);
            });
            $("#haircutDiscount").on("input", function() {
                let inputNumber = parseInt($(this).val());
                if (isNaN(inputNumber)) {
                    $(this).val(1);
                } else if (inputNumber < 1) {
                    $(this).val(1);
                } else if (inputNumber > 100) {
                    $(this).val(100);
                } else if (inputNumber < 10 && $(this).val().length == 2) {
                    $(this).val(inputNumber);
                }
            });
        }
    });
}

function deactivateUser(userId,elementId){
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {action: 'deactivateCustomer', customerId:userId },
        dataType: "json",
        success: function (data) {
            $('.newModal').remove();
            $('#'+elementId).css('display','none');
             window.location.reload();
        }
    });
}

function deactivateHaircut(haircutId,elementId,barberId,clientId){
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {
            action: 'deactivateHaircut',
            haircutId:haircutId,
            barberId: barberId,
            clientId: clientId,
        },
        dataType: "json",
        success: function (data) {
            $('.newModal').remove();
            $('#'+elementId).css('display','none');
            if(window.location.pathname === '/admin/index'){
                calendar_render();
                gather_recent_appointments(18);
            }else{
                window.location.reload();
            }
        }
    });
}

function save_client(type,elementId,cameFrom){
    var name = $('#clientNameInput').val();
    var surname = $('#clientSurnameInput').val();
    var oldCookie = JSON.parse(getCookie('prevModalInfos'));
    deleteCookie('prevModalInfos');
    oldCookie['clientName'] = name;
    oldCookie['clientSurname'] = surname;
    setCookie('prevModalInfos',JSON.stringify(oldCookie),'1');
    var phone = $('#clientPhoneInput').val();
    var note = $('#clientNoteInput').val();
    var email = $('#clientEmailInput').val();
    var referer = $('#selectedRefererId').html();
    if(type === 'edit'){
        var preSetId = $('#preSelectedId').html();
    }else{
        var preSetId = 0;
    }
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {
            action: 'save_update_client',
            type: type,
            name: name,
            surname: surname,
            phone: phone,
            note: note,
            email: email,
            referer: referer,
            preSetId:preSetId
        },
        dataType: "json",
        success: function (data) {
            if(cameFrom === 'fromHaircut'){
                render_prev_modal('addHaircut',elementId);
            }else{
                $('.newModal').remove();
                $('#'+elementId).css('display','none');
                const queryString = window.location.search;
                const urlParams = new URLSearchParams(queryString);
                var previwePage = urlParams.get('short');
                swal({
                    title: "Successfully Saved New Client",
                    type: "success",
                    showCancelButton: false,
                    showConfirmButton: true,
                    showConfirmButtonText: 'OK',
                    customClass: "customSwallClass"
                },function(){
                    if(previwePage === 'clients'){
                        window.location.reload();
                    }
                });
            }
        }
    });
}

function save_haircut(type,elementId){
    var clientId = $('#selectedClientId').html();
    var barberId = $('#selectedBarberId').html();
    var serviceId = $('#selectedServiceId').html();
    var appointment = $('#clientAppointmentDate').val();
    var note = $('#clientNoteInput').val();
    var execTime = $('#haircutTime').val();
    var haircutPrice = $('#haircutPrice').val();
    var haircutDiscount = $('#haircutDiscount').val();
    if(type === 'edit'){
        var preSetId = $('#preSelectedId').html();
    }else{
        var preSetId = 0;
    }
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {
            action: 'save_update_haircut',
            type: type,
            clientId: clientId,
            barberId: barberId,
            serviceId: JSON.parse(serviceId),
            appointment: appointment,
            note: note,
            execTime: execTime,
            haircutPrice:haircutPrice,
            preSetId: preSetId,
            haircutDiscount: haircutDiscount
        },
        dataType: "json",
        success: function (data) {
            $('.newModal').remove();
            $('#'+elementId).css('display','none');
            if(window.location.pathname === '/admin/index'){
                calendar_render();
                gather_recent_appointments(18);
            }else{
                window.location.reload();
            }
        }
    });
}

function update_date_haircut(date,preSetId){
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {
            action: 'save_update_haircut',
            type: 'update_date',
            appointment: date,
            preSetId: preSetId
        },
        dataType: "json",
        success: function (data) {
          if(data.response){
              toastr.success('You have successfully changed haircut date', 'Haircut Update');
              gather_recent_appointments(18);
          }else{
              toastr.error('Error on updating haircut please try again', 'Haircut Update');
          }
        }
    });
}

function force_sms_notify(){
    var preSetId = $('.saveNotifyButt').data("appointmentid");
    var selectedOption = $('.chosen-client option:selected');
    var data = selectedOption.data();
    swal({
        title: "Are you sure you want to notify "+data['clientname']+" with SMS?",
        text: "You are about to notify "+data['clientname']+" with SMS on: "+data['phonenum'],
        type: "warning",
        showCancelButton: true,
        showConfirmButton: true,
        showConfirmButtonText: 'Send SMS',
        customClass: "customSwallClass"
    },function(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {
                action: 'force_sms_notify',
                preSetId: preSetId
            },
            dataType: "json",
            success: function (data) {
                if(data.response === true){
                    swal({
                        title: data.customerInfos.name + " received your sms notification",
                        type: "success",
                        showCancelButton: false,
                        showConfirmButton: true,
                        showConfirmButtonText: 'OK',
                        customClass: "customSwallClass"
                    },function(){
                        var forceSmSNotificationUi = '<div class="notifyButtCont disabledMessageNotify"><div style="width: 140px;margin-left: auto;text-align: left;" class="customModal-shop-dam"><button class="notifMessageButt">SMS Notify</button><i style="font-size: 50px;position: absolute;top: -2px;" class="customFA fa fa-mobile" aria-hidden="true"></i></div></div>';
                        $('#forceSmSNotificationUi').html(forceSmSNotificationUi);
                    });
                }else{
                     swal({
                        title: "Error on sending sms notification",
                        type: "error",
                        showCancelButton: false,
                        showConfirmButton: true,
                        showConfirmButtonText: 'OK',
                        customClass: "customSwallClass"
                    });
                }
            }
        });
    });
}


