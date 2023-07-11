window.addEventListener("orientationchange", function() {
    if (screen.orientation.type.includes("landscape")) {
        $('body').css('overflow','hidden');
        document.getElementById("landscape-alert").style.display = "block";
    }else{
        $('body').css('overflow','auto');
        document.getElementById("landscape-alert").style.display = "none";
    }
});

function add_client(elementId,type,preSetId){
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
            console.log(data);
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
                var preSetName = '';
                var preSetSurname = '';
                var preSetPhone = '';
                var preSetEmail = '';
                var preSetNote = '';
                var preSetReferrer = '';

            }
            var extraHtml = '<div class="newModal"><div class="modalIcon customFA fa fa-address-book" aria-hidden="true"></i></div>' +
                '        <div class="customModal-header" id="modalino">'+ type.toUpperCase() +' Client </div>';
            extraHtml +='<div class="customModal-close-container"><button id="customModal-shop-dam-close-button" class="customModal-shop-dam-close-button"><i class="fa fa-times" aria-hidden="true"></i></button></div>';
            extraHtml += '<div class="row" style="margin:0px;margin-top: -50px;">';
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
                 extraHtml += '<div onclick="save_client(\''+type+'\',\''+elementId+'\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 100%;">Save new client</div>';
             }
            extraHtml += '</div></div></div>';
            $('#'+elementId).html(extraHtml).css('display','flex');
            $('#customModal-shop-dam-close-button').on('click',function(){
                $('body').css('overflow','auto');
                $('.newModal').remove();
                $('#'+elementId).css('display','none');
            });
             $('.chosen-select').chosen({width: "100%"});
             $('.chosen-custom').on('change', function(evt, params) {
                     $('#selectedRefererId').html(params['selected']);
             });
         }});
}

