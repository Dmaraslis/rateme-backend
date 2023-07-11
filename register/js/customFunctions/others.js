
    function searchXML2() {
        var xmlDoc=loadXMLDoc2("requests.xml");
        var x=xmlDoc.getElementsByTagName("partner");
        var results = [];

        for (var i=0;i<x.length;i++)
        {
            var partnerId=xmlDoc.getElementsByTagName("partnerId")[i].childNodes[0].nodeValue;
            var requests=xmlDoc.getElementsByTagName("requestsSum")[i].childNodes[0].nodeValue;

            results.push({'partner':[partnerId,requests]});
        }
        return results;
    }

    function getAPIStats(type){
        var seachRes = searchXML2();
        var ccc = seachRes.toString();
        var byteAmount = unpack(ccc);
        var previewsBiteAmount = getCookie('getApiStats');
        if (byteAmount !== parseInt(previewsBiteAmount) || type ==='firstLoad') {
            checkCookie('getApiStats', byteAmount);
            var length = (seachRes.length);
            for (var i=0;i<length;i++){
                var maxRequests = $('#maxRequestsLimitFor'+seachRes[i].partner[0]).html();
                var explodedSumRequests = seachRes[i].partner[1].split('<>');
                var result = ((parseInt(explodedSumRequests[0]) / parseInt(maxRequests)) * 100).toFixed(1);
                $('#apiProgressState'+seachRes[i].partner[0]).css('width', result+'%');
                var remainReqs =  maxRequests - explodedSumRequests[0];
                if(result === 0 || result === 0.0 || result === '0.0' || result === null || result === ''){
                    var test = $('#apiState' + seachRes[i].partner[0]);
                    var actualElement = test[0].parentNode.parentNode.parentNode;
                    $(actualElement).remove();
                }else {
                    if (result <= 50) {
                        $('#apiState' + seachRes[i].partner[0]).html('<span style="width: 100%; float: left;text-align: left;">API State: <i style="color:green">Normal</i></span><h2 style="color:#1ab394;position: absolute;right: 21px;top: 0px;font-size: 30px;">' + result + '%</h2><br>');
                        $('#remainingRequestsFor' + seachRes[i].partner[0]).html(remainReqs).css('color', '#1ab394');
                        $('#apiProgressState' + seachRes[i].partner[0]).css('background-color', '#1ab394');
                    }
                    if (result > 50 && result <= 70) {
                        $('#apiState' + seachRes[i].partner[0]).html('<span style="width: 100%; float: left;text-align: left;">API State: <i style="color:#ff7e32">Warning</i></span><h2 style="color:#ff7e32;position: absolute;right: 21px;top: 0px;font-size: 30px;">' + result + '%</h2><br>');
                        $('#remainingRequestsFor' + seachRes[i].partner[0]).html(remainReqs).css('color', '#ff7e32');
                        $('#apiProgressState' + seachRes[i].partner[0]).css('background-color', '#ff7e32');
                    }
                    if (result > 70) {
                        $('#apiState' + seachRes[i].partner[0]).html('<span style="width: 100%; float: left;text-align: left;">API State: <i style="color:#ff0800">Warning</i></span><h2 style="color:#ff0800;position: absolute;right: 21px;top: 0px;font-size: 30px;">' + result + '%</h2><br>');
                        $('#remainingRequestsFor' + seachRes[i].partner[0]).html(remainReqs).css('color', '#ff0800');
                        $('#apiProgressState' + seachRes[i].partner[0]).css('background-color', '#ff0800');
                    }
                    if (result > 90) {
                        var partnerName = $('#partnerName' + seachRes[i].partner[0]).val();
                        $('#aud').html('<iframe src="tones/notif.mp3" allow="autoplay" style="display:none">');
                        toastr.error(partnerName + ' api requests passed 90%! Check Requests', 'API ERROR!');
                    }
                }
            }
        }
    }



    function gather_kyt_remaining_checks(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'gather_KYT_checks'},
            dataType: "json",
            success: function (data) {
                var selectedStandardColor = 'none';
                if(data.response.standardChecks <= 1000){
                    selectedStandardColor = 'darkorange';
                }else if(data.response.standardChecks <= 300){
                    selectedStandardColor = 'darkred';
                }
                var selectedEnchancedColor = 'none';
                if(data.response.enhancedChecks <= 60){
                    selectedEnchancedColor = 'darkorange';
                }else if(data.response.enhancedChecks <= 30){
                    selectedEnchancedColor = 'darkred';
                }
                $('.enchancedChecks').html(data.response.enhancedChecks).css('color',selectedEnchancedColor);
                $('.standardChecks').html(data.response.standardChecks).css('color',selectedStandardColor);
            }
        });
    }


    function gather_wallets_volume() {
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'gather_wallets_volume'},
            dataType: "json",
            success: function (data) {
                var erxportationfree = ['Available_Wallets'];
                var erxportationSumWallets = ['Wallets_Sum'];
                var erxportationAssets = [];

                var erxportationfree2 = ['Available_Wallets'];
                var erxportationSumWallets2 = ['Wallets_Sum'];
                var erxportationAssets2 = [];

                var erxportationfree3 = ['Available_Wallets'];
                var erxportationSumWallets3 = ['Wallets_Sum'];
                var erxportationAssets3 = [];

                for (var i = 0; i < data.responseBinance.coinStats.length; i++) {
                    erxportationfree2.push([data.responseBinance.coinStats[i].walletsFree]);
                    erxportationSumWallets2.push([data.responseBinance.coinStats[i].walletsNum]);
                    erxportationAssets2.push([""+data.responseBinance.coinStats[i].asset+""]);
                }
                for (var i = 0; i < data.responseBequant.coinStats.length; i++) {
                    erxportationfree.push([data.responseBequant.coinStats[i].walletsFree]);
                    erxportationSumWallets.push([data.responseBequant.coinStats[i].walletsNum]);
                    erxportationAssets.push([""+data.responseBequant.coinStats[i].asset+""]);
                }
                for (var i = 0; i < data.responseHitbtc.coinStats.length; i++) {
                    erxportationfree3.push([data.responseHitbtc.coinStats[i].walletsFree]);
                    erxportationSumWallets3.push([data.responseHitbtc.coinStats[i].walletsNum]);
                    erxportationAssets3.push([""+data.responseHitbtc.coinStats[i].asset+""]);
                }
                var containerWidth = $("#pie2").width();
                var chart = c3.generate({
                    bindto: '#pie2',
                    size: {
                        height: 220,
                        width: containerWidth
                    },
                    data: {
                        columns: [
                            erxportationSumWallets
                        ],
                        colors: {
                            Wallets_Sum: '#089160'
                        },
                        types: {
                            Wallets_Sum: 'bar'
                        },
                        groups: [['Wallets_Sum']]
                    },
                    axis: {
                        x: {
                            type: 'category',
                            categories: erxportationAssets,
                            tick: {
                                fit: true,
                                format: "%e %b %y",
                                rotate: 75,
                                multiline: false
                            },
                            height: 35
                        }
                    },
                    legend: { show: false },
                    zoom: { enabled: true }
                });
                setTimeout(function () {
                    chart.load({
                        columns: [
                            erxportationfree
                        ],
                        colors: {
                            Available_Wallets: '#08b07a'
                        },
                        types: {
                            Available_Wallets: 'bar'
                        },
                        groups: [['Available_Wallets']]
                    });
                }, 100);


                var containerWidth2 = $("#pie3").width();
                var chart2 = c3.generate({
                    bindto: '#pie3',
                    size: {
                        height: 220,
                        width: containerWidth2
                    },
                    data: {
                        columns: [
                            erxportationSumWallets2
                        ],
                        colors: {
                            Wallets_Sum: '#089160'
                        },
                        types: {
                            Wallets_Sum: 'bar'
                        },
                        groups: [['Wallets_Sum']]
                    },
                    axis: {
                        x: {
                            type: 'category',
                            categories: erxportationAssets2,
                            tick: {
                                fit: true,
                                format: "%e %b %y",
                                rotate: 75,
                                multiline: false
                            },
                            height: 35
                        }
                    },
                    legend: { show: false },
                    zoom: { enabled: true }
                });
                setTimeout(function () {
                    chart2.load({
                        columns: [
                            erxportationfree2
                        ],
                        colors: {
                            Available_Wallets: '#08b07a'
                        },
                        types: {
                            Available_Wallets: 'bar'
                        },
                        groups: [['Available_Wallets']]
                    });
                }, 100);

                var containerWidth3 = $("#pie4").width();
                var chart3 = c3.generate({
                    bindto: '#pie4',
                    size: {
                        height: 220,
                        width: containerWidth3
                    },
                    data: {
                        columns: [
                            erxportationSumWallets3
                        ],
                        colors: {
                            Wallets_Sum: '#089160'
                        },
                        types: {
                            Wallets_Sum: 'bar'
                        },
                        groups: [['Wallets_Sum']]
                    },
                    axis: {
                        x: {
                            type: 'category',
                            categories: erxportationAssets3,
                            tick: {
                                fit: true,
                                format: "%e %b %y",
                                rotate: 75,
                                multiline: false
                            },
                            height: 35
                        }
                    },
                    legend: { show: false },
                    zoom: { enabled: true }
                });
                setTimeout(function () {
                    chart3.load({
                        columns: [
                            erxportationfree3
                        ],
                        colors: {
                            Available_Wallets: '#08b07a'
                        },
                        types: {
                            Available_Wallets: 'bar'
                        },
                        groups: [['Available_Wallets']]
                    });
                }, 100);
            }
        });
    }

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

    function checkExchangerApiStatus(type) {
        var seachRes = searchXMLApiCheck();
        var ccc = seachRes.toString();
        var byteAmount = unpack(ccc);
        var previewsBiteAmount = getCookie('exchangerStatus');
        if (byteAmount !== parseInt(previewsBiteAmount) || type === 'firstLoad') {
            checkCookie('exchangerStatus', byteAmount);
            var length = (seachRes.length)-1;
            var resp = '';
            for (var i=0;i<=length;i++){
                var stateStatus = '';
                var statusText = '';
                var freeCenter = '';
                var explodedSumRequests = seachRes[i].partner[1].split('<>');
                if(explodedSumRequests[2] !== 'Not Used') {
                    if (explodedSumRequests[2] === 'OK') {
                        stateStatus = 'background: linear-gradient(90deg,rgb(29 29 29) 10%, rgb(12 170 113 / 61%) 140%);';
                        var statusText2 = '<small>' + explodedSumRequests[4] + '</small>';
                        var statusText = '';
                        var extraText = '<span style="color:forestgreen;font-size:20px;">OK</span>';
                    } else {
                        stateStatus = 'background: linear-gradient(90deg,rgb(29 29 29) 10%, #a50c0c 140%);border-left: 1px solid #2d2d2d;';
                        var statusText = '<small>Response prices: ' + explodedSumRequests[3] + '</small>';
                        var statusText2 = '<small>Response wallets: ' + explodedSumRequests[4] + '</small>';
                        var extraText = '<span style="color:darkred;font-size:20px;">ERROR</span>';
                    }
                    switch (seachRes[i].partner[0]) {
                        case '19':
                        case '39':
                            var freeCenter = '<div class="col-lg-5 flex-center" style="'+ stateStatus +'border-left: 1px solid #2d2d2d;border-top-right-radius: 10px;border-bottom-right-radius: 10px;"><div><button onclick="transfer_sub_wallets_balance_to_main(\''+ seachRes[i].partner[0] +'\',\''+ explodedSumRequests[0] +'\')" style="color: white;background-color: #1a644a;border-radius: 5px;"> Claim Profits</button><br><small>From Sub\'s to Main</small></div></div>';
                            break;
                        case '65530':
                            var freeCenter = '<div class="col-lg-5 flex-center" style="'+ stateStatus +'border-left: 1px solid #2d2d2d;border-top-right-radius: 10px;border-bottom-right-radius: 10px;"><div>'+explodedSumRequests[4]+'</div></div>';
                            statusText2 = '';
                            break;
                        default:
                            var freeCenter = '<div class="col-lg-5 flex-center" style="'+ stateStatus +'border-left: 1px solid #2d2d2d;border-top-right-radius: 10px;border-bottom-right-radius: 10px;">'+ extraText +'</div>';
                    }
                    resp = resp +
                        '<div class="col-lg-3 row" style="    margin: 10px 0px 10px 0px;">' +
                        '       <div class="col-lg-7" style="min-height: 100px;height:auto;background-color: #1d1d1d;border-top-left-radius: 10px;border-bottom-left-radius: 10px;padding-top: 10px;padding-bottom: 10px;">' +
                        '         <div class="row">' +
                        '           <div class="col-lg-8">' +
                        '               <div class="col-lg-12 flex-center" style="padding:0px;"><img style="max-width: 130px;max-height: 40px;margin-left:10px;width: 100%;" src="' + explodedSumRequests[1] + '"></div>' +
                        '               <div class="col-lg-12" style="padding:0px;"><i style="color:#6c6c6c;font-size:8px;!important">Updated: ' + explodedSumRequests[7] + '</i></div>' +
                        '           </div>' +
                        '       </div>' +
                        '       <div class="row" style="margin:0px;"><span style="color:#a0a0a0;font-size:12px;">' + statusText + statusText2 +'</span></div>' +
                        '       </div>' +freeCenter+
                        '   </div>';
                    $('.spiner-example').css('display', 'none');
                    $('#exchangersListStatus').html(resp).css('margin-top','50px');
                }
            }
        }
    }


    function transfer_sub_wallets_balance_to_main(exchangerId,exchangerName){
        swal({
            title: "Warning",
            text: 'Are you sure you want to transfer all sub accounts balance of '+ exchangerName +' to main account?',
            type: "warning",
            customClass: "customSwallClass",
            confirmButtonColor: "#0caa71",
            confirmButtonText: "Yes Continue",
            showCancelButton: true,
            closeOnConfirm: false
        }, function (e) {
            if(e){
                /* swal({
                title: 'Transfering...',
                type: 'info',
                showCancelButton: false,
                showConfirmButton: false,
                customClass: "customSwallClass"
            });*/
                $.ajax({
                    url: "AJAXPOSTS",
                    type: "POST",
                    data: {action: 'transferBalanceFromSubsToMain', exchangerId: exchangerId},
                    dataType: "json",
                    success: function (data) {
                        console.log(data);
                    }
                });
            }
        });
    }

    function loadActivePairsGraph() {
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'gatherPairsGraph'},
            dataType: "json",
            success: function (data) {
                var pairsByExchanger = data.response.pairsByExchanger;
                var countedCrypto = data.response.countedCrypto;
                var countedFiat = data.response.countedFiat;
                var maxCountedOnExchange = 0;
                for (var j = 0; j <= pairsByExchanger.length -1; j++) {
                    if(pairsByExchanger[j].pairsCount > maxCountedOnExchange){
                        maxCountedOnExchange = pairsByExchanger[j].pairsCount
                    }
                }
                var exports = '<div class="row" style="position: absolute;width: 90%;top: 0px;text-align: center;margin-left: 20px;">' +
                    '                        <div class="col-lg-6 custC">' +
                    '                        <h1 class="no-margins countedFiat">'+ countedFiat +'</h1>' +
                    '                        <small>Active Fiat Pairs</small>' +
                    '                    </div>' +
                    '                    <div class="col-lg-6 custC">' +
                    '                        <h1 class="no-margins countedCrypto">'+ countedCrypto +'</h1>' +
                    '                        <small>Active Crypto Pairs</small>' +
                    '                    </div>' +
                    '                    </div>' +
                    '<div class="row" style="width:100%;">'+
                    '                        <div class="col-lg-12">'+
                    '                        <div class="">'+
                    '                        <div class="flot-chart-content" id="flot-dashboard-Active-Pairs-chart"></div>'+
                    '                        </div>'+
                    '                        </div>'+
                    '                        </div>';
                $('#activePairsGraphPreview').html(exports);
                var erxportationSumWallets = ['Active_Pairs'];
                var erxportationAssets = [];
                for (var i = 0; i <= pairsByExchanger.length -1; i++) {
                    erxportationSumWallets.push([pairsByExchanger[i].pairsCount]);
                    erxportationAssets.push([""+pairsByExchanger[i].exchangerName+""]);
                }
                var containerWidth = $("#flot-dashboard-Active-Pairs-chart").width();
                var chart = c3.generate({
                    bindto: '#flot-dashboard-Active-Pairs-chart',
                    size: {
                        height: 120,
                        width: containerWidth
                    },
                    data: {
                        columns: [
                            erxportationSumWallets
                        ],
                        colors: {
                            Active_Pairs: '#0cffaa'
                        },
                        types: {
                            Active_Pairs: 'spline'
                        },
                        groups: [['Active Pairs']]
                    },
                    axis: {
                        x: {
                            type: 'category',
                            categories: erxportationAssets,
                            tick: {
                                fit: true,
                                format: "%e %b %y",
                                rotate: 75,
                                multiline: false
                            },
                            height: 40
                        },
                        y: {
                            show: false
                        }
                    },
                    legend: { show: false },
                    zoom: { enabled: false }
                });
            }
        });
    }

    function load1stGraph(type,fromMonth,toMonth) {
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'gather1stGraph', date: fromMonth},
            dataType: "json",
            success: function (data) {
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
                var SumCompletedMonthlyOrders = data.response.dato.successTxCount;
                var currentMonth = data.response.dato.month;
                var SumAnnualOrders = data.response.extraData.sumCompletedOrders;
                var sumDays = data.response.dato.monthDays;
                var SumMonthOrders = parseInt(data.response.dato.successTxCount) + parseInt(data.response.dato.failedTxCount);
                var sumEurProf = data.response.dato.sumEurProfit;
                var maxOrdersNumOnADay = data.response.dato.maxOrdersNumOnDay;
                var maxCompletedOrdersNumOnADay = data.response.dato.maxCompletedOrdersNumOnDay;
                var days = data.response.dato.dayAnalysis;
                $('#MonthDisplay').html('Preview Range: 01-' + sumDays + ' ' + currentMonth);
                if(data.response.prevDato){
                    var prevSumCompletedMonthlyOrders = data.response.prevDato.successTxCount;
                    var prevMonth = data.response.prevDato.month;
                    var prevSumMonthOrders = parseInt(data.response.prevDato.successTxCount) + parseInt(data.response.prevDato.failedTxCount);
                    var prevsumEurProf = data.response.prevDato.sumEurProfit;
                    var prevmaxOrdersNumOnADay = data.response.prevDato.maxOrdersNumOnDay;
                    var prevmaxCompletedOrdersNumOnADay = data.response.prevDato.maxCompletedOrdersNumOnDay;
                    var prevdays = data.response.prevDato.dayAnalysis;
                }else{
                    var prevSumCompletedMonthlyOrders = 0;
                    var prevMonth = 0;
                    var prevSumMonthOrders = 0;
                    var prevsumEurProf = 0;
                    var prevmaxOrdersNumOnADay = 0;
                    var prevmaxCompletedOrdersNumOnADay = 0;
                    var prevdays = 0;
                }

                /* annual orders infos START */
                $('.totalOrders').html(SumAnnualOrders);
                var percentageOfTotal = (SumAnnualOrders / 50000 * 100).toFixed(2);
                $('.totalOrdersByGoal').html(percentageOfTotal + '%');
                $('.totalOrdersByGoalBar').css('width', percentageOfTotal + '%');
                /* annual orders infos END */
                /* total monhly orders infos START */
                $('.totalMonthlyOrders').html(SumMonthOrders);
                var percentageOfMonthly = ((prevSumMonthOrders / prevSumMonthOrders * 100) - (SumMonthOrders / prevSumMonthOrders * 100)).toFixed(2);
                if (SumMonthOrders < prevSumMonthOrders) {
                    $('.totalMonthlyOrdersByPrev').html(percentageOfMonthly + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.totalMonthlyOrdersByPrevBar').css('background-color', 'red');
                    $('.totalMonthlyOrdersByPrevBar').css('width', percentageOfMonthly + '%');
                } else {
                    $('.totalMonthlyOrdersByPrev').html(Math.abs(percentageOfMonthly) + '% <i class="fa fa-level-up text-navy"></i>');
                    $('.totalMonthlyOrdersByPrevBar').css('width', Math.abs(percentageOfMonthly) + '%');
                }
                /* total monhly orders infos END */
                /* total Completed monhly orders infos START*/
                $('.totalCompletedMonthlyOrders').html(SumCompletedMonthlyOrders);
                var percentageOfCompletedMonthly = ((prevSumCompletedMonthlyOrders / prevSumCompletedMonthlyOrders * 100) - (SumCompletedMonthlyOrders / prevSumCompletedMonthlyOrders * 100)).toFixed(2);
                if (SumCompletedMonthlyOrders < prevSumCompletedMonthlyOrders) {
                    $('.totalCompletedMonthlyOrdersByPrev').html(percentageOfCompletedMonthly + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.totalCompletedMonthlyOrdersByPrevBar').css('background-color', 'red');
                    $('.totalCompletedMonthlyOrdersByPrevBar').css('width', percentageOfCompletedMonthly + '%');
                } else {
                    $('.totalCompletedMonthlyOrdersByPrev').html(Math.abs(percentageOfCompletedMonthly) + '% <i class="fa fa-level-up text-navy"></i>');
                    $('.totalCompletedMonthlyOrdersByPrevBar').css('width', Math.abs(percentageOfCompletedMonthly) + '%');
                }
                /* total Completed monhly orders infos END*/
                /* Max Order On One Day infos START*/
                $('.maxOrdersOnOneDay').html(maxOrdersNumOnADay);
                var percentageOfMaxOrdersOnADay = ((prevmaxOrdersNumOnADay / prevmaxOrdersNumOnADay * 100) - (maxOrdersNumOnADay / prevmaxOrdersNumOnADay * 100)).toFixed(2);
                if (maxOrdersNumOnADay < prevmaxOrdersNumOnADay) {
                    $('.maxOrdersOnOneDayByPrev').html(percentageOfMaxOrdersOnADay + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.maxOrdersOnOneDayByPrevBar').css('background-color', 'red');
                    $('.maxOrdersOnOneDayByPrevBar').css('width', percentageOfMaxOrdersOnADay + '%');
                } else {
                    $('.maxOrdersOnOneDayByPrev').html(Math.abs(percentageOfMaxOrdersOnADay) + '% <i class="fa fa-level-up text-navy"></i>');
                    $('.maxOrdersOnOneDayByPrevBar').css('width', Math.abs(percentageOfMaxOrdersOnADay) + '%');
                }
                /* Max Order On One Day infos END*/
                /* Max Completed Order On One Day infos infos WIND */
                $('.maxCompleteOrdersOnOneDay').html(maxCompletedOrdersNumOnADay);
                var percentageOfMaxCompleteOrdersOnADay = ((prevmaxCompletedOrdersNumOnADay / prevmaxCompletedOrdersNumOnADay * 100) - (maxCompletedOrdersNumOnADay / prevmaxCompletedOrdersNumOnADay * 100)).toFixed(2);
                if (maxCompletedOrdersNumOnADay < prevmaxCompletedOrdersNumOnADay) {
                    $('.maxCompleteOrdersOnOneDayByPrev').html(percentageOfMaxCompleteOrdersOnADay + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.maxCompleteOrdersOnOneDayByPrevBar').css('background-color', 'red');
                    $('.maxCompleteOrdersOnOneDayByPrevBar').css('width', percentageOfMaxCompleteOrdersOnADay + '%');
                } else {
                    $('.maxCompleteOrdersOnOneDayByPrev').html(Math.abs(percentageOfMaxCompleteOrdersOnADay) + '% <i class="fa fa-level-up text-navy"></i>');
                    $('.maxCompleteOrdersOnOneDayByPrevBar').css('width', Math.abs(percentageOfMaxCompleteOrdersOnADay) + '%');
                }
                /* Max Completed Order On One Day infos infos END*/
                /* Annual Euro Profit START */
                $('.annualEurProf').html(parseFloat(sumEurProf).toFixed(2) + '€');
                var percentageOfannualEurProf = ((prevsumEurProf / prevsumEurProf * 100) - (sumEurProf / prevsumEurProf * 100)).toFixed(2);
                if (sumEurProf < prevsumEurProf) {
                    $('.annualEurProfPrev').html(percentageOfannualEurProf + '% <i class="fa fa-level-down text-navy" style="color:red!important;"></i>').css('color', 'red');
                    $('.annualEurProfPrevBar').css('background-color', 'red');
                    $('.annualEurProfPrevBar').css('width', percentageOfannualEurProf + '%');
                } else {
                    $('.annualEurProfPrev').html(Math.abs(percentageOfannualEurProf) + '% <i class="fa fa-level-up text-navy"></i>');
                    $('.annualEurProfPrevBar').css('width', Math.abs(percentageOfannualEurProf) + '%');
                }
                /* Annual Euro Profit END*/
                var data3 = [];
                var data2 = [];
                for (var i = 0; i <= sumDays-1; i++) {
                    var preData3 = [gd(days[i].date), days[i].sumTxs];
                    var preData2 = [gd(days[i].date), days[i].successTx];
                    data3.push(preData3);
                    data2.push(preData2);
                }

                var dataset = [
                    {
                        label: "Haircuts",
                        data: data3,
                        color: "#0dda92",
                        bars: {
                            show: true,
                            align: "left",
                            barWidth: 24 * 60 * 60 * 600,
                            lineWidth: 1
                        }

                    }
                ];

                var metrics = (300 / maxOrdersNumOnADay) / 2;
                if (metrics >= maxOrdersNumOnADay) {
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
                        tickSize: [10],
                        tickLength: 10,
                        axisLabel: "count",
                        position: "left",
                        max: Math.round(maxOrdersNumOnADay + 2),
                        color: "#d55252",
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: 'Arial',
                        axisLabelPadding: 3
                    }, {
                        mode: "count",
                        tickSize: [10],
                        tickLength: 10,
                        axisLabel: "count",
                        position: "right",
                        color: "#d55252",
                        max: Math.round(maxOrdersNumOnADay + 2),
                        axisLabelUseCanvas: true,
                        axisLabelFontSizePixels: 12,
                        axisLabelFontFamily: ' Arial',
                        axisLabelPadding: 3
                    }
                    ],
                    legend: {
                        noColumns: 2,
                        labelBoxBorderColor: "#2d2d2d",
                        position: "nw",
                        backgroundColor: null,
                    },
                    grid: {
                        color: "#ffffff",
                        clickable: true,
                        tickColor: "#2f4050",
                        borderWidth: 0,
                        hoverable: true,
                        backgroundColor: "#2d2d2d",

                    },
                    tooltip: true,
                    tooltipOpts: {
                        content: function (data, x, y, dataObject) {
                            return "<p style='color:red;'>%y %s for %x </p>";
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
        $('#graphSpinner').css('display', 'block').css('opacity', 0);
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

    function changePermitionsState(permitionId,giveCoinShortname,getCoinShortname){
        swal({
            title: "Activate Permitions?",
            text: "Are you sure you want to activate this combination of permitions?",
            type: "success",
            showCancelButton: true,
            confirmButtonColor: "#9cdd2c",
            confirmButtonText: "Yes, proceed!",
            customClass: "customSwallClass",
            closeOnConfirm: true
        }, function () {
            $.ajax({
                url: "AJAXPOSTS",
                type: "POST",
                data: {action: 'constructionPermitionActive', giveCoinShortname: giveCoinShortname, getCoinShortname:getCoinShortname},
                dataType: "json",
                success: function (data) {
                    if (data.result.responseState === 'ERROR'){
                        swal({
                            title: "Access Denied",
                            text: "Please contact with system administrator",
                            type: "error",
                            showCancelButton: false,
                            confirmButtonColor: "#9cdd2c",
                            confirmButtonText: "Ok",
                            customClass: "customSwallClass",
                            closeOnConfirm: true
                        })
                    }else{
                        toastr.info('Permition Activated Successfully!', 'Permition Managment!');
                        $('.perm-'+permitionId).remove();
                    }
                }
            });
        });
    }

    function profitsByAdmins(){
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'profitsByAdmins'},
            dataType: "json",
            success: function (data) {
                var exportedHtml = '';
                for(var i=0; i<data.response.length;i++){
                    exportedHtml += '<div class="col-lg-6 customMobileFunc" style="font-size:12px!important;"><div onclick="loadTxLog();" style="width: 20px;height: 20px;background-color: #0caa71;position: absolute;z-index: 1;left: 8px;top: 15px;padding-left: 6px;padding-top: 2px;border-radius: 10px;"><i class="fa fa-caret-down" aria-hidden="true"></i>\n</div>' +
                        '<div class="col-lg-12 row" style="position: absolute;width: 82%;background-color: #1d1d1dbf;border-radius: 5px;align-items: center;margin: 1.5%;height: 45px;">' +
                        ''+ data.response[i].nickname +'' +
                        '<span class="prehiddenInput normalInput" style="position: absolute;right: 10px;">'+ data.response[i].euroProfit +' €</span>' +
                        '<span class="hiddenInput normalSecInput"> * * * * €</span>' +
                        '</div>' +
                        '<div class="row animated  flex-center" data-animation="fadeInUpShorter" data-animation-delay="0.'+i+'s" style="min-height: 51px;margin: 0px 0px 0px 0px;padding: 5px;border-radius: 5px;background-color: #0caa71;background-size: cover;background-image: url('+ data.response[i].image +');background-repeat: no-repeat;background-position: 10% 20%;    margin-bottom: 5px;">'+
                        '</div></div>';
                }
                $('#profitsByAdmins').html(exportedHtml);
            }
        });
    }

    function loadTxLog(){
        /*$.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {action: 'TxLog'},
            dataType: "json",
            success: function (data) {
                console.log(data);
            }
        });*/
    }

    function page_change(j,previewRange){
        $('.swapResp').append('<div class="flex-center newLoaderContainer" style="position: absolute;background-color: #1d1d1dde;top: 0px;height: 100%;">' +
            '                     <img class="blink_me" style="max-width:300px;" src="images/TAURUS-DARKBGSMALL.png">' +
            '                  </div>');
        history.pushState(null, null, 'https://'+window.location.host+'/index.php?pairsCheckPage='+j);
        pairsChecker(previewRange);
    }

    function pairsChecker(previewPerPage){
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        var currentPage = urlParams.get('pairsCheckPage');
        if(currentPage === null){currentPage = 1}else currentPage = parseInt(currentPage);
        $.ajax({
            url: "AJAXPOSTS",
            type: "POST",
            data: {
                action: 'pairsChecker',
                limit: previewPerPage,
                currentPage: currentPage,
            },
            dataType: "json",
            success: function (data) {
                var responsePagination = '';
                var pages = data.pages;
                var responseLength = (data.response.length) - 1;
                if(pages){
                    responsePagination += '<ul class="pagination float-left">';
                    if (pages > 1 && currentPage > 1){
                        responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change('+1+','+previewPerPage+');return false;">«</a></li>';
                    }else{
                        responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">«</a></li>';
                    }
                    if (currentPage > 1){
                        responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change('+(currentPage-1)+','+previewPerPage+');return false;">‹</a></li>';
                    }else{
                        responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">‹</a></li>';
                    }
                    var meter1 = 0;
                    for (var j=currentPage;j<=pages;j++){
                        if(meter1 <= 4){
                            if (currentPage === j){
                                responsePagination += '<li class="footable-page active"><a href="JavaScript:Void(0)" style="background-color: #00c383!important;">'+currentPage+'</a></li>';
                            }else{
                                responsePagination += '<li class="footable-page"><a href="#" onclick="page_change('+j+','+previewPerPage+');return false;">'+ j+'</a></li>';
                            }
                        }else if(meter1 > 4 && j < (pages - 4)){
                            if(meter1 === 6){
                                responsePagination += '<li class="footable-page"><a href="JavaScript:Void(0)">...</a></li>';
                            }
                            if(currentPage > 7){
                                if(meter1 > Math.round(pages/2) && meter1 < (Math.round(pages/2)) + 4){
                                    responsePagination += '<li class="footable-page"><a href="#" onclick="page_change('+j+','+previewPerPage+');return false;">'+j+'</a></li>';
                                }
                                if( meter1 === (Math.round(pages/2)) + 4){
                                    responsePagination += '<li class="footable-page"><a href="JavaScript:Void(0)">...</a></li>';
                                }
                            }
                        }else if(j >= (pages - 1)){
                            responsePagination += '<li class="footable-page"><a href="#" onclick="page_change('+j+','+previewPerPage+');return false;">'+ j+'</a></li>';
                        }
                        meter1++;
                    }
                    if (currentPage < pages){
                        responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change('+parseInt(currentPage+1)+','+previewPerPage+');return false;">›</a></li>';
                    }else{
                        responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">›</a></li>';
                    }
                    if (pages > 1 && currentPage !== pages){
                        responsePagination += '<li class="footable-page-arrow"><a href="#" onclick="page_change('+pages+','+previewPerPage+');return false;">»</a></li>';
                    }else{
                        responsePagination += '<li class="footable-page-arrow customDisabled"><a href="JavaScript:Void(0)">»</a></li>';
                    }
                    responsePagination += '</ul>';
                }else{
                    responsePagination += '<ul class="pagination float-right"></ul>';
                }
                var responsePagination2 = '<div style="margin-bottom: 5px;color: grey;">Previewing '+ (currentPage * previewPerPage) +' of '+ data.pairs +'&nbsp; <i style="color:orange">Closed</i>&nbsp;  pairs</div>';
                var constuctionPairs = data.response;
                var constructionPairsLength = data.response.length;
                var resultWithdrawRequests = '' +
                    '<div class="col-lg-12"><div class="row">' +
                    '   <div class="col-lg-3" style="padding: 0px;">Pair</div>' +
                    '   <div class="col-lg-5">Closed By</div>' +
                    '   <div class="col-lg-4">Actions</div>' +
                    '</div></div>';
                for (var p = 0; p < constructionPairsLength; p++) {
                    var dateTime4 = constuctionPairs[p].extraData.dateTimeCreated;
                    var nod4 = dateFormat(new Date(), 'Y-m-d H:i:s');
                    var dateFirst4 = new Date(dateTime4);
                    var dateSecond4 = new Date(nod4);
                    var differance4 = new DateDiff(dateSecond4, dateFirst4);
                    var resultedDifferance4 = '';
                    if (differance4.days > 0) {
                        resultedDifferance4 = differance4.days + 'd ' + differance4.hours + 'h';
                    } else if (differance4.hours > 0) {
                        resultedDifferance4 = differance4.hours + 'h ' + differance4.minutes + 'm';
                    } else if (differance4.minutes > 0) {
                        resultedDifferance4 = differance4.minutes + 'm';
                    } else if (differance4.seconds > 0 && differance4.seconds <= 60) {
                        resultedDifferance4 = 'Just now!';
                    }
                    if(constuctionPairs[p].baseCurrency.side === 'our' || constuctionPairs[p].quoteCurrency.side === 'our'){
                        var  closedSide = '<div style="">Mods <i class="customFA fa fa-user-secret" aria-hidden="true"></i></div>';
                        var closedSideState = 'mods';
                    }else{
                        var  closedSide = '<div style="">Checker <img style="width:20px;" src="https://gintonic.instaswap.io/images/darthVader.png"></div>';
                        var closedSideState = 'checker';
                    }
                    if(resultedDifferance4 !== 'Just now!'){
                        var extramessage = ' ago';
                    }else{
                        var extramessage = '';
                    }
                    if(parseInt(constuctionPairs[p].extraData.checkTimes) < 2000 && closedSideState === 'checker'){
                        var pairInfoMessage = '<i style="color:grey;font-size:10px;">Auto Open</i>';
                    }else{
                        var pairInfoMessage = '<i style="color:darkorange;font-size:10px;">Open manually</i>';
                    }
                    resultWithdrawRequests +=
                        '<div class="col-lg-12 perm-'+  constuctionPairs[p].extraData.pairId +'"><div class="row" style="align-items: center;padding: 5px;background-color: #1d1d1d;margin-top: 2.5px;margin-bottom: 2.5px; border-radius: 5px;">' +
                        '   <div class="col-lg-3" style="padding: 0px;"><img style="border-radius:100%;width:25px;" src="'+ constuctionPairs[p].baseCurrency.img +'"><img style="width:25px;margin: -5px;border-radius:100%;" src="'+ constuctionPairs[p].quoteCurrency.img +'"></div>' +
                        '   <div class="col-lg-5"> '+ closedSide +'</div>' +
                        '   <div class="col-lg-4"><button class="btn btn-lg btn-primary" style="cursor: pointer;    padding: 2px;" onclick="changePermitionsState(\''+ constuctionPairs[p].extraData.pairId +'\',\''+ constuctionPairs[p].baseCurrency.shortname +'\',\''+ constuctionPairs[p].quoteCurrency.shortname +'\');"> Activate </button></div>' +
                        '<div class="col-lg-3" style="padding:0px;"><img style="width:50px;" src="'+ constuctionPairs[p].extraData.exchangerInfos +'"></div>'+
                        '<div class="col-lg-5" style="padding:0px;text-align: center;" ><i style="color:grey;font-size:10px;">Closed '+resultedDifferance4+'' + extramessage+' </i></div>'+
                        '<div class="col-lg-4" style="padding:0px;text-align: center;">'+ pairInfoMessage +'</div>'+
                        '</div></div>';
                }
                $('#pairsChecker').html(responsePagination2 + resultWithdrawRequests);
                $('#paginationPairs').html(responsePagination);
            }
        });
    }

