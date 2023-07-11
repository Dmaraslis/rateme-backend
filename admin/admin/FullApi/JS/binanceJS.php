<script>

    function subAccounttransferBalance(type){
        if(type === 'sendAll'){
            $.ajax({
                url: "ajP-Binance.php",
                type: "POST",
                data: {action: 'sendAllToMainAccount'},
                dataType: "json",
                success: function (data) {
                    console.log(data);
                }
            });


        }
        if(type === 'receive'){


        }
    }


    load_deposit_history(1,15,15,0);

    $(document).ready(function() {
        $('.collapse-link').on('click',function(){
            var selectedId = $(this).attr('id');
            if(selectedId === 'loadActiveOrders'){
                if($('#activeOrders').html().length <= 200){
                    /* Function gia to active Orders*/
                }
            }
            if(selectedId === 'loadWithdrawHistory'){
                if($('#withdrawHistory').html().length <= 200){
                    load_withdraw_history();
                }
            }
            if(selectedId === 'loadOrdersHistory'){
                if($('#ordersHistory').html().length <= 200){
                    load_orders_history();
                }
            }
            if(selectedId === 'loadBalances'){
                if($('#balances').html().length <= 200){
                    load_balances();
                }
            }
        });
        $('.refresh-link').on('click',function(){
            var selectedId = $(this).attr('id');
            if(selectedId === 'depositHistoryRef'){
                $('#depositHistory').html('<div class="sk-spinner sk-spinner-wave">' +
                    '                <div class="sk-rect1"></div>' +
                    '                <div class="sk-rect2"></div>' +
                    '                <div class="sk-rect3"></div>' +
                    '                <div class="sk-rect4"></div>' +
                    '                <div class="sk-rect5"></div>' +
                    '            </div>');
                load_deposit_history(1,15,15,0);
            }
            if(selectedId === 'activeOrdersRef'){
                $('#activeOrders').html('<div class="sk-spinner sk-spinner-wave">' +
                    '                <div class="sk-rect1"></div>' +
                    '                <div class="sk-rect2"></div>' +
                    '                <div class="sk-rect3"></div>' +
                    '                <div class="sk-rect4"></div>' +
                    '                <div class="sk-rect5"></div>' +
                    '            </div>');
                /* Function gia to active Orders*/
            }
            if(selectedId === 'withdrawHistoryRef'){
                $('#withdrawHistory').html('<div class="sk-spinner sk-spinner-wave">' +
                    '                <div class="sk-rect1"></div>' +
                    '                <div class="sk-rect2"></div>' +
                    '                <div class="sk-rect3"></div>' +
                    '                <div class="sk-rect4"></div>' +
                    '                <div class="sk-rect5"></div>' +
                    '            </div>');
                load_withdraw_history();
            }
            if(selectedId === 'ordersHistoryRef'){
                $('#ordersHistory').html('<div class="sk-spinner sk-spinner-wave">' +
                    '                <div class="sk-rect1"></div>' +
                    '                <div class="sk-rect2"></div>' +
                    '                <div class="sk-rect3"></div>' +
                    '                <div class="sk-rect4"></div>' +
                    '                <div class="sk-rect5"></div>' +
                    '            </div>');
                load_orders_history();
            }
            if(selectedId === 'balancesRef'){
                $('#balances').html('<div class="sk-spinner sk-spinner-wave">' +
                    '                <div class="sk-rect1"></div>' +
                    '                <div class="sk-rect2"></div>' +
                    '                <div class="sk-rect3"></div>' +
                    '                <div class="sk-rect4"></div>' +
                    '                <div class="sk-rect5"></div>' +
                    '            </div>');
                load_balances();
            }

        });
        load_balances();
        check_networks();
    });
    function placeOrderR(){
        swal({
            title: 'ORDER Under Construction | Assign & Withdraw Works OK',
            type: 'warning',
            showCancelButton: true,
            showConfirmButton: false,
            customClass: "customSwallClass"
        });
    }

    function placeOrder(priceType) {
        var transactionId = '<?=$_GET['transactionId']?>';
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var giveCoin = '<?=$_GET['giveCoin']?>';
        swal({
            title: 'loading...',
            type: 'warning',
            showCancelButton: false,
            showConfirmButton: false,
            customClass: "customSwallClass"
        });
        var OrderAction = function(buyType,newBtcGetVal) {
            $.ajax({
                url: "../AJAXPOSTS",
                type: "POST",
                data: {action: 'gatherSwaps', orderBy: 'id', value: transactionId,mode:'forPanel'},
                dataType: "json",
                success: function (data) {
                    if (data.response[0].btcGetVal > 0 && data.response[0].exchangerName !== 'MoonPay') {
                        if(buyType === 'retryFirstBuyCross'){
                            var depositAmount = parseFloat(newBtcGetVal);
                            var withdrawAmount = parseFloat(data.response[0].btcGetVal);
                            giveCoin = '<?=$_GET['giveCoin']?>';
                            getCoin = 'BTC';
                            var orderType = 'firstCross';
                        }
                        if(buyType === 'retryFirstBuyNormal'){
                            var depositAmount = parseFloat(newBtcGetVal);
                            var withdrawAmount = parseFloat(data.response[0].resultedReceivingAmount);
                            giveCoin = '<?=$_GET['giveCoin']?>';
                            getCoin = '<?=$_GET['getCoin']?>';
                            var orderType = 'firstNormal';
                        }
                        if(buyType === 'firstBuy'){
                            var depositAmount = parseFloat(data.response[0].depositAmount);
                            var withdrawAmount = parseFloat(data.response[0].btcGetVal);
                            giveCoin = '<?=$_GET['giveCoin']?>';
                            getCoin = 'BTC';
                            var orderType = 'firstCross';
                        }
                        if(buyType === 'secondBuy'){
                            var depositAmount = parseFloat(newBtcGetVal);
                            var withdrawAmount = parseFloat(data.response[0].resultedReceivingAmount);
                            giveCoin = 'BTC';
                            getCoin = '<?=$_GET['getCoin']?>';
                            var orderType = 'secondCross';
                        }
                        swal({
                            title: priceType.toUpperCase() + ' Order',
                            text: 'Are you sure you want to buy ' + withdrawAmount + ' ' + getCoin + ' and change the swap state to swapping?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "Yes, proceed!",
                            customClass: "customSwallClass",
                            closeOnConfirm: false
                        }, function () {
                            swal({
                                title: 'loading...',
                                type: 'warning',
                                showCancelButton: false,
                                showConfirmButton: false,
                                customClass: "customSwallClass"
                            });
                            var recall = function () {
                                $.ajax({
                                    url: "ajP-Binance.php",
                                    type: "POST",
                                    data: {
                                        action: 'placeOrder',
                                        type: priceType,
                                        giveCoin: giveCoin,
                                        getCoin: getCoin,
                                        depositAmount: depositAmount,
                                        withdrawAmount: withdrawAmount,
                                        apiKey: apiKey,
                                        apiSecret: apiSecret,
                                        txId: transactionId,
                                        orderType: orderType
                                    },
                                    dataType: "json",
                                    success: function (data2) {
                                        if (data2.response !== 'ERROR') {
                                            if (data2.response.code) {
                                                swal({
                                                    title: priceType.toUpperCase() + ' Order FAILED',
                                                    text: data2.response.msg,
                                                    type: 'error',
                                                    showCancelButton: true,
                                                    confirmButtonColor: "#30966c",
                                                    confirmButtonText: "Retry order",
                                                    customClass: "customSwallClass",
                                                    closeOnConfirm: false
                                                }, function(data){
                                                    if(data){
                                                        swal({
                                                            title: 'loading...',
                                                            type: 'warning',
                                                            showCancelButton: false,
                                                            showConfirmButton: false,
                                                            customClass: "customSwallClass"
                                                        });
                                                        if(buyType === 'secondBuy'){
                                                            OrderAction(buyType,newBtcGetVal);
                                                        }else{
                                                            OrderAction(buyType);
                                                        }
                                                    }
                                                });
                                            } else {
                                                var extraFills = '';
                                                for (var i = 0; i < data2.ordersCompleted.length; i++) {
                                                    extraFills += '\n' + (i + 1) + ') Price: ' + data2.ordersCompleted[i].price + ' / Qty: ' + data2.ordersCompleted[i].origQty;
                                                }
                                                if(buyType === 'firstBuy') {
                                                    swal({
                                                        title: priceType.toUpperCase() + ' Order was Successful',
                                                        text: 'Transaction is now on swapping state.\n Order Analyze:\n' + extraFills,
                                                        type: 'success',
                                                        showCancelButton: true,
                                                        confirmButtonColor: "#30966c",
                                                        confirmButtonText: "Continue to 2nd buy",
                                                        customClass: "customSwallClass",
                                                        closeOnConfirm: false
                                                    },function(inpu){
                                                        if(inpu === true){
                                                            swal({
                                                                title: 'loading...',
                                                                type: 'warning',
                                                                showCancelButton: false,
                                                                showConfirmButton: false,
                                                                customClass: "customSwallClass"
                                                            });
                                                            OrderAction('secondBuy',data2.receivedAmountMsg);
                                                        }
                                                    });
                                                    toastr.success('Your MARKET order is created! Transaction is now on swapping state', 'Trade Successful');
                                                }else{
                                                    swal({
                                                        title: priceType.toUpperCase() + ' Order was Successful',
                                                        text: 'Transaction is now on swapping state.\n Order Analyze:\n' + extraFills,
                                                        type: 'success',
                                                        showCancelButton: true,
                                                        confirmButtonColor: "#30966c",
                                                        confirmButtonText: "Continue to Withdraw",
                                                        customClass: "customSwallClass",
                                                        closeOnConfirm: false
                                                    },function(inpu){
                                                        swal({
                                                            title: 'loading...',
                                                            type: 'warning',
                                                            showCancelButton: false,
                                                            showConfirmButton: false,
                                                            customClass: "customSwallClass"
                                                        });
                                                        window.parent.$(window.parent.document).trigger('refresh_part_transaction_details');
                                                        withdraw();
                                                    });
                                                    toastr.success('Your MARKET order is created! Transaction is now on swapping state', 'Trade Successful');
                                                }
                                            }
                                        } else {
                                            swal({
                                                title: priceType.toUpperCase() + " Order can't proceed",
                                                text: 'You must detect deposit first in order to create order',
                                                type: 'error',
                                                showCancelButton: false,
                                                confirmButtonColor: "#30966c",
                                                confirmButtonText: "OK",
                                                customClass: "customSwallClass",
                                                closeOnConfirm: true
                                            });
                                        }
                                    },
                                    complete: function(completeData){
                                        setTimeout(function(){
                                            writeDownLog(completeData.responseJSON.orderResponse.side+ ' '+orderType,'<?=$_GET['transactionId']?>',JSON.stringify(completeData.responseJSON));
                                        },2000);
                                    }
                                });
                            };
                            recall();
                        });
                    }else{
                        if(data.response[0].exchangerName === 'MoonPay'){
                            var depositAmount = parseFloat(data.response[0].btcGetVal);
                        }else{
                            if(buyType === 'retryFirstBuyNormal'){
                                var depositAmount = parseFloat(newBtcGetVal);
                            }else{
                                var depositAmount = parseFloat(data.response[0].depositAmount);
                            }
                        }
                        var withdrawAmount = parseFloat(data.response[0].resultedReceivingAmount);
                        swal({
                            title: priceType.toUpperCase() + ' Order',
                            text: 'Are you sure you want to buy ' + withdrawAmount + ' ' + getCoin + ' and change the swap state to swapping?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "Yes, proceed!",
                            customClass: "customSwallClass",
                            closeOnConfirm: false
                        }, function () {
                            swal({
                                title: 'loading...',
                                type: 'warning',
                                showCancelButton: false,
                                showConfirmButton: false,
                                customClass: "customSwallClass"
                            });
                            var recall = function () {
                                $.ajax({
                                    url: "ajP-Binance.php",
                                    type: "POST",
                                    data: {
                                        action: 'placeOrder',
                                        type: priceType,
                                        giveCoin: giveCoin,
                                        getCoin: getCoin,
                                        depositAmount: depositAmount,
                                        withdrawAmount: withdrawAmount,
                                        apiKey: apiKey,
                                        apiSecret: apiSecret,
                                        txId: transactionId,
                                        orderType: 'firstNormal'
                                    },
                                    dataType: "json",
                                    success: function (data2) {
                                        if (data2.response === 'ERROR') {
                                            swal({
                                                title: priceType.toUpperCase() + " Order can't proceed",
                                                text: 'You must detect deposit first in order to create order',
                                                type: 'error',
                                                showCancelButton: false,
                                                confirmButtonColor: "#30966c",
                                                confirmButtonText: "OK",
                                                customClass: "customSwallClass",
                                                closeOnConfirm: true
                                            });
                                        } if(data2.response === 'Order is not full filled'){
                                            swal({
                                                title: priceType.toUpperCase() + " Order can't proceed",
                                                text: data2.stateResponse[0].msg,
                                                type: 'error',
                                                showCancelButton: false,
                                                confirmButtonColor: "#30966c",
                                                confirmButtonText: "OK",
                                                customClass: "customSwallClass",
                                                closeOnConfirm: true
                                            });
                                        }else {
                                            if(data2.response.msg === "Filter failure: LOT_SIZE"){
                                                swal({
                                                    title: priceType.toUpperCase() + ' Order FAILED',
                                                    text: data2.response.msg,
                                                    type: "error",
                                                    showCancelButton: true,
                                                    closeOnConfirm: false,
                                                    confirmButtonColor: "#f0b413",
                                                    confirmButtonText: "Try different amount",
                                                    cancelButtonText: "cancel",
                                                    customClass: "customSwallClass"
                                                }, function () {
                                                    swal({
                                                        title: "Type here the ammount of " + giveCoin + ' you want to trade',
                                                        type: "input",
                                                        showCancelButton: true,
                                                        closeOnConfirm: false,
                                                        confirmButtonColor: "#4fc900",
                                                        confirmButtonText: "Place Order",
                                                        inputPlaceholder: giveCoin + " Amount",
                                                        customClass: "customSwallClass"
                                                    }, function (inputValue) {
                                                        swal({
                                                            title: 'loading...',
                                                            type: 'warning',
                                                            showCancelButton: false,
                                                            showConfirmButton: false,
                                                            customClass: "customSwallClass"
                                                        });
                                                        OrderAction('retryFirstBuyNormal',inputValue);
                                                    })
                                                });
                                            }else if(data2.response.code) {
                                                swal({
                                                    title: priceType.toUpperCase() + ' Order FAILED',
                                                    text: data2.response.msg,
                                                    type: 'error',
                                                    showCancelButton: false,
                                                    confirmButtonColor: "#30966c",
                                                    confirmButtonText: "OK",
                                                    customClass: "customSwallClass",
                                                    closeOnConfirm: true
                                                });
                                            } else {
                                                var extraFills = '';
                                                for (var i = 0; i < data2.ordersCompleted.length; i++) {
                                                    extraFills += '\n' + (i + 1) + ') Price: ' + data2.ordersCompleted[i].price + ' / Qty: ' + data2.ordersCompleted[i].origQty;
                                                }
                                                swal({
                                                    title: priceType.toUpperCase() + ' Order was Successful',
                                                    text: 'Transaction is now on swapping state.\n Order Analyze:\n' + extraFills,
                                                    type: 'success',
                                                    showCancelButton: true,
                                                    confirmButtonColor: "#30966c",
                                                    confirmButtonText: "Continue to Withdraw",
                                                    customClass: "customSwallClass",
                                                    closeOnConfirm: false
                                                },function(){
                                                    swal({
                                                        title: 'loading...',
                                                        type: 'warning',
                                                        showCancelButton: false,
                                                        showConfirmButton: false,
                                                        customClass: "customSwallClass"
                                                    });
                                                    window.parent.$(window.parent.document).trigger('refresh_part_transaction_details');
                                                    withdraw();
                                                });
                                                toastr.success('Your LIMIT order is created! Transaction is now on swapping state', 'Trade Successful');
                                            }
                                        }
                                    },
                                    complete: function(completeData){
                                        setTimeout(function(){
                                            writeDownLog('ORDER FIRST NORMAL','<?=$_GET['transactionId']?>',JSON.stringify(completeData.responseJSON));
                                        },2000);
                                    }
                                });
                            };
                            recall();
                        });
                    }
                }
            });
        };
        var transferBalance = function(txId,moveType){
            swal({
                title: 'Transfer Balance',
                text: 'Transfer balance '+moveType,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#30966c",
                confirmButtonText: "Yes, proceed!",
                customClass: "customSwallClass",
                closeOnConfirm: true
            }, function (dato) {
                if(dato){
                    $.ajax({
                        url: "ajP-Binance.php",
                        type: "POST",
                        data: {action: 'transferBalance', txId: txId, type:moveType, apiKey: apiKey, apiSecret: apiSecret},
                        dataType: "json",
                        success: function (data) {
                            if(data.response.code){
                                swal({
                                    title: 'Transfer failed',
                                    text: data.responseMsg,
                                    type: 'error',
                                    showCancelButton: true,
                                    confirmButtonColor: "#30966c",
                                    confirmButtonText: "Try again",
                                    customClass: "customSwallClass",
                                    closeOnConfirm: true
                                }, function (dato) {
                                    if(dato){
                                        setTimeout(function(){ placeOrder('market'); },500);
                                    }
                                });
                            }else{
                                placeOrder('market');
                            }
                        }
                    });
                }
            });
        };


        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkOrderInfo', txId: transactionId,orderInfoWay:'deposit' },
            dataType: "json",
            success: function (data) {
                if(data.response.withdrawExplTxId === null){
                    if(data.responseState === 'OK' && data.response.depositExplTxId !== ''){
                        var continueOrder = false;
                        if(data.extraResponse.orderMoves.move === false) {
                            var continueOrder = false;
                            $('.depositLine').each(function (i) {
                                if ($(this).hasClass('animTran')) {
                                    selectThisToAssignOnTransaction('depositHistory', 'assign', this, i, 'abnormal');
                                    continueOrder = true;
                                }
                            });
                            if (continueOrder) {
                                setTimeout(function () { OrderAction('firstBuy'); }, 500);
                            } else {
                                swal({
                                    title: 'Deposit history must be on page that assign is printed',
                                    type: 'error',
                                    showCancelButton: true,
                                    showConfirmButton: false,
                                    customClass: "customSwallClass"
                                });
                            }
                        }else{
                            if(data.extraResponse.orderMoves.move === 'transferBalance'){
                                transferBalance(transactionId,data.extraResponse.orderMoves.moveType);
                            }
                        }
                    }else{
                        swal({
                            title:"Order can't proceed",
                            text: 'You must assign a deposit first in order to place order',
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "OK",
                            customClass: "customSwallClass",
                            closeOnConfirm: true
                        });
                    }
                }else{
                    swal({
                        title: 'This Order Is Completed you cant buy/sell again',
                        type: 'error',
                        showCancelButton: true,
                        showConfirmButton: false,
                        customClass: "customSwallClass"
                    });
                }
            }
        });
    }

    function withdraw_custom_network(){
        var selectedNetwork =  window.parent.$('#selectedNetworkForWithdraw',window.parent.document).html();
        var networkFullName =  window.parent.$('#selectedNetworkFullNameForWithdraw',window.parent.document).html();
        swal({
            title: 'Changing Network...',
            type: 'warning',
            showCancelButton: false,
            showConfirmButton: false,
            customClass: "customSwallClass"
        });
        var txId = '<?=$_REQUEST['transactionId']?>';
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var txId = '<?=$_REQUEST['transactionId']?>';
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var ressCall = function(timeType,newWithdrawAmount,network) {
            if(screen.width > '700'){
                var withdrawAmount =  window.parent.$('.withdrawAmountFront:eq(1)',window.parent.document).val();
            } else{
                var withdrawAmount =  window.parent.$('.withdrawAmountFront:eq(0)',window.parent.document).val();
            }
            $.ajax({
                url: "ajP-Binance.php",
                type: "POST",
                data: {action: 'checkOrderInfo', txId: txId, apiKey: apiKey, apiSecret: apiSecret},
                dataType: "json",
                success: function (data) {
                    if (data.responseState !== 'ERROR' && data.responseState !== 'ERROR2') {
                        if (data.response.destinationTagPhrase === "is not required") {
                            var extraText = '';
                        } else {
                            var extraText = '\n Destination Tag ' + data.response.destinationTagPhrase;
                        }
                        swal({
                            title: 'Withdraw Request of ' + withdrawAmount + ' ' + data.response.getCoin + ' on network: '+selectedNetwork+' ('+ networkFullName +')',
                            text: 'WITHDRAW WALLET: ' + data.response.clientRefundAddress + extraText,
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "Complete Withdraw",
                            cancelButtonText: "cancel",
                            customClass: "customSwallClass",
                            closeOnConfirm: false
                        }, function () {
                            swal({
                                title: 'loading...',
                                type: 'warning',
                                showCancelButton: false,
                                showConfirmButton: false,
                                customClass: "customSwallClass"
                            });
                            var clientWithdrawWallet = data.response.clientRefundAddress;
                            var destinationTagPhrase = data.response.destinationTagPhrase;
                            $.ajax({
                                url: "ajP-Binance.php",
                                type: "POST",
                                data: {
                                    action: 'withdrawAction',
                                    txId: txId,
                                    withdrawCoin: getCoin,
                                    amount: withdrawAmount,
                                    wallet: clientWithdrawWallet,
                                    destinationTagPhrase: destinationTagPhrase,
                                    apiKey: apiKey,
                                    apiSecret: apiSecret,
                                    network: network,
                                    failedTry: 1
                                },
                                dataType: "json",
                                success: function (data2) {
                                    console.log(data2,'EDW');
                                    if(data2.responseState === 'ERROR' && data2.response === 'Address verification failed' || data2.response === 'Address verification failed.'){
                                        window.parent.$(window.parent.document).trigger('failedWithdraw');
                                    }else if(data2.responseState === 'ERROR4'){
                                        swal({
                                            title: "Withdraw Request Failed",
                                            text: data2.response,
                                            type: "error",
                                            showCancelButton: true,
                                            showConfirmButton: false,
                                            customClass: "customSwallClass"
                                        });
                                    }else if (data2.responseState === 'ERROR') {
                                        swal({
                                            title: "Withdraw Request Failed",
                                            text: data2.response,
                                            type: "error",
                                            showCancelButton: true,
                                            closeOnConfirm: false,
                                            confirmButtonColor: "#f0b413",
                                            confirmButtonText: "Withdraw Custom Amount",
                                            customClass: "customSwallClass"
                                        }, function () {
                                            swal({
                                                title: "Type here the ammount of " + getCoin + ' you want to withdraw',
                                                type: "input",
                                                showCancelButton: true,
                                                closeOnConfirm: false,
                                                confirmButtonColor: "#4fc900",
                                                confirmButtonText: "Withdraw",
                                                inputPlaceholder: getCoin + " Amount",
                                                customClass: "customSwallClass"
                                            }, function (inputValue) {
                                                swal({
                                                    title: 'loading...',
                                                    type: 'warning',
                                                    showCancelButton: false,
                                                    showConfirmButton: false,
                                                    customClass: "customSwallClass"
                                                });
                                                ressCall('secondTime',inputValue,network);
                                            })
                                        });
                                    } else {
                                        toastr.success('Withdraw of ' + withdrawAmount + ' ' + getCoin + ' was sent.', 'Deposit History');
                                        swal({
                                            title: "Withdraw Request Succeed",
                                            text: 'Withdraw of ' + withdrawAmount + ' ' + getCoin + ' was sent on: \n' + clientWithdrawWallet ,
                                            type: "success",
                                            showCancelButton: false,
                                            closeOnConfirm: true,
                                            confirmButtonColor: "#6f6f6f",
                                            confirmButtonText: "OK",
                                            customClass: "customSwallClass"
                                        });
                                    }
                                },
                                complete: function(completeData){
                                    setTimeout(function(){
                                        writeDownLog('WITHDRAW ACTION','<?=$_GET['transactionId']?>',JSON.stringify(completeData.responseJSON));
                                    },2000);
                                }
                            });
                        });
                    } else {
                        if (data.responseState === 'ERROR2') {
                            swal({
                                title: "Withdraw Can't proceed",
                                text: data.response,
                                type: 'error',
                                showCancelButton: false,
                                confirmButtonColor: "#30966c",
                                confirmButtonText: "OK",
                                customClass: "customSwallClass",
                                closeOnConfirm: true
                            });
                        } else if (data.responseState === 'ERROR') {
                            swal({
                                title: 'Withdraw Request Failed!',
                                text: data.response,
                                type: 'error',
                                showCancelButton: true,
                                showConfirmButton: false,
                                cancelButtonText: "OK",
                                customClass: "customSwallClass",
                                closeOnConfirm: true
                            });
                        }
                    }
                }
            });
        };

        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkOrderInfo', txId: txId},
            dataType: "json",
            success: function (data) {
                if (data.response.withdrawExplTxId === null) {
                    if (data.responseState === 'OK' && data.response.depositExplTxId !== '') {
                        ressCall('','',selectedNetwork);
                    } else {
                        swal({
                            title: "Withdraw can't proceed",
                            text: 'You must assign a deposit first place order and after make a withdraw',
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "OK",
                            customClass: "customSwallClass",
                            closeOnConfirm: true
                        });
                    }
                }else{
                    swal({
                        title: 'This Order Is Completed you cant withdraw again',
                        type: 'error',
                        showCancelButton: true,
                        showConfirmButton: false,
                        customClass: "customSwallClass"
                    });
                }
            }
        });
    }

    function withdraw(){
        load_balances();
        swal({
            title: 'loading...',
            type: 'warning',
            showCancelButton: false,
            showConfirmButton: false,
            customClass: "customSwallClass"
        });
        var txId = '<?=$_REQUEST['transactionId']?>';
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var ressCall = function(timeType,newWithdrawAmount) {
            if(screen.width > '700'){
                var withdrawAmount =  window.parent.$('.withdrawAmountFront:eq(1)',window.parent.document).val();
            } else{
                var withdrawAmount =  window.parent.$('.withdrawAmountFront:eq(0)',window.parent.document).val();
            }
            $.ajax({
                url: "ajP-Binance.php",
                type: "POST",
                data: {action: 'checkOrderInfo', txId: txId, apiKey: apiKey, apiSecret: apiSecret},
                dataType: "json",
                success: function (data) {
                    if (data.responseState !== 'ERROR' && data.responseState !== 'ERROR2') {
                        if (data.response.destinationTagPhrase === "is not required") {
                            var extraText = '';
                        } else {
                            var extraText = '\n Destination Tag ' + data.response.destinationTagPhrase;
                        }
                        swal({
                            title: 'Withdraw Request of ' + withdrawAmount + ' ' + data.response.getCoin,
                            text: 'WITHDRAW WALLET: ' + data.response.clientRefundAddress + extraText,
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "Complete Withdraw",
                            cancelButtonText: "cancel",
                            customClass: "customSwallClass",
                            closeOnConfirm: false
                        }, function () {
                            swal({
                                title: 'loading...',
                                type: 'warning',
                                showCancelButton: false,
                                showConfirmButton: false,
                                customClass: "customSwallClass"
                            });
                            var clientWithdrawWallet = data.response.clientRefundAddress;
                            var destinationTagPhrase = data.response.destinationTagPhrase;
                            $.ajax({
                                url: "ajP-Binance.php",
                                type: "POST",
                                data: {
                                    action: 'withdrawAction',
                                    txId: txId,
                                    withdrawCoin: getCoin,
                                    amount: withdrawAmount,
                                    wallet: clientWithdrawWallet,
                                    destinationTagPhrase: destinationTagPhrase,
                                    apiKey: apiKey,
                                    apiSecret: apiSecret
                                },
                                dataType: "json",
                                success: function (data2) {
                                    if(data2.responseState === 'ERROR' && data2.response === 'Address verification failed.' || data2.response === 'Address verification failed'){
                                        window.parent.$(window.parent.document).trigger('failedWithdraw');
                                    }else if(data2.responseState === 'ERROR4'){
                                        swal({
                                            title: "Withdraw Request Failed",
                                            text: data2.response,
                                            type: "error",
                                            showCancelButton: true,
                                            showConfirmButton: false,
                                            customClass: "customSwallClass"
                                        });
                                    }else if (data2.responseState === 'ERROR') {
                                        swal({
                                            title: "Withdraw Request Failed",
                                            text: data2.response,
                                            type: "error",
                                            showCancelButton: true,
                                            closeOnConfirm: false,
                                            confirmButtonColor: "#f0b413",
                                            confirmButtonText: "Withdraw Custom Amount",
                                            customClass: "customSwallClass"
                                        }, function () {
                                            swal({
                                                title: "Type here the ammount of " + getCoin + ' you want to withdraw',
                                                type: "input",
                                                showCancelButton: true,
                                                closeOnConfirm: false,
                                                confirmButtonColor: "#4fc900",
                                                confirmButtonText: "Withdraw",
                                                inputPlaceholder: getCoin + " Amount",
                                                customClass: "customSwallClass"
                                            }, function (inputValue) {
                                                swal({
                                                    title: 'loading...',
                                                    type: 'warning',
                                                    showCancelButton: false,
                                                    showConfirmButton: false,
                                                    customClass: "customSwallClass"
                                                });
                                                ressCall('secondTime',inputValue);
                                            })
                                        });
                                    } else {
                                        toastr.success('Withdraw of ' + withdrawAmount + ' ' + getCoin + ' was sent.', 'Deposit History');
                                        swal({
                                            title: "Withdraw Request Succeed",
                                            text: 'Withdraw of ' + withdrawAmount + ' ' + getCoin + ' was sent on: \n' + clientWithdrawWallet ,
                                            type: "success",
                                            showCancelButton: false,
                                            closeOnConfirm: true,
                                            confirmButtonColor: "#6f6f6f",
                                            confirmButtonText: "OK",
                                            customClass: "customSwallClass"
                                        });
                                    }
                                },
                                complete: function(completeData){
                                    setTimeout(function(){
                                        writeDownLog('WITHDRAW ACTION','<?=$_GET['transactionId']?>',JSON.stringify(completeData.responseJSON));
                                    },2000);
                                }
                            });
                        });
                    } else {
                        if (data.responseState === 'ERROR2') {
                            swal({
                                title: "Withdraw Can't proceed",
                                text: data.response,
                                type: 'error',
                                showCancelButton: false,
                                confirmButtonColor: "#30966c",
                                confirmButtonText: "OK",
                                customClass: "customSwallClass",
                                closeOnConfirm: true
                            });
                        } else if (data.responseState === 'ERROR') {
                            swal({
                                title: 'Withdraw Request Failed!',
                                text: data.response,
                                type: 'error',
                                showCancelButton: true,
                                showConfirmButton: false,
                                cancelButtonText: "OK",
                                customClass: "customSwallClass",
                                closeOnConfirm: true
                            });
                        }
                    }
                }
            });
        };

        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkOrderInfo', txId: txId},
            dataType: "json",
            success: function (data) {
                if (data.response.withdrawExplTxId === null) {
                    if (data.responseState === 'OK' && data.response.depositExplTxId !== '') {
                        ressCall('firstTime');
                    } else {
                        swal({
                            title: "Withdraw can't proceed",
                            text: 'You must assign a deposit first place order and after make a withdraw',
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "OK",
                            customClass: "customSwallClass",
                            closeOnConfirm: true
                        });
                    }
                }else{
                    swal({
                        title: 'This Order Is Completed you cant withdraw again',
                        type: 'error',
                        showCancelButton: true,
                        showConfirmButton: false,
                        customClass: "customSwallClass"
                    });
                }
            }
        });
    }

    function refund(type){
        swal({
            title: 'loading...',
            type: 'warning',
            showCancelButton: false,
            showConfirmButton: false,
            customClass: "customSwallClass"
        });
        var txId = '<?=$_REQUEST['transactionId']?>';
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var ressCall = function(timeType,newWithdrawAmount) {
            $.ajax({
                url: "ajP-Binance.php",
                type: "POST",
                data: {action: 'checkOrderInfo', txId: txId, apiKey: apiKey, apiSecret: apiSecret},
                dataType: "json",
                success: function (data) {
                    if (data.responseState !== 'ERROR' && data.responseState !== 'ERROR2') {
                        if (data.response.destinationTagPhrase === "is not required") {
                            var extraText = '';
                        } else {
                            var extraText = '\n Destination Tag ' + data.response.destinationTagPhrase;
                        }

                        var clientWithdrawWallet = data.response.clientReceiveAddress;
                        var withdrawAmount = newWithdrawAmount;
                        var destinationTagPhrase = data.response.destinationTagPhrase;
                        var getCoin = data.response.givenCoin;
                        swal({
                            title: 'Refund Request of ' + withdrawAmount + ' ' + getCoin,
                            text: 'WITHDRAW WALLET: ' + clientWithdrawWallet + extraText,
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "Complete Refund",
                            cancelButtonText: "cancel",
                            customClass: "customSwallClass",
                            closeOnConfirm: false
                        }, function () {
                            swal({
                                title: 'loading...',
                                type: 'warning',
                                showCancelButton: false,
                                showConfirmButton: false,
                                customClass: "customSwallClass"
                            });
                            var refundTypeSelectedPc =  window.parent.$('.refundSelectedTypeMobile',window.parent.document).find("option:selected").val();
                            var refundTypeSelectedMobile =  window.parent.$('.refundSelectedType',window.parent.document).find("option:selected").val();
                            var customRefundAmount =  window.parent.$('.refundNetAmount:eq(0)',window.parent.document).html();
                            if(refundTypeSelectedMobile.length > 0){
                                var selectedRefundType = refundTypeSelectedMobile;
                            }else{
                                var selectedRefundType = refundTypeSelectedPc;
                            }
                            $.ajax({
                                url: "ajP-Binance.php",
                                type: "POST",
                                data: {
                                    action: 'refundAction',
                                    txId: txId,
                                    withdrawCoin: getCoin,
                                    amount: withdrawAmount,
                                    wallet: clientWithdrawWallet,
                                    destinationTagPhrase: destinationTagPhrase,
                                    apiKey: apiKey,
                                    apiSecret: apiSecret,
                                    refundType:selectedRefundType,
                                    customRefundAmount:customRefundAmount
                                },
                                dataType: "json",
                                success: function (data2) {
                                    if(data2.responseState === 'ERROR4'){
                                        swal({
                                            title: "Refund Request Failed",
                                            text: data2.response,
                                            type: "error",
                                            showCancelButton: true,
                                            showConfirmButton: false,
                                            customClass: "customSwallClass"
                                        });
                                    }else if (data2.responseState === 'ERROR') {
                                        swal({
                                            title: "Refund Request Failed",
                                            text: data2.response.message,
                                            type: "error",
                                            showCancelButton: true,
                                            closeOnConfirm: false,
                                            confirmButtonColor: "#f0b413",
                                            confirmButtonText: "Refund Custom Amount",
                                            customClass: "customSwallClass"
                                        }, function () {
                                            swal({
                                                title: "Type here the ammount of " + getCoin + ' you want to Refund',
                                                type: "input",
                                                showCancelButton: true,
                                                closeOnConfirm: false,
                                                confirmButtonColor: "#4fc900",
                                                confirmButtonText: "Refund",
                                                inputPlaceholder: getCoin + " Amount",
                                                customClass: "customSwallClass"
                                            }, function (inputValue) {
                                                swal({
                                                    title: 'loading...',
                                                    type: 'warning',
                                                    showCancelButton: false,
                                                    showConfirmButton: false,
                                                    customClass: "customSwallClass"
                                                });
                                                ressCall('secondTime',inputValue);
                                            })
                                        });
                                    } else {
                                        toastr.success('Refund of ' + data2.response.amount + ' ' + data2.response.currency_code + ' was sent.', 'Deposit History');
                                        swal({
                                            title: "Refund Request Succeed",
                                            text: 'Refund of ' + data2.response.amount + ' ' + data2.response.currency_code + ' was sent on: \n' + data2.response.withdrawal_address.address + '\nStatus: ' + data2.response.status,
                                            type: "success",
                                            showCancelButton: false,
                                            closeOnConfirm: true,
                                            confirmButtonColor: "#6f6f6f",
                                            confirmButtonText: "OK",
                                            customClass: "customSwallClass"
                                        });
                                    }
                                },
                                complete:function(completeData){
                                    setTimeout(function(){
                                        writeDownLog('REFUND ACTION','<?=$_GET['transactionId']?>',JSON.stringify(completeData.responseJSON));
                                    },2000);
                                }
                            });
                        });
                    } else {
                        if (data.responseState === 'ERROR2') {
                            swal({
                                title: "Refund Can't proceed",
                                text: data.response,
                                type: 'error',
                                showCancelButton: false,
                                confirmButtonColor: "#30966c",
                                confirmButtonText: "OK",
                                customClass: "customSwallClass",
                                closeOnConfirm: true
                            });
                        } else if (data.responseState === 'ERROR') {
                            swal({
                                title: 'Refund Request Failed!',
                                text: data.response,
                                type: 'error',
                                showCancelButton: true,
                                showConfirmButton: false,
                                cancelButtonText: "OK",
                                customClass: "customSwallClass",
                                closeOnConfirm: true
                            });
                        }
                    }
                }
            });
        };

        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkOrderInfo', txId: txId},
            dataType: "json",
            success: function (data) {
                if (data.response.withdrawExplTxId === null) {
                    if (data.responseState === 'OK' && data.response.depositExplTxId !== '') {
                        var boxWithdrawAmount =  window.parent.$('.refundNetAmount:eq(0)',window.parent.document).html();
                        ressCall('secondTime',boxWithdrawAmount,data);
                    } else {
                        swal({
                            title: "Refund can't proceed",
                            text: 'You must assign a deposit first place order and after make a Refund',
                            type: 'error',
                            showCancelButton: false,
                            confirmButtonColor: "#30966c",
                            confirmButtonText: "OK",
                            customClass: "customSwallClass",
                            closeOnConfirm: true
                        });
                    }
                }else{
                    swal({
                        title: 'This Order Is Completed you cant Refund again',
                        type: 'error',
                        showCancelButton: true,
                        showConfirmButton: false,
                        customClass: "customSwallClass"
                    });
                }
            }
        });
    }


    function changeDepositPage(item,pageResultsCount,pageLoad){
        var movingByNum = true;
        var sendRequest = false;
        var pageElements = $('.footable-page-href');
        var selectedElement = $('.selected-footable').html();
        if(item === 'next'){
            if(pageElements.length-3 !== parseInt(selectedElement) + 1){
                var selectorMove = parseInt(selectedElement) + 2;
            }else{
                var selectorMove = parseInt(selectedElement) + 1;
            }
            movingByNum = false;
        }
        if(item === 'prev'){
            if(parseInt(selectedElement) > 1){
                var selectorMove = parseInt(selectedElement);
            }else{
                var selectorMove = parseInt(selectedElement) + 1;
            }
            movingByNum = false;
        }
        if(item === 'last'){
            var selectorMove = pageElements.length-3;
            movingByNum = false;
        }
        if(item === 'first'){
            var selectorMove = 2;
            movingByNum = false;
        }
        if(movingByNum){
            var selectorMove = item+1;
        }

        var previewedPage = selectorMove - 1;
        var limit = pageResultsCount;
        var offset = (previewedPage * pageResultsCount) - pageResultsCount;

        if(parseInt(item) >= 0){
            if(parseInt(selectedElement) !== parseInt(item)){ sendRequest = true; }
        }else{
            if(item === 'prev') {
                if (parseInt(selectedElement) > 1) { sendRequest = true; }
            }
            if(item === 'next'){
                if(pageElements.length-3 !== parseInt(selectedElement) + 1){ sendRequest = true; }
            }
            if(item === 'last'){
                if(parseInt(selectedElement) !== pageElements.length-4){ sendRequest = true; }
            }
            if(item === 'first'){
                if(parseInt(selectedElement) !== 1){ sendRequest = true; }
            }
        }


        if(sendRequest){
            load_deposit_history(previewedPage,pageLoad,limit,offset);
            $('.selected-footable').removeClass('selected-footable').attr('style', 'background: #486179 !important');
            $('.footable-page-href:eq('+ selectorMove +')').addClass('selected-footable').attr('style', 'background: #00c383 !important');
        }



    }

    function load_deposit_history(previewPage,pageLoad,pageResultsCount,offset){
        $('#depositHistory').html(' <div class="sk-spinner sk-spinner-wave"><div class="sk-rect1"></div><div class="sk-rect2"></div><div class="sk-rect3"></div><div class="sk-rect4"></div><div class="sk-rect5"></div></div>');
        var transactionId = "<?=$_REQUEST['transactionId']?>";
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var giveCoin = '<?=$_GET['giveCoin']?>';
        var depositAmount = '<?=$_GET['depositAmount']?>';
        var ourReceiveAddress = '<?=$_GET['ourReceiveAddress']?>';
        var destinationTagPhrase = '<?=$_GET['destinationTagPhrase']?>';
        var extraHtml = '';
        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkDepositHistory', transactionId:transactionId,giveCoin:giveCoin,apiKey:apiKey,apiSecret:apiSecret,pageResultsCount:pageResultsCount,offset:offset,ourReceiveAddress:ourReceiveAddress,destinationTagPhrase:destinationTagPhrase},
            dataType: "json",
            success: function (data) {
                if(data.responseState === 'ERROR'){
                    swal({
                        title: 'API ERROR',
                        text: data.response,
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonColor: "#30966c",
                        confirmButtonText: "OK",
                        customClass: "customSwallClass",
                        closeOnConfirm: true
                    });
                }else{
                    var extraHtml = '' +
                        '<table class="footable footable-custom2 table table-stripped toggle-arrow-tiny" data-page-size="20" style="font-size:12px;">' +
                        '  <thead>' +
                        '        <tr>' +
                        '        <th>Actions</th>' +
                        '        <th>Insta.Tx</th>' +
                        '        <th data-hide="pair">Deposit Amount</th>' +
                        '        <th data-hide="executedAmount">Confirmations</th>' +
                        '        <th data-hide="executedAmount">Status</th>' +
                        '        <th data-hide="depositAmount" >txId</th>' +
                        '    </tr>' +
                        '    </thead><tbody>';

                    for (var i = 0; i < data.response.length ; i++) {
                        var color = '';
                        var backColor = '';
                        var additionTypes = '';
                        var extraClass = 'trCustomClass';
                        if(data.response[i].noAnyColor){
                            if(data.response[i].isSelectedForThisTransaction){
                                extraClass += ' animTran';
                                backColor = 'background-color:#ef6a38c9;height: 0px;';
                                additionTypes = 'unAssign';
                            }else{
                                additionTypes = 'assign';
                            }
                        }else{
                            additionTypes = 'assign';
                            var perc = ((parseFloat(data.response[i].amount)/parseFloat(depositAmount)) * 100).toFixed(0);
                            if(perc >= 95 && perc <= 100){
                                color = 'color:black;';
                                backColor = 'background-color:#acc306;';
                            }
                            if(parseFloat(data.response[i].amount) === parseFloat(depositAmount)){
                                color = 'color:black;';
                                backColor = 'background-color:#1cc30f;';
                            }
                        }
                        var papa ='';
                        var url_string = window.location.href;
                        var url = new URL(url_string);
                        var c = url.searchParams.get("transactionId");
                        if(data.response[i].extraInfo && data.response[i].extraInfo !== 'Unused' && data.response[i].instaTxId && data.response[i].instaTxId !== c){
                            if(data.response[i].extraInfo && data.response[i].instaTxId === '9999999a'){
                                papa =  data.response[i].extraInfo ;
                            }else{
                                papa = '<td></td><td style="vertical-align: middle;">'+ data.response[i].extraInfo +'</td>';
                            }
                            extraHtml += '<tr class="depositLine trCustomAlreadyUsed">';
                        }else{
                           if(data.response[i].extraInfo && data.response[i].extraInfo === 'Unused'){
                                papa = '<td><button class="btn btn-warning btn-lg" onclick="selectThisToAssignOnTransaction(\'depositHistory\',\''+additionTypes+'\',this,\''+ i +'\',\'normal\')" style="font-size: 20px;padding: 5px; color: #1e1e1e;"><i class="fa fa-check-square" aria-hidden="true"></i></button></td><td><button class="btn btn-warning btn-lg" onclick="selectThisToAssignOnTransaction(\'depositHistory\',\''+additionTypes+'\',this,\''+ i +'\',\'AbnormalNoTx\')" style="font-size: 20px;padding: 4px;     margin-top: 1px;color: #1e1e1e;"><i class="fa fa-bell-slash" aria-hidden="true"></i></button></td>';
                            }else {
                                papa = '<td><button class="btn btn-warning btn-lg" onclick="selectThisToAssignOnTransaction(\'depositHistory\',\''+additionTypes+'\',this,\''+ i +'\',\'normal\')" style="font-size: 20px;padding: 5px; color: #1e1e1e;"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td><td style="vertical-align: middle;">Current Tx</td>';
                            }
                            extraHtml += '<tr class="depositLine '+extraClass+'" style="'+ color + backColor +'">';
                        }
                        extraHtml+=  papa +
                            '<td><img src="'+ data.response[i].coinImage +'" style="width:20px;">' +'  <span class="depositAmounts">'+  data.response[i].amount +'</span> '+ data.response[i].coin + '</td>' +
                            '<td>'+ data.response[i].confirmTimes +'</td>' +
                            '<td>'+ data.response[i].statusResponse +'</td>' +
                            '<td style="font-size:10px;">'+ data.response[i].txId +'</td>' +
                            '</tr>';
                    }
                    var extraPaginationButtons = '<li class="footable-page-arrow disabled"><a class="footable-page-href" data-page="first" href="javascript:void(0)" onclick="changeDepositPage(\'first\','+ pageResultsCount +','+ pageLoad +')">«</a></li>' +
                        '<li class="footable-page-arrow disabled"><a class="footable-page-href" data-page="prev" href="javascript:void(0)" onclick="changeDepositPage(\'prev\','+ pageResultsCount +','+ pageLoad +')">‹</a></li>';

                    for (var j = 1; j <= pageLoad; j++) {
                        if(j === previewPage){
                            extraPaginationButtons+= '<li class="footable-page"><a href="javascript:void(0)" class="selected-footable footable-page-href" onclick="changeDepositPage('+ j +','+ pageResultsCount +','+ pageLoad +')" style=" background-color:#00c383!important;">'+j+'</a></li>';
                        }else{
                            extraPaginationButtons+= '<li class="footable-page"><a class="footable-page-href" href="javascript:void(0)" onclick="changeDepositPage('+ j +','+ pageResultsCount +','+ pageLoad +')">'+j+'</a></li>';
                        }

                        if(j === pageLoad){
                            extraPaginationButtons += '<li class="footable-page-arrow"><a class="footable-page-href" data-page="next" href="javascript:void(0)" onclick="changeDepositPage(\'next\','+ pageResultsCount +','+ pageLoad +')">›</a></li>' +
                                '<li class="footable-page-arrow"><a data-page="last" class="footable-page-href" href="javascript:void(0)" onclick="changeDepositPage(\'last\','+ pageResultsCount +','+ pageLoad +')">»</a></li>';
                        }

                    }
                    extraHtml += '</tbody>' +
                        '    <tfoot>' +
                        '    <tr>' +
                        '    <td colspan="6">' +
                        '   <div> <ul class="pagination float-left">'+ extraPaginationButtons +'</ul></div>' +
                        '    </td>' +
                        '    </tr>' +
                        '    </tfoot>' +
                        '   </table>';
                    $('#depositHistory').html(extraHtml).css('overflow-y','scroll').css('max-height','600px');
                }
            }
        });
    }

    function selectThisToAssignOnTransaction(type,additionType,element,pos,typeOfassign){
        if(typeOfassign === 'abnormal'){
            var positions1 = element.childNodes[2].innerText;
            var positions2 = element.childNodes[5].innerText;
            var positions3 = element.childNodes[3].innerText;
            var positions4 = element.childNodes[4].innerText;
        }else{
            var positions1 = element.parentNode.parentNode.childNodes[2].innerText;
            var positions2 = element.parentNode.parentNode.childNodes[5].innerText;
            var positions3 = element.parentNode.parentNode.childNodes[3].innerText;
            var positions4 = element.parentNode.parentNode.childNodes[4].innerText;
        }
        var transactionId = "<?=$_REQUEST['transactionId']?>";
        if(type === 'depositHistory') {
            if (additionType === 'unAssign') {
                var swalTitle = "Deselect This Deposit from the Transaction?";
                var swalText = 'Are you sure you want to deselect this deposit from the transaction?';
                var swalType = 'error';
            } else {
                var swalTitle = "Select Deposit (" + positions1 + ") as Transaction Deposit?";
                var swalText = 'Are you sure you want to chose this deposit as transaction deposit? After that step transaction will go on Deposit Detected Step';
                var swalType = 'warning';
            }
            var doAction = function (typeOfAssign) {
                $.ajax({
                    url: "ajP-Binance.php",
                    type: "POST",
                    data: {
                        action: 'assignDepositHistory',
                        depositAmount: $('.depositAmounts:eq(' + pos + ')').text(),
                        additionType: additionType,
                        depositExplTxId: positions2,
                        transactionId: transactionId,
                        typeOfAssign:typeOfAssign
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.response) {
                            if (additionType === 'unAssign') {
                                toastr.warning('Deposit removed from associated transaction', 'Deposit History');
                            } else {
                                toastr.success('Deposit of ' + positions1 + ' associated with transaction id: ' + transactionId, 'Deposit History');
                                window.parent.$(window.parent.document).trigger('refresh_part_transaction_details');
                            }
                            $('#depositHistoryRef').click();
                        } else {
                            if (data.responseMessage === 'Assigned By Another User') {
                                toastr.error('Transaction handle Taken By another User', 'Transaction Handle');
                                $('#depositHistoryRef').click();
                                window.parent.$(window.parent.document).trigger('open_another_handler');
                            } else {
                                toastr.error(data.responseMessage, 'Deposit History');
                                $('#depositHistoryRef').click();
                            }
                        }
                    },
                    complete: function(completeData){
                        setTimeout(function(){
                            writeDownLog('ASSIGN TX. WITH DEPOSIT','<?=$_GET['transactionId']?>',JSON.stringify(completeData.responseJSON));
                        },2000);
                    }
                });
            };

            var doActionUbnormalNoTx = function (typeOfAssign,reason) {
                $.ajax({
                    url: "ajP-Binance.php",
                    type: "POST",
                    data: {
                        action: 'assignDepositHistory',
                        depositAmount: $('.depositAmounts:eq(' + pos + ')').text(),
                        additionType: additionType,
                        depositExplTxId: positions2 ,
                        typeOfAssign:typeOfAssign,
                        reason:reason,
                        confirmations:positions3 ,
                        status:positions4,
                        subAccount:$('#handlerSubAcc').innerText
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.response) {
                            toastr.success('Deposit of ' + positions1 + ' associated with god with reason: ' + reason, 'Deposit History');
                            $('#depositHistoryRef').click();
                        } else {
                            if (data.responseMessage === 'Assigned By Another User') {
                                toastr.error('Deposit Changed By another User', 'Transaction Handle');
                                window.parent.$(window.parent.document).trigger('open_another_handler');
                            } else {
                                toastr.error(data.responseMessage, 'Deposit History');
                                $('#depositHistoryRef').click();
                            }
                        }
                    }
                });
            };

            if (typeOfassign === 'normal') {
                swal({
                    title: swalTitle,
                    text: swalText,
                    type: swalType,
                    showCancelButton: true,
                    confirmButtonColor: "#30966c",
                    confirmButtonText: "Yes, proceed!",
                    customClass: "customSwallClass",
                    closeOnConfirm: true
                }, function () {
                    doAction('normal');
                });
            } else if(typeOfassign === 'AbnormalNoTx'){
                swal({
                    title: "Write down a note for this deposit",
                    type: "input",
                    showCancelButton: true,
                    closeOnConfirm: true,
                    confirmButtonColor: "#4fc900",
                    confirmButtonText: "Assign Deposit on god",
                    inputPlaceholder: "Reason of deposit that is assigned on god",
                    customClass: "customSwallClass"
                }, function (inputValue) {
                    if(inputValue === false || inputValue === '') {
                        toastr.error('You must write a reason in order to add deposit to god');
                        return false;
                    }
                    doActionUbnormalNoTx('AbnormalNoTx',inputValue);
                });
            }else{
                doAction('abnormal');
            }
        }

        if(type === 'withdrawHistory'){

        }
    }


    function load_balances(){
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var extraHtml = '';
        var headExtraHtml = '';
        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkBalances', apiKey:apiKey,apiSecret:apiSecret},
            dataType: "json",
            success: function (data) {
                for (var i = 0; i < data.response.length ; i++) {
                    if(getCoin === data.response[i].coinName){
                        if (data.response[i].coinImage){
                            headExtraHtml += '<div class="flex-center" style="height: 30px;font-size:10px;min-width:85%;width:0px;margin-bottom: 5px;background-color: #1d1d1d;border-radius: 80px;padding-top: 5px; padding-bottom: 5px;" >Withdraw coin balance: '+ data.response[i].available+' '+ getCoin +' <img src="'+ data.response[i].coinImage +'" style="width:20px;"></div>';
                            headExtraHtml += '<div class="flex-center" style="height: 30px;cursor:pointer;text-align:center;font-size:10px;min-width:15%;width:0px;margin-bottom: 5px;background-color: #1d1d1d;border-radius: 80px;padding-top: 5px; padding-bottom: 5px;" onclick="extraBalanceLoad()">Show All</div>';
                        }else{
                            headExtraHtml += '<div class="flex-center" style="height: 30px;font-size:12px;min-width:80%;width:0px;margin-bottom: 5px;background-color: #1d1d1d;border-radius: 80px;padding-top: 5px; padding-bottom: 5px;" >Withdraw coin balance: '+ data.response[i].available+' '+ getCoin +'</div>';
                        }
                    }
                    if (data.response[i].coinImage){
                        extraHtml += '<div style="margin-bottom: 5px;background-color: #455562;padding-left: 15px;border-radius: 80px;padding-top: 5px; padding-bottom: 5px;" class="col-sm-3"><img src="'+ data.response[i].coinImage +'" style="width:25px;">'+ data.response[i].coinName +' : '+ data.response[i].available+'</div>';
                    }else{
                        extraHtml += '<div style="margin-bottom: 5px;background-color: #455562;padding-left: 15px;border-radius: 80px;padding-top: 5px; padding-bottom: 5px;" class="col-sm-3">'+ data.response[i].coinName +' : '+ data.response[i].available+'</div>';
                    }
                }
                if(headExtraHtml === ''){
                    headExtraHtml += '<div class="flex-center" style="height: 30px;font-size:12px;min-width:75%;width:0px;margin-bottom: 5px;background-color: #1d1d1d;border-radius: 80px;padding-top: 5px; padding-bottom: 5px;" >Withdraw coin balance: 0 '+ getCoin +' </div>';
                    headExtraHtml += '<div class="flex-center" style="height: 30px;cursor:pointer;text-align:center;font-size:12px;min-width:25%;width:0px;margin-bottom: 5px;background-color: #1d1d1d;border-radius: 80px;padding-top: 5px; padding-bottom: 5px;" onclick="extraBalanceLoad()">Show All</div>';
                }
                $('#balances').html(extraHtml);
                $('#balanceOnWithdrawCoin').html(headExtraHtml);
            }
        });
    }


    function check_networks(){
        var getCoin = '<?=$_GET['getCoin']?>';
        var giveCoin = '<?=$_GET['giveCoin']?>';
        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkNetworks', giveCoin:giveCoin,getCoin:getCoin},
            dataType: "json",
            success: function (data) {
                var extraHtml = '' +
                    '<div class="col-sm-6" style="padding:0px;">' +
                        '<div class="row" style="padding:0px;margin:0px">'+
                        '<div class="checkBoot col-sm-2 col-xs-2" style="margin: 0px;height: 40px;background-color: #00976d;padding: 10px;border-top-left-radius: 10px;border-bottom-left-radius: 10px;"><img src="'+data.giveCoin.image+'"  style="width:20px;">'+data.giveCoin.shortname+'</div>' +
                        data.depositCurrNetInfos+
                    '</div></div>'+
                    '<div class="checkBoot2 col-sm-6" style="padding:0px;margin:0px;">' +
                        '<div class="row" style="padding:0px;margin:0px">'+
                        '<div class="checkBoot col-sm-2 col-xs-2" style="margin: 0px;height: 40px;background-color: #00976d;padding: 10px;border-top-left-radius: 10px;border-bottom-left-radius: 10px;"><img src="'+data.getCoin.image+'"  style="width:20px;">'+data.getCoin.shortname+'</div>' +
                         data.withdrawCurrNetInfos+
                    '</div></div>';
                $('#checkNetworks').html(extraHtml);
                if(data.depositCurrNetInfosState && data.withdrawCurrNetInfosState){ $('#checkNetworksContainer').css('display','none'); }
                if(screen.width > '700'){
                    $(".checkBoot").map(function(i, el) {
                        $(el).removeClass('col-xs-2');
                    }).get();
                }else{
                    $(".checkBoot").map(function(i, el) {
                        $(el).removeClass('col-sm-2');
                    }).get();
                    $(".checkBoot2").map(function(i, el) {
                        $(el).css('margin-top','10px');
                    }).get();
                }
            }
        });
    }

    function extraBalanceLoad(){
        if(screen.width > '700'){
            $('#loadBalances').click();
            var element = document.getElementById('balancesScroll');
            var elementPosition = element.getBoundingClientRect().top;
            element.scrollIntoView({top: elementPosition, behavior: "smooth"});
        }else{
            $('#depositsHistory').click();
            $('#loadBalances').click();
            setTimeout(function(){
                var element = document.getElementById('balancesScroll');
                var elementPosition = element.getBoundingClientRect().top;
                element.scrollIntoView({top: elementPosition, behavior: "smooth"});
            },800);
        }
    }

    function load_orders_history(){
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var giveCoin = '<?=$_GET['giveCoin']?>';
        var limit = 100;
        var offset = 0;
        var extraHtml = '';
        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkOrdersHistory', giveCoin:giveCoin,getCoin:getCoin,limit:limit,offset:offset,apiKey:apiKey,apiSecret:apiSecret},
            dataType: "json",
            success: function (data) {
                var extraHtml = '' +
                    '<table class="footable footable-custom table table-stripped toggle-arrow-tiny" data-page-size="20" style="font-size:12px;">' +
                    '  <thead>' +
                    '        <tr>' +
                    '        <th>Binance Order Id</th>' +
                    '        <th data-hide="pair">Pair set</th>' +
                    '        <th data-hide="type" >Type</th>' +
                    '        <th data-hide="depositAmount" >Amount Deposited</th>' +
                    '        <th data-hide="executedAmount">Amount Executed</th>' +
                    '        <th data-hide="price">Price</th>' +
                    '        <th data-hide="status">Status</th>' +
                    '    </tr>' +
                    '    </thead><tbody>';
                for (var i = 0; i < data.response.length-1 ; i++) {

                    if(data.response[i].side === 'BUY'){
                        var way = ' <- ';
                        var color = "color: rgb(2, 192, 118);";
                    }else if (data.response[i].side === 'SELL'){
                        var way = ' -> ';
                        var color = "color: rgb(248, 73, 96);";
                    }else{
                        var way = ' - ';
                        var color = "";
                    }

                    if(data.response[i].status === 'FILLED'){
                        var status = '<span style="background-color:lightgreen;color:green;padding:5px;">Filed</span>';
                    }else if (data.response[i].side === 'PARTIAL'){
                        var status = '<span style="background-color:orange;color:darkorange;padding:5px;">Partial Fill</span>';
                    }

                    extraHtml += '<tr style="'+color+'">' +
                        '<td>' +
                        '#'+ data.response[i].orderId +
                        '</td>' +
                        '<td style="width:160px;">' +
                        '<img src="'+ data.response[i].giveCoinImage +'" style="width:20px;">'+ data.response[i].giveCoin + way +
                        '<img src="'+ data.response[i].getCoinImage +'" style="width:20px;">'+ data.response[i].getCoin +
                        '</td>' +
                        '<td>' + data.response[i].type +'</td>' +
                        '<td>'+ data.response[i].origQty +'</td>' +
                        '<td>'+ data.response[i].executedQty +'</td>' +
                        '<td>'+data.response[i].price+'</td>' +
                        '<td>'+status+'</td>' +
                        '</tr>';
                }
                extraHtml += '</tbody>' +
                    '    <tfoot>' +
                    '    <tr>' +
                    '    <td colspan="7">' +
                    '   <div> <ul class="pagination float-right"></ul></div>' +
                    '    </td>' +
                    '    </tr>' +
                    '    </tfoot>' +
                    '   </table>';
                $('#ordersHistory').html(extraHtml).css('overflow-y','scroll').css('height','400px');
                $('.footable-custom').footable();

            }
        });
    }

    function load_withdraw_history(){
        var apiKey = '<?=$_GET['apiKey']?>';
        var apiSecret = '<?=$_GET['apiSecret']?>';
        var getCoin = '<?=$_GET['getCoin']?>';
        var extraHtml = '';
        $.ajax({
            url: "ajP-Binance.php",
            type: "POST",
            data: {action: 'checkWithdrawHistory', getCoin:getCoin,apiKey:apiKey,apiSecret:apiSecret},
            dataType: "json",
            success: function (data) {
                var extraHtml = '' +
                    '<table class="footable footable-custom3 table table-stripped toggle-arrow-tiny" data-page-size="20" style="font-size:12px;">' +
                    '  <thead>' +
                    '        <tr>' +
                    '        <th data-hide="pair">Deposit Amount</th>' +
                    '        <th data-hide="executedAmount">Status</th>' +


                    '        <th data-hide="depositAddress">Deposit Address</th>' +
                    '        <th data-hide="depositAmount" >txId</th>' +
                    '    </tr>' +
                    '    </thead><tbody>';
                for (var i = 0; i < data.response.length-1 ; i++) {

                    extraHtml += '<tr>' +
                        '<td><img src="'+ data.response[i].coinImage +'" style="width:20px;">' +  data.response[i].amount +' '+ data.response[i].asset + '</td>' +
                        '<td>'+ data.response[i].statusResponse +'</td>' +
                        '<td>' +
                        data.response[i].address +
                        '</td>' +
                        '<td>'+ data.response[i].txId +'</td>' +
                        '</tr>';
                }
                extraHtml += '</tbody>' +
                    '    <tfoot>' +
                    '    <tr>' +
                    '    <td colspan="4">' +
                    '   <div> <ul class="pagination float-left"></ul></div>' +
                    '    </td>' +
                    '    </tr>' +
                    '    </tfoot>' +
                    '   </table>';
                $('#withdrawHistory').html(extraHtml).css('overflow-y','scroll').css('height','400px');
                $('.footable-custom3').footable();

            }
        });
    }

    function load_exchanger_buttons(){
        $('.buttonsPc').append('<div>test</div>');


    }

    function writeDownLog(title,txId,dato){
        $.ajax({
            url: "../AJAXPOSTS",
            type: "POST",
            data: {action: 'writeDownTransactionLog', dato: dato,txId:txId,title:title},
            dataType: "json",
            success: function (data) {
                console.log(data);
            }
        });
    }
</script>