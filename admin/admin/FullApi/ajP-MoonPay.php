<?php
use Stocks\ApiVersion\S2s;
require_once('../include/admin-load.php');
/**
 Functions Required START
 **/
date_default_timezone_set('Europe/Athens');


function fix_keys($array) {
    foreach ($array as $k => $val) {
        if (is_array($val))
            $array[$k] = fix_keys($val); //recurse
    }
    return array_values($array);
}

function array_combine_($keys, $values)
{
    $result = array();
    foreach ($keys as $i => $k) {
        $result[$k][] = $values[$i];
    }
    array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return    $result;
}


/**
 Functions Required END
 **/



if(isset($_REQUEST['action'])) {
    $swapReturn = array();
    include '../STEX/StocksExchange.php';
    //include '../STEX/vendor/autoload.php'; // this comented because autoload is performed on sumsubs class file.
    include '../STEX/Services/Service.php';
    include '../STEX/ApiVersion/Three.php';
    include '../STEX/ApiVersion/S2s.php';
    $se = new S2s(
        [
            'tokenObject' => [
                'access_token' => $config['apiTokens']['stex']['accessToken'],
            ],
            'accessTokenUrl' => 'https://api3.stex.com/oauth/token',
            'scope' => 'profile trade withdrawal reports push settings'
        ]
    );

    if($_REQUEST['action'] == 'checkDepositHistory') {
        /** Ta comments edw einai gia na perneis ta response apo ta currencies tou stex
         twra ta kanei track apo to db mas pou ta evala egw an alaksoun ta id apo to stex den tha doulevei
         swsta to deposit history kai den tha mporei na ginei assign
         */
       // $decodedResponse = $se->publicCurrencies();
        $decodedResponse = $cryptoCoins->matchOurCoinWithStexCoinId($_REQUEST['giveCoin']);
       // $founded = array_search($_REQUEST['giveCoin'],array_column($decodedResponse->data,'code'));
       // $founded2 = array_search($_REQUEST['getCoin'],array_column($decodedResponse->data,'code'));
       // $foundedGiveCoinInfosForCoinShortName = $decodedResponse->data[$founded];
        $balances = $se->deposits(array('currencyId'=>$decodedResponse['stexCurrencyId'],'limit'=>$_REQUEST['pageResultsCount'],'offset'=>$_REQUEST['offset']));
        $swapReturn['response'] = $balances;
        if($balances->success == false){
            $swapReturn['responseState'] = 'ERROR';
            $swapReturn['response'] = $balances;
        }else{
            $balances = $balances->data;
            $transactionInfos = $order->swap_by_id($_REQUEST['transactionId']);
            $useaLL = false;
            foreach ($balances as $balance => $value) {
                $balances[$balance]->test1 = $transactionInfos['depositExplTxId'];
                $balances[$balance]->test2 = $balances[$balance]->txid;
                if ($transactionInfos['depositExplTxId'] == $balances[$balance]->txid && $transactionInfos['btcGetVal'] == $balances[$balance]->amount) {
                    $balances[$balance]->isSelectedForThisTransaction = true;
                    $useaLL = true;
                } else {
                    $balances[$balance]->isSelectedForThisTransaction = false;
                }
                $coinInfos = $cryptoCoins->show_coins_by_name($balances[$balance]->currency_code);
                $balances[$balance]->coinImage = $setting['website_url'] . $setting['images'] . 'small-' . $coinInfos['image'];
                $balances[$balance]->statusResponse = '<span style="color:'. $balances[$balance]->status_color .'">'. $balances[$balance]->status .'</span>';
                $balances[$balance]->noAnyColor = false;
                $checkOrd = $order->check_if_transaction_has_already_assigned_to_exchanger_transaction('deposit', $balances[$balance]->txid,$balances[$balance]->amount);
                if ($checkOrd) {
                    $adminDetails = $user->is_admin($checkOrd['signedBy']);
                    if($adminDetails){
                        $balances[$balance]->extraInfo = '<div class="flex-center"><img src="https://gintonic.instaswap.io/images/admph/small-' . $adminDetails['image'] . '" style="width:20px;border-radius:100%;"><a target="_blank" href="https://gintonic.instaswap.io/proOrder?id=' . $checkOrd['id'] . '">#' . $checkOrd['id'] . '</a></div>';
                    }else{
                        $balances[$balance]->extraInfo = '<div class="flex-center"><img src="/images/darthVader.png" style="width:25px;height:25px;"><a target="_blank" href="https://gintonic.instaswap.io/proOrder?id=' . $checkOrd['id'] . '">#' . $checkOrd['id'] . '</a></div>';
                    }
                    $balances[$balance]->instaTxId = $checkOrd['id'];
                } else {
                    $balances[$balance]->instaTxId = false;
                    $balances[$balance]->extraInfo = 'Unused';
                }
                $balances[$balance]->explorerFullUrl = $balances[$balance]->block_explorer_url.$balances[$balance]->txid;

            }
            if ($useaLL) {
                foreach ($balances as $balance => $value) {
                    $balances[$balance]->noAnyColor = true;
                }
            }
            $swapReturn['response'] = $balances;
        }
    }

    if($_REQUEST['action'] == 'checkBalances') {
        $availableCoins = $cryptoCoins->show_coins();
        $profileInfos = $se->profileInfo(array('show_balances'=>1));
        $balances = (array)$profileInfos->data->approx_balance;
        $meter = 0;
        $array_keys = array_keys($balances);
        foreach ($balances as $balance){
            $balance->coinName = $array_keys[$meter];
            $balance->available = $balance->balance;
            $coinInfos = $cryptoCoins->show_coins_by_name($array_keys[$meter]);
            if($coinInfos){
                $balance->coinImage = $setting['website_url'].$setting['images'].'small-'.$coinInfos['image'];
            }else{
                $balance->coinImage = false;
            }
            $meter++;
        }
        $swapReturn['response'] = array_values($balances);
    }

    if($_REQUEST['action'] == 'checkWithdrawHistory') {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api3.stex.com/public/currencies");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $decodedResponse = json_decode($server_output);
        curl_close($ch);
        $founded = array_search($_REQUEST['giveCoin'],array_column($decodedResponse->data,'code'));
        $founded2 = array_search($_REQUEST['getCoin'],array_column($decodedResponse->data,'code'));
        $foundedGiveCoinInfosForCoinShortName = $decodedResponse->data[$founded];
        $foundedGetCoinInfosForCoinShortName = $decodedResponse->data[$founded2];
        $balances = $se->withdrawals(array('id'=>$foundedGiveCoinInfosForCoinShortName->id));
        $swapReturn['response'] = $balances;
        $balances = $balances->data;
        foreach ($balances as $balance =>$value){
            $coinInfos = $cryptoCoins->show_coins_by_name($balances[$balance]->currency_code);
            $balances[$balance]->coinImage = $setting['website_url'].$setting['images'].'small-'.$coinInfos['image'];
            $balances[$balance]->statusResponse = '<span style="color:'. $balances[$balance]->status_color .'">'. $balances[$balance]->status .'</span>';
            $balances[$balance]->amount = number_format($balances[$balance]->amount,$coinInfos['allowedDecimals'],'.','');
            $balances[$balance]->explorerFullUrl = $balances[$balance]->block_explorer_url.$balances[$balance]->txid;
        }
        $swapReturn['response'] =  $balances;
    }

    if($_REQUEST['action'] == 'checkOrderInfo'){
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        if($orderInfos){
            if($orderInfos['depositExplTxId'] == null){
                $swapReturn['responseState'] = 'ERROR2';
                $swapReturn['response'] = 'You must detect deposit first in order to create order';
            }else{
                if($orderInfos['getCoinInfos']['destinationTagPhrase'] == 'none'){
                    $orderInfos['destinationTagPhrase'] = 'is not required';
                }
                $swapReturn['responseState'] = 'OK';
                $swapReturn['response'] = $orderInfos;
            }
        }else{
            $swapReturn['responseState'] = 'ERROR';
            $swapReturn['response'] = 'Something went wrong please try again!';
        }
    }

    if($_REQUEST['action'] == 'assignDepositHistory'){
        if($_REQUEST['additionType'] == 'assign'){
            $response = $order->update_ztd($_REQUEST['transactionId'],'depositExplTxId',$_REQUEST['depositExplTxId'],$_REQUEST['depositAmount'],$_REQUEST['typeOfAssign']);
        }else{
            $response = $order->update_ztd($_REQUEST['transactionId'],'depositExplTxId',null);
        }
        if($order->error){
            $swapReturn['responseMessage'] = $order->error;
            $swapReturn['response'] = $response;
        }else{
            $swapReturn['responseMessage'] = $order->msg;
            $swapReturn['response'] = $response;
        }
    }

    if($_REQUEST['action'] == 'checkOrdersHistory') {
        $stex_coin_pairs = $se->publicCurrencyPairsList('ALL');
        $mixedSymbolDESC = $_REQUEST['giveCoin'].'_'.$_REQUEST['getCoin'];
        $mixedSymbolASC = $_REQUEST['getCoin'].'_'.$_REQUEST['giveCoin'];
        $getCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['getCoin']);
        $giveCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['giveCoin']);
        $stexPairId = $stex_coin_pairs->data[$founded]->currency_id;
        $balances = $se->reportsOrders($stexPairId);
            $export = array();
        foreach ($balances->data as $balance =>$value){
            $balances->data[$balance]->initial_amount = number_format($balances->data[$balance]->initial_amount,5,'.','');
            $balances->data[$balance]->processed_amount = number_format($balances->data[$balance]->processed_amount,5,'.','');
            if($balances->data[$balance]->currency_pair_name == $mixedSymbolDESC){
                $balances->data[$balance]->giveCoinImage = $setting['website_url'].$setting['images'].'small-'.$giveCoinInfos['image'];
                $balances->data[$balance]->giveCoin = $giveCoinInfos['shortname'];
                $balances->data[$balance]->getCoinImage = $setting['website_url'].$setting['images'].'small-'.$getCoinInfos['image'];
                $balances->data[$balance]->getCoin = $getCoinInfos['shortname'];
            }else if($balances->data[$balance]->currency_pair_name == $mixedSymbolASC){

                $balances->data[$balance]->giveCoinImage = $setting['website_url'].$setting['images'].'small-'.$getCoinInfos['image'];
                $balances->data[$balance]->giveCoin = $getCoinInfos['shortname'];
                $balances->data[$balance]->getCoinImage = $setting['website_url'].$setting['images'].'small-'.$giveCoinInfos['image'];
                $balances->data[$balance]->getCoin = $giveCoinInfos['shortname'];
            }else{
                unset($balances->data[$balance]);
            }
        }
        $swapReturn['response'] =  array_values($balances->data);
    }

    if($_REQUEST['action'] == 'checkCoinBalance') {
        $availableCoins = $cryptoCoins->show_coins();
        $profileInfos = $se->profileInfo(array('show_balances'=>1));
        $balances = (array)$profileInfos->data->approx_balance;
        $meter = 0;
        $array_keys = array_keys($balances);
        foreach ($balances as $balance){
            if($balances[$balance]['coinName'] == $_REQUEST['giveCoin'] || $balances[$balance]['coinName'] == $_REQUEST['getCoin']) {
                $balance->coinName = $array_keys[$meter];
                $balance->available = $balance->balance;
                $coinInfos = $cryptoCoins->show_coins_by_name($array_keys[$meter]);
                if ($coinInfos) {
                    $balance->coinImage = $setting['website_url'] . $setting['images'] . 'small-' . $coinInfos['image'];
                } else {
                    $balance->coinImage = false;
                }
                $meter++;
            }else{
                unset($balances[$balance]);
            }
        }
        $swapReturn['response'] = array_values($balances);
    }

    if($_REQUEST['action'] == 'placeOrder') {
        $exportedOrders = array();
        $notCompletedOrders = array();
        $exportedOrdersCompleted = array();
        $binancePrices = $se->publicTicker();
        $binancePrices = $binancePrices->data;
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        $mixedSymbolDESC = $_REQUEST['giveCoin'] .'_'. $_REQUEST['getCoin'];
        $mixedSymbolASC = $_REQUEST['getCoin'] .'_'. $_REQUEST['giveCoin'];
        if($orderInfos['depositExplTxId']!= null) {
            $serchedASC = array_search($mixedSymbolASC,array_column($binancePrices,'symbol'));
            $serchedDESC = array_search($mixedSymbolDESC,array_column($binancePrices,'symbol'));
            $stexFees = $se->gather_fees($binancePrices[$serchedASC]->id);

            if(!is_bool($serchedASC) && $serchedASC != false){
                if($stexFees->success){
                    $stexFeesCounted = $stexFees->data->buy_fee;
                }else{
                    $stexFeesCounted = 0;
                }
                $chargedTradingFees = 0;
                $decodedResponse3 = $se->publicOrderBook($binancePrices[$serchedASC]->id,array('limit_bids'=>0,'limit_asks'=>1000));
                $countedResponse = count($decodedResponse3->data->ask);
                if($countedResponse != 0) {
                    if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'secondCross') {
                        $decodedResponse = $cryptoCoins->matchOurCoinWithStexCoinId($_REQUEST['getCoin']);
                        $withdrawCurrStexInfos = $se->publicCurrenciesById($decodedResponse['stexCurrencyId']);
                        if($withdrawCurrStexInfos->success){
                            $withdrawalFee = floatval($withdrawCurrStexInfos->data->withdrawal_fee_const);
                        }else{
                            $swapReturn['errorMsg'] = 'Cant declare withdraw fee';
                            echo json_encode($swapReturn);
                            exit();
                        }
                    }else{
                        $withdrawalFee = 0;
                    }
                    $remainingAmount = floatval($_REQUEST['withdrawAmount']) + floatval($withdrawalFee);
                    $sumBuyPrice = floatval($_REQUEST['depositAmount']);
                    $meterinio = 0;
                    $hasError = false;
                    $summPrice = 0;
                    $boughtAmount = 0;
                   /** na ftiaksw mia function  pou na ksana ypologizei ta fees gia partner kai ta dika mas
                    *
                    */
                    foreach ($decodedResponse3->data->ask as $orderinio2 => $value2) {
                        $price = $decodedResponse3->data->ask[$orderinio2]->price;
                        $amount = $decodedResponse3->data->ask[$orderinio2]->amount;
                        if ($remainingAmount != 0) {
                            if($amount >= ($remainingAmount + ($remainingAmount * $stexFeesCounted)) && $meterinio === 0){
                                $remainingAmount = $remainingAmount + ($remainingAmount * $stexFeesCounted);
                                if(($price * $remainingAmount) <= $_REQUEST['depositAmount']){
                                    $move = $se->addTradingOrdersByPair($binancePrices[$serchedASC]->id, 'BUY', $remainingAmount, $price);
                                    if ($move->success == false) {
                                        $hasError = true;
                                        array_push($notCompletedOrders, $move);
                                        array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol));
                                    }else{
                                        array_push($exportedOrdersCompleted, $move->data);
                                        array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol));
                                        $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
                                        $boughtAmount = $boughtAmount + $remainingAmount;
                                        $remainingAmount = 0;
                                    }
                                    /** afta einai gia debug
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol,'way'=>'SELL'));
                                    $remainingAmount = 0;
                                    /** afta einai gia debug*/
                                }else{
                                    $hasError = true;
                                    $errorMsg = 'Not Enough '.$_REQUEST['giveCoin'].' You want to buy '.floatval($_REQUEST['withdrawAmount']).' now price for that is '.$price*$remainingAmount;
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol));
                                }
                            }else if ($amount < $remainingAmount && $meterinio > 0 && $summPrice < $sumBuyPrice) {
                                $move = $se->addTradingOrdersByPair($binancePrices[$serchedASC]->id,'BUY',$amount,$price);
                                if ($move->success == false) {
                                    $hasError = true;
                                    array_push($notCompletedOrders, $move);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol));
                                }else{
                                    array_push($exportedOrdersCompleted, $move->data);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol));
                                    $summPrice = floatval($summPrice) + floatval($price * $amount);
                                    $boughtAmount = $boughtAmount + $amount;
                                    $remainingAmount = (floatval($remainingAmount) - floatval($amount)) + ($move->data->initial_amount * $stexFeesCounted);
                                }

                                /** afta einai gia debug
                                array_push($exportedOrders,array('sumPrice' =>$summPrice,'Fee on this trate'=>$stexFeesCounted,'remainingAmount' =>$remainingAmount,'Amount'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol,'way'=>'Buy'));
                                $remainingAmount = floatval($remainingAmount) - floatval($amount);
                                $summPrice = floatval($summPrice) + floatval($price * $amount);
                                /** afta einai gia debug*/
                            } else if($amount >= $remainingAmount && $meterinio > 0 && $summPrice < $sumBuyPrice) {
                               $move = $se->addTradingOrdersByPair($binancePrices[$serchedASC]->id,'BUY',$remainingAmount,$price);
                                if ($move->success == false) {
                                    $hasError = true;
                                    array_push($notCompletedOrders, $move);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol));
                                }else{
                                    array_push($exportedOrdersCompleted, $move->data);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol));
                                }
                                $boughtAmount = $boughtAmount + $remainingAmount;
                                $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
                                $remainingAmount = 0;
                              /** afta einai gia debug
                                $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
                                array_push($exportedOrders,array('sumPrice' =>$summPrice,'Fee on this trate'=>$stexFeesCounted,'remainingAmount' =>$remainingAmount,'Amount'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol,'way'=>'Buy'));
                                 $remainingAmount = 0;
                                /** afta einai gia debug*/
                            }else{
                                if($boughtAmount < $_REQUEST['withdrawAmount']) {
                                    $hasError = true;
                                    $errorMsg = 'Transaction is not completed \n You Bought : ' . $boughtAmount . ' ' . $_REQUEST['getCoin'] . ' \n Remaining to complete transaction: ' . $remainingAmount . ' ' . $_REQUEST['getCoin'];
                                    array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $binancePrices[$serchedASC]->symbol));
                                }else{
                                    $hasError = false;
                                    $errorMsg = 'Transaction is completed \n You Bought : ' . $boughtAmount . ' ' . $_REQUEST['getCoin'];
                                    array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $binancePrices[$serchedASC]->symbol));
                                }
                            }
                        }
                        $meterinio++;
                    }
                }

                if (count($exportedOrdersCompleted) > 0) {
                    $calculateReceivedAmountFromSell = 0;
                    foreach ($exportedOrdersCompleted as $ord =>$value){
                        $sumRecieve = $exportedOrdersCompleted[$ord]->initial_amount * $exportedOrdersCompleted[$ord]->price;
                        $calculateReceivedAmountFromSell = $calculateReceivedAmountFromSell + $sumRecieve;
                        $chargedTradingFees = $chargedTradingFees + ($exportedOrdersCompleted[$ord]->initial_amount * $stexFeesCounted);
                    }

                    /** edw mporei na mpei ena koumpi pou na to energopoiei h na to apenergopoiei
                     gia ta cross pair profit maximzie apla prepei na koitas ti pernei kai ti dinei*/
                    if($_REQUEST['orderType'] == 'firstcross'){
                        $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'btcGetVal',$calculateReceivedAmountFromSell);
                    }
                   /* if($_REQUEST['orderType'] == 'firstNormal'){
                        $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'resultedReceivingAmount',($calculateReceivedAmountFromSell -  floatval($withdrawalFee)));
                    }*/
                    /** --- */
                    $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 2);
                    $swapReturn['response'] = 'Order is full';
                    $swapReturn['generalResponse'] = $exportedOrders;
                    $swapReturn['stateResponse'] = $changeOrderState;
                    $swapReturn['notCompletedOrders'] = $notCompletedOrders;
                    $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
                    $swapReturn['Bids'] = $decodedResponse3->data->bid;
                    $swapReturn['Asks'] = $decodedResponse3->data->ask;
                    $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
                    $swapReturn['tradingFeeCharged'] = $chargedTradingFees;
                    $swapReturn['orderType'] = $_REQUEST['orderType'];
                    $swapReturn['errorMsg'] = $errorMsg;
                    $swapReturn['boughtAmount'] = $boughtAmount;
                    $swapReturn['boughtAmountMsg'] = 'Bought Amount: '.$boughtAmount.' '.$_REQUEST['getCoin'];
                    $swapReturn['receivedAmount'] = 'You Sold: '.$calculateReceivedAmountFromSell.' '.$_REQUEST['giveCoin'];
                    $swapReturn['receivedAmountMsg'] = $calculateReceivedAmountFromSell;
                }else{
                    $swapReturn['generalResponse'] = $exportedOrders;
                    $swapReturn['response'] = 'Order is not full filled';
                    $swapReturn['stateResponse'] = $notCompletedOrders;
                    $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
                    $swapReturn['Bids'] = $decodedResponse3->data->bid;
                    $swapReturn['Asks'] = $decodedResponse3->data->ask;
                    $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
                    $swapReturn['orderType'] = $_REQUEST['orderType'];
                    $swapReturn['errorMsg'] = $errorMsg;
                    $swapReturn['boughtAmount'] = $boughtAmount;
                    $swapReturn['boughtAmountMsg'] = 'Bought Amount: '.$boughtAmount.' '.$_REQUEST['getCoin'];
                }
            }

            if(!is_bool($serchedDESC) && $serchedDESC != false){
                if($stexFees->success){
                    $stexFeesCounted = $stexFees->data->sell_fee;
                }else{
                    $stexFeesCounted = 0;
                }
                $chargedTradingFees = 0;
                $decodedResponse3 = $se->publicOrderBook($binancePrices[$serchedDESC]->id,array('limit_bids'=>1000,'limit_asks'=>0));
                $climateCurrency = 0;
                $countedResponse = count($decodedResponse3->data->bid);
                if($countedResponse != 0) {
                    $hasError = false;
                    $sumBuyPrice = floatval($_REQUEST['withdrawAmount']);
                    $remainingAmount = floatval($_REQUEST['depositAmount']);
                    $meterinio = 0;
                    $boughtAmount = 0;
                    $summPrice = 0;
                    foreach ($decodedResponse3->data->bid as $orderinio => $value2) {
                        $price = $decodedResponse3->data->bid[$orderinio]->price;
                        $amount = $decodedResponse3->data->bid[$orderinio]->amount;
                        if ($remainingAmount != 0) {
                            if($amount >= ($remainingAmount - ($remainingAmount * $stexFeesCounted)) && $meterinio === 0){
                                $remainingAmount = $remainingAmount - ($remainingAmount * $stexFeesCounted);
                                if(($price * $remainingAmount) <= $_REQUEST['depositAmount']){
                                    $move = $se->addTradingOrdersByPair($binancePrices[$serchedDESC]->id, 'SELL', $remainingAmount, $price);
                                    if ($move->success == false) {
                                        $hasError = true;
                                        array_push($notCompletedOrders, $move);
                                        array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol));
                                    }else{
                                        array_push($exportedOrdersCompleted, $move->data);
                                        array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol));
                                        $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
                                        $boughtAmount = $boughtAmount + $remainingAmount;
                                        $remainingAmount = 0;
                                    }
                                    /** afta einai gia debug
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol,'way'=>'SELL'));
                                    $remainingAmount = 0;
                                    /** afta einai gia debug*/
                                }else{
                                    $hasError = true;
                                    $errorMsg = 'Not Enough '.$_REQUEST['giveCoin'].' You want to buy '.floatval($_REQUEST['withdrawAmount']).' now price for that is '.$price*$remainingAmount;
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol));
                                }
                            }else if ($amount < $remainingAmount && $summPrice < $sumBuyPrice) {
                                $move = $se->addTradingOrdersByPair($binancePrices[$serchedDESC]->id, 'SELL', $amount, $price);
                                if ($move->success == false) {
                                    $hasError = true;
                                    array_push($notCompletedOrders, $move);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol));

                                }else{
                                    array_push($exportedOrdersCompleted, $move->data);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol));
                                    $summPrice = floatval($summPrice) + floatval($price * $amount);
                                    $boughtAmount = $boughtAmount + $amount;
                                    $remainingAmount = (floatval($remainingAmount) - floatval($amount)) - ($move->data->initial_amount * $stexFeesCounted);
                                }
                                /** afta einai gia debug
                                array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol,'way'=>'SELL'));
                                $remainingAmount = floatval($remainingAmount) - floatval($amount);
                                /** afta einai gia debug*/
                            } else if($amount >= $remainingAmount && $summPrice < $sumBuyPrice) {
                                $move = $se->addTradingOrdersByPair($binancePrices[$serchedDESC]->id, 'SELL', $remainingAmount, $price);
                                if ($move->success == false) {
                                    $hasError = true;
                                    array_push($notCompletedOrders, $move);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol));
                                }else{
                                    array_push($exportedOrdersCompleted, $move->data);
                                    array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol));
                                }
                                $boughtAmount = $boughtAmount + $remainingAmount;
                                $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
                                $remainingAmount = 0;
                                /** afta einai gia debug
                                array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$binancePrices[$serchedDESC]->symbol,'way'=>'SELL'));
                                $remainingAmount = 0;
                                /** afta einai gia debug*/
                            }else{
                                if($boughtAmount < $_REQUEST['depositAmount']) {
                                    $hasError = true;
                                    $errorMsg = 'Transaction is not completed. You Sell : '.$boughtAmount.' '.$_REQUEST['giveCoin'].'. Remaining to sell in order to complete transaction: '.$remainingAmount.' '.$_REQUEST['giveCoin'].'------DEBUG-----: '.$amount.' - '.$remainingAmount.' - '.$summPrice.' - '.$sumBuyPrice;
                                    array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $binancePrices[$serchedASC]->symbol));
                                }else{
                                    $hasError = false;
                                    $errorMsg = 'Transaction is completed \n You Sell : ' . $boughtAmount . ' ' . $_REQUEST['giveCoin'];
                                    array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $binancePrices[$serchedASC]->symbol));
                                }
                            }
                        }
                        $meterinio++;
                    }
                }
                if (count($exportedOrdersCompleted) > 0) {
                    $calculateReceivedAmountFromSell = 0;
                    foreach ($exportedOrdersCompleted as $ord =>$value){
                        $sumRecieve = $exportedOrdersCompleted[$ord]->initial_amount * $exportedOrdersCompleted[$ord]->price;
                        $chargedTradingFees = $chargedTradingFees + ($exportedOrdersCompleted[$ord]->initial_amount * $stexFeesCounted);
                        $calculateReceivedAmountFromSell = $calculateReceivedAmountFromSell + $sumRecieve;
                    }

                    /** edw mporei na mpei ena koumpi pou na to energopoiei h na to apenergopoiei
                    gia ta cross pair profit maximzie apla prepei na koitas ti pernei kai ti dinei*/
                    if($_REQUEST['orderType'] == 'firstcross'){
                        $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'btcGetVal',$calculateReceivedAmountFromSell);
                    }
                   /* if($_REQUEST['orderType'] == 'firstNormal'){
                        $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'resultedReceivingAmount',($calculateReceivedAmountFromSell -  floatval($withdrawalFee)));
                    }*/
                    /** --- */
                    $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 2);
                    $swapReturn['response'] = 'Order is full';
                    $swapReturn['generalResponse'] = $exportedOrders;
                    $swapReturn['stateResponse'] = $changeOrderState;
                    $swapReturn['notCompletedOrders'] = $notCompletedOrders;
                    $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
                    $swapReturn['Bids'] = $decodedResponse3->data->bid;
                    $swapReturn['Asks'] = $decodedResponse3->data->ask;
                    $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
                    $swapReturn['tradingFeeCharged'] = $chargedTradingFees;
                    $swapReturn['orderType'] = $_REQUEST['orderType'];
                    $swapReturn['errorMsg'] = $errorMsg;
                    $swapReturn['boughtAmount'] = $boughtAmount;
                    $swapReturn['boughtAmountMsg'] = 'Sold Amount: '.$boughtAmount.' '.$_REQUEST['giveCoin'];
                    $swapReturn['receivedAmount'] = 'You Bought: '.$calculateReceivedAmountFromSell.' '.$_REQUEST['getCoin'];
                    $swapReturn['receivedAmountMsg'] = $calculateReceivedAmountFromSell;

                }else{
                    $swapReturn['generalResponse'] = $exportedOrders;
                    $swapReturn['response'] = 'Order is not full filled';
                    $swapReturn['stateResponse'] = $notCompletedOrders;
                    $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
                    $swapReturn['Bids'] = $decodedResponse3->data->bid;
                    $swapReturn['Asks'] = $decodedResponse3->data->ask;
                    $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
                    $swapReturn['orderType'] = $_REQUEST['orderType'];
                    $swapReturn['errorMsg'] = $errorMsg;
                    $swapReturn['boughtAmount'] = $boughtAmount;
                    $swapReturn['boughtAmountMsg'] = 'Sell Amount: '.$boughtAmount.' '.$_REQUEST['giveCoin'];
                    $swapReturn['receivedAmount'] = 'You Cant Buy';
                }
            }
        }else{
            $swapReturn['response'] = 'ERROR';
            $swapReturn['stateResponse'] = 'Order is not full filled';
        }
    }

    if($_REQUEST['action'] == 'checkBalanceOfCoin'){
        $decodedResponse  = $se->wallets('');
        $searched = array_search($_REQUEST['getCoin'],array_column($decodedResponse->data,'currency_code'));
        $searched2 = array_search($_REQUEST['giveCoin'],array_column($decodedResponse->data,'currency_code'));
        $swapReturn['response']['giveCoin'] = $decodedResponse->data[$searched2]->balance;
        $swapReturn['response']['getCoin'] = $decodedResponse->data[$searched]->balance;
    }

    if($_REQUEST['action'] == 'withdrawAction'){
        $transactionInfos = $order->swap_by_id(intval($_REQUEST['txId']));
        if(!is_null($transactionInfos['withdrawExplTxId'])){
            $swapReturn['responseState'] = 'ERROR4';
            $swapReturn['response'] = 'Withdraw already executed';
            echo json_encode($swapReturn);
            exit();
        }

        if($transactionInfos['signedBy'] != $_SESSION['curr_user']){
            $swapReturn['responseState'] = 'ERROR4';
            $swapReturn['response'] = 'Withdraw must be executed by '.$transactionInfos['signedBy'];
            echo json_encode($swapReturn);
            exit();
        }
        $decodedResponse = $cryptoCoins->matchOurCoinWithStexCoinId($_REQUEST['withdrawCoin']);
        $withdrawCurrStexInfos = $se->publicCurrenciesById($decodedResponse['stexCurrencyId']);
        if($withdrawCurrStexInfos->success){
            $withdrawalFee = floatval($withdrawCurrStexInfos->data->withdrawal_fee_const);
        }else{
            $swapReturn['responseState'] = 'ERROR';
            $swapReturn['response'] = 'Cant declare withdraw fee';
            echo json_encode($swapReturn);
            exit();
        }
        $withdrawAmount = floatval($_REQUEST['amount']) + floatval($withdrawalFee);
        if($_REQUEST['destinationTagPhrase'] == 'is not required'){
            if(strtolower($_REQUEST['withdrawCoin']) == 'usdt'){
                $response = $se->addWithdrawal($decodedResponse['stexCurrencyId'], $withdrawAmount, $_REQUEST['wallet'],'',5);
            }else{
                $response = $se->addWithdrawal($decodedResponse['stexCurrencyId'], $withdrawAmount, $_REQUEST['wallet']);
            }
            $swapReturn['$testWithdrawCoin'] = strtolower($_REQUEST['withdrawCoin']);
            $swapReturn['responseState'] = 'OK';
            $swapReturn['response'] = $response;
            if($response->success){
                if($response->data->status == 'Withdrawal Error'){
                    $swapReturn['responseState'] = 'ERROR';
                    $swapReturn['response'] = $response->data->status;
                }else{
                    $swapReturn['responseState'] = 'OK';
                    $swapReturn['response'] = $response->data;
                    $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 3);
                    $updateOrderWithdrawAmount = $order->update_decrypted(intval($_REQUEST['txId']),'resultedReceivingAmount',$_REQUEST['amount']);
                    $assignWithdraw = $order->update_decrypted(intval($_REQUEST['txId']),'withdrawExplTxId',$response->data->id);
                }
            }else{
                $swapReturn['responseState'] = 'ERROR';
                $swapReturn['response'] = $response;
            }
        }else{
            $response = $se->addWithdrawal($decodedResponse['stexCurrencyId'], $withdrawAmount, $_REQUEST['wallet'], $_REQUEST['destinationTagPhrase']);
            if($response->success){
                if($response->data->status == 'Withdrawal Error'){
                    $swapReturn['responseState'] = 'ERROR';
                    $swapReturn['response'] = $response->data->status;
                }else{
                    $swapReturn['responseState'] = 'OK';
                    $swapReturn['response'] = $response->data;
                    $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 3);
                    $updateOrderWithdrawAmount = $order->update_decrypted(intval($_REQUEST['txId']),'resultedReceivingAmount',$_REQUEST['amount']);
                    $assignWithdraw = $order->update_decrypted(intval($_REQUEST['txId']),'withdrawExplTxId',$response->data->id);
                }
            }else{
                $swapReturn['responseState'] = 'ERROR';
                $swapReturn['response'] = $response;
            }
        }
    }

    if($_REQUEST['action'] == 'checkPairExistance'){
        $response = $exchanger->find_single_pair_on_exchanger_existance($_REQUEST['giveCoin'],$_REQUEST['getCoin']);
        $swapReturn['response'] = $response;
    }

    echo json_encode($swapReturn);
}

?>
