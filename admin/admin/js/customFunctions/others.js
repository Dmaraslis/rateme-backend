    function unpack(str) {
        var bytes = [];
        var meter = 0;
        for(var i = 0; i < str.length; i++) {
            var char = str.charCodeAt(i);
            bytes.push(char >>> 8);
            bytes.push(char & 0xFF);
            meter = meter + (char >>> 8);
            meter = meter + (char & 0xFF);
        }
        return meter;
    }

    function load1stGraph(type,fromMonth,toMonth) {
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'gather1stGraph', date: fromMonth},
            dataType: "json",
            success: function (data) {
                $('.dailyEurProf').html(data.response.dailyProfit+' €');
                var sumActiveMonths = data.response.extraData.sumActiveMonths;
                if (fromMonth === sumActiveMonths - 1) {
                    $('#graphPreviousMonth').css('display', 'none');
                } else {
                    $('#graphPreviousMonth').css('display', 'block');
                }
                if ($(window).width() < 769) {
                    $('#MonthDisplay').css('font-size', '10px').css('top', '40px');
                    $('#graphNextMonth').css('margin', '0px');
                    $('#graphPreviousMonth').css('margin', '0px');
                }
                $('#graphMonthRange').html('');
                $('#graphMonthRange').append('<input type="text" id="graphFromMonth" value="' + fromMonth + '" style="display: none;">');
                $('#graphMonthRange').append('<input type="text" id="graphToMonth" value="' + toMonth + '" style="display: none;">');
                if (fromMonth === 0) {
                    $('#graphNextMonth').css('display', 'none');
                } else {
                    $('#graphNextMonth').css('display', 'block');
                }
                var SumCompletedMonthlyHaircuts = data.response.dato.monthlyAppointmentsCount;
                var currentMonth = data.response.dato.month;
                var SumAnnualOrders = data.response.extraData.sumCompletedOrders;
                var sumDays = data.response.dato.monthDays;
                var sumEurProf = data.response.dato.sumEurProfit;
                var maxHaircutsOnDay = data.response.dato.maxHaircutsOnDay;
                var maxProfitOnDay = data.response.dato.maxProfitOnDay;
                var maxSpendedTimeOnDay = data.response.dato.maxSpendedTimeOnDay;
                var days = data.response.dato.dayAnalysis;
                var avProfPerHour = data.response.dato.hourAvProf;
                $('#MonthDisplay').html('Preview Range: 01-' + sumDays + ' ' + currentMonth);
                if(data.response.prevDato){
                    var prevSumCompletedMonthlyHaircuts = data.response.prevDato.monthlyAppointmentsCount;
                    var prevMonth = data.response.prevDato.month;
                    var prevsumEurProf = data.response.prevDato.sumEurProfit;
                    var prevmaxHaircutsOnDay = data.response.prevDato.maxHaircutsOnDay;
                    var prevmaxProfitOnDay = data.response.prevDato.maxProfitOnDay;
                    var prevmaxSpendedTimeOnDay = data.response.prevDato.maxSpendedTimeOnDay;
                    var prevdays = data.response.prevDato.dayAnalysis;
                    var prevAvProfPerHour = data.response.prevDato.hourAvProf
                }else{
                    var prevSumCompletedMonthlyHaircuts = 0;
                    var prevMonth = 0;
                    var prevsumEurProf = 0;
                    var prevmaxHaircutsOnDay = 0;
                    var prevmaxProfitOnDay = 0;
                    var prevmaxSpendedTimeOnDay = 0;
                    var prevdays = 0;
                    var prevAvProfPerHour = 0;
                }
                /* annual orders infos START */
                $('.totalOrders').html(SumAnnualOrders);
                var percentageOfTotal = (SumAnnualOrders / 50000 * 100).toFixed(2);
                $('.totalOrdersByGoal').html(percentageOfTotal + '%');
                $('.totalOrdersByGoalBar').css('width', percentageOfTotal + '%');
                /* annual orders infos END */
                /* total monhly orders infos START */
                $('.totalMonthlyOrders').html(SumCompletedMonthlyHaircuts);
                var percentageOfMonthly = ((prevSumCompletedMonthlyHaircuts / prevSumCompletedMonthlyHaircuts * 100) - (SumCompletedMonthlyHaircuts / prevSumCompletedMonthlyHaircuts * 100)).toFixed(2);
                if (percentageOfMonthly === 'NaN'){
                    percentageOfMonthly = 0;
                }
                if (SumCompletedMonthlyHaircuts < prevSumCompletedMonthlyHaircuts) {
                    $('.totalMonthlyOrdersByPrev').html(percentageOfMonthly + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.totalMonthlyOrdersByPrevBar').css('background-color', 'red');
                    $('.totalMonthlyOrdersByPrevBar').css('width', percentageOfMonthly + '%');
                } else if (SumCompletedMonthlyHaircuts > prevSumCompletedMonthlyHaircuts) {
                    $('.totalMonthlyOrdersByPrev').html(Math.abs(percentageOfMonthly) + '% <i class="fa fa-level-up text-navy"></i>').css('color','#0dda92');
                    $('.totalMonthlyOrdersByPrevBar').css('width', Math.abs(percentageOfMonthly) + '%').css('background-color','#0dda92');
                }
                /* total monhly orders infos END */
                /* Max Order On One Day infos START*/
                $('.maxOrdersOnOneDay').html(maxHaircutsOnDay);
                var percentageOfMaxOrdersOnADay = ((prevmaxHaircutsOnDay / prevmaxHaircutsOnDay * 100) - (maxHaircutsOnDay / prevmaxHaircutsOnDay * 100)).toFixed(2);
                if (percentageOfMaxOrdersOnADay === 'NaN'){
                    percentageOfMaxOrdersOnADay = 0;
                }
                if (maxHaircutsOnDay < prevmaxHaircutsOnDay) {
                    $('.maxOrdersOnOneDayByPrev').html(percentageOfMaxOrdersOnADay + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.maxOrdersOnOneDayByPrevBar').css('background-color', 'red');
                    $('.maxOrdersOnOneDayByPrevBar').css('width', percentageOfMaxOrdersOnADay + '%');
                } else if (maxHaircutsOnDay > prevmaxHaircutsOnDay){
                    $('.maxOrdersOnOneDayByPrev').html(Math.abs(percentageOfMaxOrdersOnADay) + '% <i class="fa fa-level-up text-navy"></i>').css('color','#0dda92');
                    $('.maxOrdersOnOneDayByPrevBar').css('width', Math.abs(percentageOfMaxOrdersOnADay) + '%').css('background-color','#0dda92');
                }
                /* Max Order On One Day infos END*/
                /* Max Completed Order On One Day infos infos WIND */
                $('.maxCompleteOrdersOnOneDay').html(maxProfitOnDay);
                var percentageOfMaxCompleteOrdersOnADay = ((prevmaxProfitOnDay / prevmaxProfitOnDay * 100) - (maxProfitOnDay / prevmaxProfitOnDay * 100)).toFixed(2);
                if (percentageOfMaxCompleteOrdersOnADay === 'NaN'){
                    percentageOfMaxCompleteOrdersOnADay = 0;
                }
                if (maxProfitOnDay < prevmaxProfitOnDay) {
                    $('.maxCompleteOrdersOnOneDayByPrev').html(percentageOfMaxCompleteOrdersOnADay + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.maxCompleteOrdersOnOneDayByPrevBar').css('background-color', 'red');
                    $('.maxCompleteOrdersOnOneDayByPrevBar').css('width', percentageOfMaxCompleteOrdersOnADay + '%');
                } else if (maxProfitOnDay > prevmaxProfitOnDay) {
                    $('.maxCompleteOrdersOnOneDayByPrev').html(Math.abs(percentageOfMaxCompleteOrdersOnADay) + '% <i class="fa fa-level-up text-navy"></i>').css('color','#0dda92');
                    $('.maxCompleteOrdersOnOneDayByPrevBar').css('width', Math.abs(percentageOfMaxCompleteOrdersOnADay) + '%').css('background-color','#0dda92');
                }
                /* Max Completed Order On One Day infos infos END*/
                /* Annual Euro Profit START */
                $('.annualEurProf').html(parseFloat(sumEurProf).toFixed(2) + '€');
                var percentageOfannualEurProf = ((parseFloat(prevsumEurProf) / parseFloat(prevsumEurProf) * 100) - (parseFloat(sumEurProf) / parseFloat(prevsumEurProf) * 100)).toFixed(2);
                if (percentageOfannualEurProf === 'NaN'){
                    percentageOfannualEurProf = 0;
                }
                if (sumEurProf < prevsumEurProf) {
                    $('.annualEurProfPrev').html(percentageOfannualEurProf + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.annualEurProfPrevBar').css('background-color', 'red');
                    $('.annualEurProfPrevBar').css('width', percentageOfannualEurProf + '%');
                } else if(sumEurProf > prevsumEurProf) {
                    $('.annualEurProfPrev').html(Math.abs(percentageOfannualEurProf) + '% <i class="fa fa-level-up text-navy"></i>').css('color','#0dda92');
                    $('.annualEurProfPrevBar').css('width', Math.abs(percentageOfannualEurProf) + '%').css('background-color','#0dda92');
                }
                /* Annual Euro Profit END*/
                /* Annual Time Profit START */
                $('.monthlyAvEurProf').html(parseFloat(avProfPerHour).toFixed(2) + "€");
                var percentageOfAvEurProf = ((prevAvProfPerHour / prevAvProfPerHour * 100) - (avProfPerHour / prevAvProfPerHour * 100)).toFixed(2);
                if (percentageOfAvEurProf === 'NaN'){
                    percentageOfAvEurProf = 0;
                }
                if (avProfPerHour < prevAvProfPerHour) {
                    $('.monthlyAvEurProfPrev').html(percentageOfAvEurProf + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.monthlyAvEurProfPrevBar').css('background-color', 'red');
                    $('.monthlyAvEurProfPrevBar').css('width', percentageOfAvEurProf + '%');
                } else if(avProfPerHour > prevAvProfPerHour) {
                    $('.monthlyAvEurProfPrev').html(Math.abs(percentageOfAvEurProf) + '% <i class="fa fa-level-up text-navy"></i>').css('color','#0dda92');
                    $('.monthlyAvEurProfPrevBar').css('width', Math.abs(percentageOfAvEurProf) + '%').css('background-color','#0dda92');
                }
                /* Annual Euro Profit END*/

                /* Annual Time Profit START */
                $('.annualTimeSpent').html(parseFloat(maxSpendedTimeOnDay).toFixed(0) + "'");
                var percentageOfannualEurProf = ((prevmaxSpendedTimeOnDay / prevmaxSpendedTimeOnDay * 100) - (maxSpendedTimeOnDay / prevmaxSpendedTimeOnDay * 100)).toFixed(2);
                if (percentageOfannualEurProf === 'NaN'){
                    percentageOfannualEurProf = 0;
                }
                if (maxSpendedTimeOnDay < prevmaxSpendedTimeOnDay) {
                    $('.annualTimeSpentPrev').html(percentageOfannualEurProf + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.annualTimeSpentPrevBar').css('background-color', 'red');
                    $('.annualTimeSpentPrevBar').css('width', percentageOfannualEurProf + '%');
                } else if (maxSpendedTimeOnDay > prevmaxSpendedTimeOnDay) {
                    $('.annualTimeSpentPrev').html(Math.abs(percentageOfannualEurProf) + '% <i class="fa fa-level-up text-navy"></i>');
                    $('.annualTimeSpentPrevBar').css('width', Math.abs(percentageOfannualEurProf) + '%').css('background-color','#0dda92');
                }
                /* Annual Euro Profit END*/
                var data3 = [];
                var data2 = [];
                var data4 = [];
                for (var i = 0; i <= sumDays-1; i++) {
                    var preData3 = [gd(days[i].date), days[i].sumTime];
                    var preData2 = [gd(days[i].date), days[i].sumHaircuts];
                    var preData4 = [gd(days[i].date), days[i].sumEur];
                    data3.push(preData3);
                    data2.push(preData2);
                    data4.push(preData4);
                }

                var dataset = [
                    {
                        label: "Time Spent",
                        data: data3,
                        color: "#1d1d1d",
                        bars: {
                            show: true,
                            align: "left",
                            barWidth: 24 * 60 * 60 * 600,
                            lineWidth: 1
                        }

                    }, {
                        label: "Haircuts",
                        data: data2,
                        yaxis: 2,
                        color: "#0caa71",
                        lines: {
                            lineWidth: 1,
                            show: true,
                            fill: true,
                            fillColor: {
                                colors: [{
                                    opacity: 0.4
                                }, {
                                    opacity: 1
                                }]
                            }
                        }
                    },{
                        label: "€ Profit",
                        data: data4,
                        yaxis: 3,
                        color: "#181818",
                        points: { symbol: "triangle", fillColor: "#ffeff6", show: true },
                        lines: { show: true }
                    }
                ];

                var metrics = (300 / maxHaircutsOnDay) / 2;
                if (metrics >= maxHaircutsOnDay) {
                    var titick = 2;
                } else if (metrics <= 20) {
                    var titick = 1;
                }

                var options = {
                    xaxis: {
                        mode: "time",
                        tickSize: [2, "day"],
                        tickLength: 0,
                        axisLabel: "Date",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: 'Arial',
                        axisLabelPadding: 10,
                        color: "#d5d5d5"
                    },
                    yaxes: [{
                        mode: "count",
                        tickSize: [15],
                        tickLength: 10,
                        axisLabel: "count",
                        position: "left",
                        max: Math.round(maxHaircutsOnDay + 2),
                        color: "#1d1d1d",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: 'Arial',
                        axisLabelPadding: 3
                    },{
                        mode: "count",
                        tickSize: [0],
                        tickLength: 10,
                        axisLabel: "count",
                        position: "right",
                        color: "#1d1d1d",
                        max: Math.round(maxHaircutsOnDay + 2),
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: ' Arial',
                        axisLabelPadding: 3
                    },{
                        position: "right",
                        color: "#1d1d1d",
                        axisLabel: "Euro Profit",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: 'Verdana, Arial',
                        axisLabelPadding: 3
                    }
                    ],
                    legend: {
                        noColumns: 2,
                        labelBoxBorderColor: "#2d2d2d",
                        position: "nw",
                        backgroundColor: null
                    },
                    grid: {
                        color: "#ffffff",
                        clickable: true,
                        tickColor: "#2f4050",
                        borderWidth: 0,
                        hoverable: true,
                        backgroundColor: "#2d2d2d"

                    },
                    tooltip: true,
                    tooltipOpts: {
                        content: function (data, x, y, dataObject) {
                            return "<p style='color:white;border:1px solid white;border-radius:5px;background-color: #1d1d1d'>%y %s for %x </p>";
                        },
                        xDateFormat: "%d/%m",
                        defaultTheme: false,
                        shifts: {x: 0, y: -40}
                    }
                };

                function getTooltip(label, x, y) {
                    return "<p style='color:red;background-color: rebeccapurple'>%y %s for %x </p>";
                }

                function gd(date) {
                    var splited = date.split('-');
                    return new Date(splited[2], splited[1], splited[0]).getTime();
                }

                var previousPoint = null, previousLabel = null;
                $.plot($("#flot-dashboard-chart"), dataset, options);
            }
        });
    }

    function changeMonthGraph(type) {
        $('#graphSpinner').css('display', 'flex').css('opacity', 0);
        setTimeout(function () {
            $('#graphSpinner').css('opacity', 1);
        }, 10);
        var prevFromMonth = $('#graphFromMonth').val();
        var prevToMonth = $('#graphToMonth').val();
        if (type === 'prev') {
            prevToMonth++;
            prevFromMonth++;
        } else if (type === 'next') {
            prevToMonth--;
            prevFromMonth--;
        }
        $('#graphSpinner').css('opacity', 0);
        setTimeout(function () {
            $('#graphSpinner').css('display', 'none');
        }, 1000);
        setTimeout(function () {
            load1stGraph('', prevFromMonth, prevToMonth);
        }, 100);
    }

    function generateUrl(page) {
        var currentUrl = window.location.href;
        var urlArr = currentUrl.split("&");
        var newUrl = "";
        for(var i = 0; i < urlArr.length; i++) {
            if (urlArr[i].includes("page")) {
                newUrl += "&page=" + page;
            } else {
                newUrl +=  urlArr[i];
            }
        }
        return newUrl;
    }

    function page_change(j,previewRange){
        $('.swapResp').append('<div class="flex-center newLoaderContainer" style="position: absolute;background-color: #1d1d1dde;top: 0px;height: 100%;">' +
            '                     <img class="blink_me" style="max-width:300px;" src="images/TAURUS-DARKBGSMALL.png">' +
            '                  </div>');
        window.location.href = generateUrl(j);
    }

    function gather_recent_appointments(previewPerPagePreset) {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        var categoryType = urlParams.get('short');
        var previewType = 'appointments';
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {
                action: 'gatherBulk',
                limit: 6,
                currentPage: 1,
                categoryType: 'haircuts',
                previewType: previewType
            },
            dataType: "json",
            success: function (data) {
                var response = '';
                var responseLength = (data.response.length) - 1;
                var response = '<table class="footable table table-stripped toggle-arrow-tiny">' +
                    '                                <thead>' +
                    '                                <tr>' +
                    '                                    <th data-hide="exchanger">State</th>' +
                    '                                    <th data-hide="exchanger">Client</th>' +
                    '                                    <th data-hide="exchanger">Phone</th>' +
                    '                                    <th data-hide="exchanger">on date</th>' +
                    '                                </tr>' +
                    '                                </thead>' +
                    '                                <tbody style="color:white;">';
                for (var i = 0; i <= responseLength; i++) {
                    var selectedServices = '';
                    for (var pk = 0; pk <= data.response[i].servicesInfos.length - 1; pk++) {
                        selectedServices += '<li class="search-choice2"><span>' + data.response[i].servicesInfos[pk].name + '</span></li>';
                    }
                    response += '<tr style="height:50px;" class="swaps">' +
                        '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')">' + data.response[i].state.icon + '</td>' +
                        '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].clientInfos.name + ' ' + data.response[i].clientInfos.surname + '</span></td>' +
                        '<td><a href="tel:' + data.response[i].clientInfos.phone + '"><i class="customFA fa fa-phone-square" style="font-size: 40px" aria-hidden="true"></i></a> ' + data.response[i].clientInfos.phone + '</td>' +
                        '<td onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')"><span>' + data.response[i].dateTimeExecuted + '</span></td>' +
                        '</tr>';

                }
                response += '  </tbody></table>';
                $('.recentHaircuts').html(response);
            },
        });
    }

    function detectDevice() {
        if (window.innerWidth < 768) {
            return true;
        }else{
            return false;
        }
    }

    function triggerStars(value){
        if(value === '1'){
            $('#hideBalances').attr('checked','checked');
            $('.normalInput').addClass('prehiddenInput');
            $('.hiddenInput').removeClass('normalSecInput');
        }else{
            $('.normalInput').removeClass('prehiddenInput');
            $('.hiddenInput').addClass('normalSecInput');
        }
    }

    function gather_settings_buttons_states(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'gatherSettingsData'},
            dataType: "json",
            success: function (data) {
                if(data.response.liveChat === '1'){
                    $('#activate_chat').attr('checked','checked');
                }
                triggerStars(data.response.hideBalances);
            }
        });
    }

    $(".onoffswitch-checkbox").change(function() { if(this.checked) { update_cms_settings_state('activate',this); }else{ update_cms_settings_state('deactivate',this); } });

    function update_cms_settings_state(type,elem){
        if(type === 'activate'){
            triggerStars('1');
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {action: 'changeSettingState',settingName:elem.id,settingValue:1},
                dataType: "json",
                success: function (data) {
                    if(elem.id !== 'hideBalances'){
                        if(data.response){
                            toastr.success(elem.parentElement.parentElement.parentElement.childNodes[1].innerText+ ' Activated !', 'State Managment');
                        }else{
                            toastr.error(elem.parentElement.parentElement.parentElement.childNodes[1].innerText+ ' cant activate please try again', 'State Managment Error');
                        }
                    }

                }
            });
        }
        if(type === 'deactivate'){
            triggerStars('0');
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {action: 'changeSettingState',settingName:elem.id,settingValue:0},
                dataType: "json",
                success: function (data) {
                    if(elem.id !== 'hideBalances') {
                        if (data.response) {
                            toastr.warning(elem.parentElement.parentElement.parentElement.childNodes[1].innerText + ' Deactivated !', 'State Managment');
                        } else {
                            toastr.error(elem.parentElement.parentElement.parentElement.childNodes[1].innerText + 'cant deactivate please try again', 'State Managment Error');
                        }
                    }
                }
            });
        }
    }

    const debounce = (func, wait, immediate)=> {
        var timeout;
        return function executedFunction() {
            var context = this;
            var args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };

   /* $('#top-search').on('input', debounce(function(e) {
        e.preventDefault();
        $('#generalShadowActive').on('click', function() {
            $('.generalShadow').removeClass('generalShadowActive');
            $('#topSearchResults').css('display','none');
        });
        $(document).keyup(function(event) {
            if (event.key === "Escape") {
                $('.generalShadow').removeClass('generalShadowActive');
                $('#topSearchResults').css('display','none');
            }
        });
        $('.generalShadow').addClass('generalShadowActive');
        var typedText = $(this).val();
        var counted = typedText.length;
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        var searchType = urlParams.get('short');
        if(searchType !== 'haircuts' && searchType !== 'clients' && searchType !== 'barbers'){
            searchType = 'general';
        }
        if (counted >= 2){
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {action: 'topSearch', typedText: typedText, type: searchType},
                dataType: "json",
                success: function (data) {
                    $('#topSearchResults').css('display','block');
                    var responseLength = data.response.length;
                    var extraHtmlText = '<div class="row"  style="height:40px;padding-top: 10px;padding-left: 20px;background-color: #4c4c4c;margin: 0px!important;color: white;text-transform: uppercase;"><div class="col-lg-1">Type</div><div class="col-lg-1">&nbsp;</div><div class="col-lg-1">&nbsp;</div><div class="col-lg-3">&nbsp;</div><div class="col-lg-3">&nbsp;</div></div>';
                    for (var i = 0; i < responseLength; i++) {
                        if (data.response[i].type === 'haircuts'){
                            var extraHTML = '<div class="chosen-container chosen-container-multi chosen-container-active"><ul class="chosen-choices">';
                            for (var j = 0; j < data.response[i].services.length; j++) {
                                 extraHTML += '<li class="search-choice2 impres"><span>'+ data.response[i].services[j] +'</span></li>';
                            }
                            extraHTML += '</ul></div>';
                             extraHtmlText += '<div class="row trGen" onclick="add_haircut(\'modals\',\'edit\','+data.response[i].id+')" style="height:40px;padding-top: 10px;margin: 0px!important">' +
                                '                               <div class="col-lg-1 custom-col-lg-1"><i style="font-size: 30px;margin-top: -5px;" class="customFA fa fa-scissors" aria-hidden="true"></i></div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2">'+ data.response[i].customerFirstName +'</div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2">'+ data.response[i].customerLastName +'</div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2 impres">'+ data.response[i].customerPhoneNumber +'</div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2">'+ data.response[i].dateTimeExecuted +'</div>' +
                                '                               <div class="col-lg-3 custom-col-lg-2">'+ extraHTML +'</div>' +
                                '                           </div>';
                        }
                        if (data.response[i].type === 'clients'){
                            extraHtmlText += '<div class="row trGen" onclick="add_client(\'modals\',\'edit\','+data.response[i].id+')" style="height:40px;padding-top: 10px;margin: 0px!important">' +
                                '                               <div class="col-lg-1 custom-col-lg-1"><i style="font-size: 30px;margin-top: -5px;" class="customFA fa fa-address-book" aria-hidden="true"></i></div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2">'+ data.response[i].name +'</div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2">'+ data.response[i].surname +'</div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2">'+ data.response[i].phone +'</div>' +
                                '                               <div class="col-lg-2 custom-col-lg-2">'+ data.response[i].email +'</div>' +
                                '                           </div>';
                        }
                    }
                    if (responseLength > 0){
                        $('#topSearchResults').html(extraHtmlText).addClass('topSearchResultsActive');
                    }else{
                        $('#topSearchResults').html('<div class="flex-center" style="font-size: 30px;padding: 40px;">No results found</div>').addClass('topSearchResultsActive');
                    }
                }
            });
        }else{
            $('.generalShadow').removeClass('generalShadowActive');
            $('#topSearchResults').css('display','none');
        }
    }, 400));*/


    function load_appointment(haircutId) {
        add_haircut('modals', 'edit', haircutId);
    }

    $(document).ready(function() {
        // Check if the screen width is less than or equal to your desired breakpoint (e.g., 768 for mobile devices)
        if ($(window).width() <= 768) {
            // Add the 'collapsed' class to each element with the 'custom-collapsed' class
            $('.custom-collapsed').addClass('collapsed');
        }

        const SEARCH_INPUT_SELECTOR = '#top-search';
        const SEARCH_RESULTS_SELECTOR = '#topSearchResults';
        const TAKIS_BUTTON_SELECTOR = '#takisButton';
        let isTakis = 0;

        // Add event listener for input changes with debounce function
        $(SEARCH_INPUT_SELECTOR).on('input', debounce(function(e) {
            e.preventDefault();
            if (isTakis === 0) {
                $(TAKIS_BUTTON_SELECTOR).hide();
            }

            $(SEARCH_RESULTS_SELECTOR).on('click', function() {
                $('.generalShadow').removeClass('generalShadowActive');
                $(SEARCH_RESULTS_SELECTOR).css('display', 'none');
                $(TAKIS_BUTTON_SELECTOR).show();
                isTakis = 1;
            });
            $(document).keyup(function(event) {
                if (event.key === "Escape") {
                    $('.generalShadow').removeClass('generalShadowActive');
                    $(SEARCH_RESULTS_SELECTOR).css('display', 'none');
                    $('#takisButton').hide();
                    isTakis = 0;
                }
            });
            $('.generalShadow').addClass('generalShadowActive');
            var typedText = $(this).val();
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            var searchType = urlParams.get('short');
            if (searchType !== 'haircuts' && searchType !== 'clients' && searchType !== 'barbers') {
                searchType = 'general';
            }
            if (typedText.startsWith('TAKIS')) {
                typedText = typedText.substring(5);
                $(SEARCH_INPUT_SELECTOR).val(typedText);
                $(TAKIS_BUTTON_SELECTOR).show();
                isTakis = 1;
            } else if(!typedText.startsWith('TAKIS') && isTakis === 0){
                $(TAKIS_BUTTON_SELECTOR).hide();
            }
            var counted = typedText.length;
            if(isTakis === 1){
                $('.generalShadow').addClass('generalShadowActive');
            }
            if (counted >= 2){
                $.ajax({
                    url: "AJAXPOSTS",
                    type: "POST",
                    data: {
                        action: 'topSearch',
                        typedText: typedText,
                        type: searchType,
                        isTakis: isTakis
                    },
                    dataType: "json",
                    success: function (data) {
                        $('#topSearchResults').css('display', 'block');
                        if(data.response){
                            var responseLength = data.response.length;
                            var extraHtmlText = '<div class="row"  style="height:40px;padding-top: 10px;padding-left: 20px;background-color: #4c4c4c;margin: 0px!important;color: white;text-transform: uppercase;"><div class="col-lg-1">Type</div><div class="col-lg-1">&nbsp;</div><div class="col-lg-1">&nbsp;</div><div class="col-lg-3">&nbsp;</div><div class="col-lg-3">&nbsp;</div></div>';
                            for (var i = 0; i < responseLength; i++) {
                                if (data.response[i].type === 'haircuts') {
                                    var extraHTML = '<div class="chosen-container chosen-container-multi chosen-container-active"><ul class="chosen-choices">';

                                    for (var j = 0; j < data.response[i].services.length; j++) {
                                        extraHTML += '<li class="search-choice2 impres"><span>' + data.response[i].services[j] + '</span></li>';
                                    }
                                    extraHTML += '</ul></div>';
                                    extraHtmlText += '<div class="row trGen" onclick="add_haircut(\'modals\',\'edit\',' + data.response[i].id + ')" style="height:40px;padding-top10px;margin: 0px!important">' +
                                        ' <div class="col-lg-1 custom-col-lg-1"><i style="font-size: 30px;margin-top: -5px;" class="customFA fa fa-scissors" aria-hidden="true"></i></div>' +
                                        ' <div class="col-lg-2 custom-col-lg-2">' + data.response[i].customerFirstName + '</div>' +
                                        ' <div class="col-lg-2 custom-col-lg-2">' + data.response[i].customerLastName + '</div>' +
                                        ' <div class="col-lg-2 custom-col-lg-2 impres">' + data.response[i].customerPhoneNumber + '</div>' +
                                        ' <div class="col-lg-2 custom-col-lg-2">' + data.response[i].dateTimeExecuted + '</div>' +
                                        ' <div class="col-lg-3 custom-col-lg-2">' + extraHTML + '</div>' +
                                        '</div>';
                                }
                                if (data.response[i].type === 'clients') {
                                    extraHtmlText += '<div class="row trGen" onclick="add_client(\'modals\',\'edit\',' + data.response[i].id + ')" style="height:40px;padding-top: 10px;margin: 0px!important">' +
                                        '  <div class="col-lg-1 custom-col-lg-1"><i style="font-size: 30px;margin-top: -5px;" class="customFA fa fa-address-book" aria-hidden="true"></i></div>' +
                                        '  <div class="col-lg-2 custom-col-lg-2">' + data.response[i].name + '</div>' +
                                        '  <div class="col-lg-2 custom-col-lg-2">' + data.response[i].surname + '</div>' +
                                        '  <div class="col-lg-2 custom-col-lg-2">' + data.response[i].phone + '</div>' +
                                        '  <div class="col-lg-2 custom-col-lg-2">' + data.response[i].email + '</div>' +
                                        '</div>';
                                }
                            }
                            if (responseLength > 0) {
                                $('#topSearchResults').html(extraHtmlText).addClass('topSearchResultsActive');
                            }
                        }
                        if(data.AIResponse) {
                            var responseLength = data.AIResponse.length;
                            var extraHtmlText = '<div class="row"  style="height:40px;padding-top: 10px;padding-left: 20px;background-color: #4c4c4c;margin: 0px!important;color: white;text-transform: uppercase;">' +
                                '       <div class="col-lg-12" >TAKIS answer</div>' +
                                '   </div>';


                            extraHtmlText += '<div class="col-lg-12" style="margin-top:20px;">' + data.AIResponse + '</div>';
                            if (responseLength > 0) {
                                $('#topSearchResults').html(extraHtmlText).addClass('topSearchResultsActive');
                            }
                        }
                        if(data.response === false){
                            $('#topSearchResults').html('<div class="flex-center" style="font-size: 30px;padding: 40px;">No results found</div>').addClass('topSearchResultsActive');
                        }
                        if(data.AIResponse === false){
                            $('#topSearchResults').html('<div class="flex-center" style="font-size: 30px;padding: 40px;">Bot error</div>').addClass('topSearchResultsActive');
                        }
                    }
                });
            }else{
                $('.generalShadow').removeClass('generalShadowActive');
                $('#topSearchResults').css('display','none');
            }
        }, 800));

    });