function add_haircut(elementId,type,preSetId){
    window.scrollTo(0, 0);
    $('body').css('overflow','hidden');
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
            console.log(data);
            if(type === 'edit'){
                var preSetName = data.selectedCustomer.name;
                var preSetClientId = data.selectedCustomer;
                var preSetBarberId = data.selectedBarber;
                var preSetNote = data.haircutInfo.note;
                var preSetExTime = data.haircutInfo.executionTime;
                var preSetPrice = data.haircutInfo.commission;
                var preSetDatetime = data.haircutInfo.dateTimeExecuted;
                var preSetServices = data.haircutInfo.serviceId;
            }else{
                var preSetName = '';
                var preSetClientId = '';
                var preSetBarberId = '';
                var preSetNote = '';
                var preSetExTime = '';
                var preSetPrice = '';
                var preSetDatetime = '';
                var preSetServices = '["1"]';
            }

            var selectOptions = '';
            for (var i=0; i<=data.customers.length-1;i++){
                if(data.customers[i].phone === ''){
                    var selPhone = '';
                }else{
                    var selPhone = '('+ data.customers[i].phone +')';
                }
                if(typeof data.selectedCustomer !=="undefined" && data.selectedCustomer === data.customers[i].id){
                    selectOptions +='<option selected value="'+ data.customers[i].id +'">'+ data.customers[i].name +' '+ data.customers[i].surname + selPhone +'</option>';
                }else{
                    selectOptions +='<option value="'+ data.customers[i].id +'">'+ data.customers[i].name +' '+ data.customers[i].surname + selPhone + '</option>';
                }
            }
            var selectOptionsServices = '';
            for (var i=0; i<=data.services.length-1;i++){
                if(typeof data.selectedService !=="undefined"){
                    var found = false;
                    for (var pkk = 0;pkk<=data.selectedService.length-1;pkk++){
                        if(data.selectedService[pkk].id === data.services[i].id){
                            found = true;
                            selectOptionsServices +='<option selected value="'+ data.services[i].id +'">'+ data.services[i].name +'</option>';
                        }
                    }
                    if(!found){
                        selectOptionsServices +='<option value="'+ data.services[i].id +'">'+ data.services[i].name +'</option>';
                    }
                }else{
                    if(data.services[i].id === '1'){
                        selectOptionsServices +='<option selected value="'+ data.services[i].id +'">'+ data.services[i].name +'</option>';
                    }else{
                        selectOptionsServices +='<option value="'+ data.services[i].id +'">'+ data.services[i].name +'</option>';
                    }
                }
            }
            var selectOptionsBarbers = '';
            for (var i=0; i<=data.barbers.length-1;i++){
                if(typeof data.selectedBarber !=="undefined" && data.selectedBarber === data.barbers[i].id){
                    selectOptionsBarbers +='<option selected value="'+ data.barbers[i].id +'">'+ data.barbers[i].name +'</option>';
                }else{
                    selectOptionsBarbers +='<option value="'+ data.barbers[i].id +'">'+ data.barbers[i].name +'</option>';
                }
            }
            var extraHtml = '<div class="newModal"><div class="modalIcon customFA fa fa-scissors" aria-hidden="true"></i></div>' +
                '        <div class="customModal-header" id="modalino">'+ type.toUpperCase() +' Haircut </div>';
            extraHtml +='<div class="customModal-close-container"><button id="customModal-shop-dam-close-button" class="customModal-shop-dam-close-button"><i class="fa fa-times" aria-hidden="true"></i></button></div>';
            extraHtml += '<div class="row" style="margin:0px;margin-top: -50px;">';
            extraHtml += '<div style="width: 100%;padding-left: 20px;">Client Infos</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;">' +
                '  <select style="background-color: #ff000000!important;color: white!important;font-size: 15px;padding-left: 10px;border: 0px;width: 80%;" data-placeholder="Select Client" class="chosen-select chosen-custom changeSize chosen-client" name="form-client"  tabindex="2">' +
                '        <option value="0" id="0">Select Client</option>' +
                selectOptions    +
                '  </select>' +
                '</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;">' +
                '  <select style="background-color: #ff000000!important;color: white!important;font-size: 15px;padding-left: 10px;border: 0px;width: 80%;" data-placeholder="Select Barber" class="chosen-select chosen-custom changeSize chosen-barber" name="form-barber"  tabindex="2">' +
                '        <option value="0" id="0">Select Barber</option>' +
                selectOptionsBarbers    +
                '  </select>' +
                '</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;">' +
                '  <select style="background-color: #ff000000!important;color: white!important;font-size: 15px;padding-left: 10px;border: 0px;width: 80%;" data-placeholder="Select Service" multiple class="chosen-select chosen-custom changeSize chosen-service" name="form-service"  tabindex="2">' +
                '        <option value="0" disabled>Select Service</option>' +
                selectOptionsServices    +
                '  </select>' +
                '</div>';
            extraHtml += '<div id="preSelectedId" style="display:none;">'+preSetId+'</div>';
            extraHtml += '<div id="selectedServiceId" style="display:none;">'+ preSetServices +'</div>';
            extraHtml += '<div id="selectedBarberId" style="display:none;">'+ preSetBarberId +'</div>';
            extraHtml += '<div id="selectedClientId" style="display:none;">'+ preSetClientId +'</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;"><span style="color:white;">Appointment: </span> <input type="datetime-local" id="clientAppointmentDate" value="'+ preSetDatetime +'" style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;width: 60%;"></div>';
            extraHtml += '<div style="width: 100%; margin-top: 30px;padding-left: 20px;">Write down a note for haircut</div>';
            extraHtml += '<textarea style="width: 100%;border-radius: 5px; height: 100px;text-align: start; color: white;" id="clientNoteInput"  class="customModal-shop-dam" placeholder="Write down note (optional)">'+ preSetNote +'</textarea>';
            extraHtml += '<div style="width: 100%; margin-top: 30px;padding-left: 20px;">After Haircut infos</div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;width:45%;border: 1px solid darkorange;"><span style="color:white;">Exc.Time: </span> <input type="number" value="'+ preSetExTime +'" id="haircutTime" placeholder="Minutes" style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;    width: 43%;"></div>';
            extraHtml += '<div class="customModal-shop-dam" style="text-align: start;width:45%;border: 1px solid darkorange;"><span style="color:white;">Price: </span> <input type="number" value="'+ preSetPrice +'" id="haircutPrice" placeholder="Eur" style="background-color: #ff000000;color:white;font-size: 15px;padding-left: 10px;    width: 40%;"></div>';
            if(type === 'edit'){
                extraHtml += '<button class="customModal-shop-dam x2 flex-center standard" onclick="deactivateHaircut('+ preSetId +',\''+ elementId +'\')" data-clientId="'+ preSetId +'" style="margin-top: 30px;text-align: start;width: 45%;color: red;border: 1px solid red;">Deactivate Haircut</button>';
                extraHtml += '<div onclick="save_haircut(\''+type+'\',\''+elementId+'\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 45%;">Update haircut</div>';
            }else{
                extraHtml += '<div onclick="save_haircut(\''+type+'\',\''+elementId+'\')" class="customModal-shop-dam x2 flex-center standard" style="margin-top: 30px;text-align: start;width: 100%;">Save new haircut</div>';
            }
            extraHtml += '</div></div></div>';
            $('#'+elementId).html(extraHtml).css('display','flex');
            $('#customModal-shop-dam-close-button').on('click',function(){
                $('body').css('overflow','auto');
                $('.newModal').remove();
                $('#'+elementId).css('display','none');
            });
            $('.chosen-select').chosen({width: "100%"});
            $('.chosen-service').on('change', function(evt, params) {
                $('#selectedServiceId').html('');
                $('.search-choice span').map(function(selectedServiceId,totalServices,i,p){
                    var found = totalServices.find(function(selectedServiceName,serviceObj){
                        return selectedServiceName === serviceObj.name;
                    }.bind(null,p.innerText));
                    var previewsText = $('#selectedServiceId').html();
                    if(previewsText === ''){
                        $('#selectedServiceId').html('"'+found.id+'"');
                    }else{
                        $('#selectedServiceId').html(previewsText+',"'+found.id+'"');
                    }
                }.bind(null,params['selected'],data.services));
                $('#selectedServiceId').html('['+$('#selectedServiceId').html()+']');
            });
            $('.chosen-barber').on('change', function(evt, params) {
                $('#selectedBarberId').html(params['selected']);
            });
            $('.chosen-client').on('change', function(evt, params) {
                $('#selectedClientId').html(params['selected']);
            });
        }});
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

function deactivateHaircut(haircutId,elementId){
    $.ajax({
        url: "AJAXPOSTS",
        type: "POST",
        data: {action: 'deactivateHaircut', haircutId:haircutId },
        dataType: "json",
        success: function (data) {
            $('.newModal').remove();
            $('#'+elementId).css('display','none');
             window.location.reload();
        }
    });
}

function save_client(type,elementId){
    var name = $('#clientNameInput').val();
    var surname = $('#clientSurnameInput').val();
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
            $('.newModal').remove();
            $('#'+elementId).css('display','none');
            window.location.reload();
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
            preSetId: preSetId
        },
        dataType: "json",
        success: function (data) {
            $('.newModal').remove();
            $('#'+elementId).css('display','none');
            window.location.href = '/admin/index';
        }
    });
}


