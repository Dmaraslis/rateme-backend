<?php
//FIXME: DEN EINAI SINDEMENA TA TRADING FEES
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


function numberOfDecimals($value) {
    if ((int)$value == $value) { return 0; }  else if (! is_numeric($value))  { return false; }
    return strlen($value) - strrpos($value, '.') - 1;
}

function num_format($numVal,$afterPoint=2,$minAfterPoint=0,$thousandSep=",",$decPoint="."){
    $ret = number_format($numVal,$afterPoint,$decPoint,$thousandSep);
    if($afterPoint!=$minAfterPoint){
        while(($afterPoint>$minAfterPoint) && (substr($ret,-1) =="0") ){
            $ret = substr($ret,0,-1);
            $afterPoint = $afterPoint-1;
        }
    }
    if(substr($ret,-1)==$decPoint) {$ret = substr($ret,0,-1);}
    return $ret;
}



/**
 Functions Required END
 **/



if(isset($_REQUEST['action'])) {
    $swapReturn = array();
    require '../binance/php-binance-api.php';
    $binanceApi = new Binance\API($_REQUEST['apiKey'], $_REQUEST['apiSecret']);
   // $swapReturn['SUPERDEBUG'] = $binanceApi->exchangeInfo2('BNBBTC');
   // echo json_encode($swapReturn);exit();

    if($_REQUEST['action'] == 'placeOrder') {
        //TODO: To limit order code den leitourgei kapia stigmh tha svistei
        $exportedArr = array();
        $exportedOrders = array();
        $notCompletedOrders = array();
        $exportedOrdersCompleted = array();
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        if($orderInfos['depositExplTxId']!= null) {
            $binancePrices = $binanceApi->prices();
            $swapReturn['ted'] = $binancePrices;
            $mixedSymbolDESC = $_REQUEST['giveCoin'] . $_REQUEST['getCoin'];
            $mixedSymbolASC = $_REQUEST['getCoin'] . $_REQUEST['giveCoin'];
            $resultASC = array_key_exists($mixedSymbolASC, $binancePrices);
            $resultDESC = array_key_exists($mixedSymbolDESC, $binancePrices);
            $assetInfos = $binanceApi->assetDetails($_REQUEST['getCoin']);
            $assetInfos2 = $binanceApi->assetDetails($_REQUEST['giveCoin']);
            $givenCoinInfos = $cryptoCoins->show_coins_by_name($orderInfos['givenCoin']);
            $getCoinInfos = $cryptoCoins->show_coins_by_name($orderInfos['getCoin']);
            $gather_is_fees_for_provided_pair = $cryptoCoins->gather_our_fees_by_pair($givenCoinInfos,$getCoinInfos);
            if($orderInfos['placeDone'] != 'www.instaswap.io' && $orderInfos['placeDone'] != 'UserInterface'){
                $gather_partner_fees_for_provided_pair = $apiPartners->gather_partner_fee_by_pair($orderInfos['placeDone'],$givenCoinInfos,$getCoinInfos);
            }else{
                $gather_partner_fees_for_provided_pair = 0;
            }
            if (is_bool($resultASC) && $resultASC == true) {
                $exchangeInfos = $binanceApi->exchangeInfo2($mixedSymbolASC);
                $searchinio = array_search($mixedSymbolASC, array_column($exchangeInfos['symbols'], 'symbol'));
                if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'retryFirstBuyNormal' || $_REQUEST['orderType'] == 'secondCross') {
                    $withdrawalFee = $assetInfos['withdrawFee'];
                }else{
                    $withdrawalFee = 0;
                }
                $binanceWithdrawAmountCounted = numberOfDecimals($_REQUEST['withdrawAmount']);
                $binanceWithdrawAmountCountedSTEP = numberOfDecimals(num_format($exchangeInfos['symbols'][$searchinio]['filters'][2]['stepSize'],4,0));
                if($binanceWithdrawAmountCounted > $binanceWithdrawAmountCountedSTEP){
                    if($binanceWithdrawAmountCountedSTEP == 0){ $binanceWithdrawAmountCountedSTEP = $binanceWithdrawAmountCountedSTEP -1; }
                    $_REQUEST['withdrawAmount'] = substr($_REQUEST['withdrawAmount'] + $withdrawalFee,0,-($binanceWithdrawAmountCounted - $binanceWithdrawAmountCountedSTEP));
                }
                if ($_REQUEST['type'] == 'market') {
                    $searchinio = array_search($mixedSymbolASC, array_column($exchangeInfos['symbols'], 'symbol'));
                    $searchinio2 = array_search('MARKET',$exchangeInfos[$searchinio]['orderTypes']);
                    if(!is_bool($searchinio2)){
                        $orderinio = $binanceApi->marketBuy($mixedSymbolASC, floatval($_REQUEST['withdrawAmount']));
                        $swapReturn['response'] = $orderinio;
                        if($orderinio && !$orderinio['code']){
                            $boughtAmount = 0;
                            $tradeFees = 0;
                            foreach ($orderinio['fills'] as $trades =>$value){
                                $fill = $orderinio['fills'][$trades]['qty'];
                                $tradeFees = $tradeFees + $orderinio['fills'][$trades]['commission'];
                                $feeAsset = $orderinio['fills'][$trades]['commissionAsset'];
                                if($orderinio['fills'][$trades]['commissionAsset'] == $orderInfos['getCoin']){
                                    $boughtAmount = $boughtAmount + ($fill - $orderinio['fills'][$trades]['commission']);
                                }else{
                                    $boughtAmount = $boughtAmount + $fill;
                                }
                            }
                            $ClientPreviewWithdrawAmount = $boughtAmount - $withdrawalFee;
                            $spendAmount = $orderinio['cummulativeQuoteQty'];
                            array_push($exportedOrdersCompleted, $orderinio);
                            $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
                            $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
                            if($_REQUEST['orderType'] == 'firstCross'){
                                $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'btcGetVal',$orderinio['cummulativeQuoteQty']);
                            }else{
                                if($_REQUEST['orderType'] == 'secondCross'){
                                    $order->update_decrypted($orderInfos['id'], 'feeCurrency', 'BTC');
                                    $selectedCoin = 'BTC';
                                }else{
                                    $order->update_decrypted($orderInfos['id'], 'feeCurrency', $orderInfos['givenCoin']);
                                    $selectedCoin = $orderInfos['givenCoin'];
                                }
                                $order->update_decrypted($orderInfos['id'], 'lockedCurrency', floatval($_REQUEST['withdrawAmount']));
                                $order->update_decrypted($orderInfos['id'], 'resultedReceivingAmount', $ClientPreviewWithdrawAmount);
                                if($feeAsset == $orderInfos['givenCoin']){
                                    $extraMinus = $tradeFees;
                                }else{
                                    $extraMinus = 0;
                                }
                                if($_REQUEST['orderType'] == 'secondCross'){
                                    $actualProfit = $orderInfos['btcGetVal'] - $spendAmount - $extraMinus;
                                }else{
                                    $actualProfit = $orderInfos['depositAmount'] - $spendAmount - $extraMinus;
                                }
                                if($gather_partner_fees_for_provided_pair > 0){
                                    $ourProfit = $actualProfit * ($gather_is_fees_for_provided_pair / ($gather_partner_fees_for_provided_pair + $gather_is_fees_for_provided_pair));
                                    $partnerFee = $actualProfit - $ourProfit;
                                    $order->update_decrypted($orderInfos['id'],'profit',$ourProfit);
                                    $order->update_decrypted($orderInfos['id'],'partnerProfit',$partnerFee);
                                    $order->update_decrypted($orderInfos['id'],'ourProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($ourProfit,$selectedCoin),2));
                                    $order->update_decrypted($orderInfos['id'],'partnerProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($partnerFee,$selectedCoin),2));
                                }else{
                                    $ourProfit = $actualProfit;
                                    $order->update_decrypted($orderInfos['id'],'profit',$ourProfit);
                                    $order->update_decrypted($orderInfos['id'],'ourProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($ourProfit,$selectedCoin),2));
                                }
                                $changeOrderState = $order->update_order_state($orderInfos['id'], 2);
                            }
                            $swapReturn['receivedAmountMsg'] = $orderinio['cummulativeQuoteQty'];
                            $swapReturn['stateResponse'] = $changeOrderState;
                        }
                    }else{
                        //   $binanceFees = $binanceApi->tradeFees($mixedSymbolASC);
                        //   if($binanceFees['success']){
                        //$binanceFeesCounted = $binanceFees['tradeFee'][0]['maker'] + $binanceFees['tradeFee'][0]['taker'];
                        //      $countDecimalsOfStep = numberOfDecimals(floatval($exchangeInfos['symbols'][$searchinio]['filters'][2]['stepSize']));
                        //      $binanceFeesCounted =  number_format($binanceFees['tradeFee'][0]['maker'] + $binanceFees['tradeFee'][0]['taker'],$countDecimalsOfStep,'.','');
                        //    if(floatval($binanceFeesCounted) < floatval($exchangeInfos['symbols'][$searchinio]['filters'][2]['stepSize'])){
                        //        $binanceFeesCounted = floatval($exchangeInfos['symbols'][$searchinio]['filters'][2]['stepSize']);
                        //     }
                        //   }else{
                        //$binanceFeesCounted = 0;
                        // }
//                        $chargedTradingFees = $binanceFeesCounted;
//                        $depth = $binanceApi->depth($mixedSymbolASC);
//                        if (count($depth['bids']) != 0) {
//                            $remainingAmount = floatval($_REQUEST['withdrawAmount']) + $withdrawalFee;
//                            $sumBuyPrice = floatval($_REQUEST['depositAmount']);
//                            $meterinio = 0;
//                            $hasError = false;
//                            $summPrice = 0;
//                            $boughtAmount = 0;
//                            foreach ($depth['bids'] as $orderr => $value) {
//                                $hasError = false;
//                                $price = array_search($depth['bids'][$orderr], $depth['bids']);
//                                $amount = $depth['bids'][$orderr];
//                                if ($remainingAmount != 0) {
//                                    if($amount >= ($remainingAmount - ($remainingAmount * $binanceFeesCounted)) && $meterinio === 0){
//                                        $remainingAmount = $remainingAmount - ($remainingAmount * $binanceFeesCounted);
//                                        if(($price * $remainingAmount) <= $_REQUEST['depositAmount']) {
//                                            $move = $binanceApi->buy($mixedSymbolASC,$remainingAmount,$price);
//                                            if ($move['code']) {
//                                                $hasError = true;
//                                                array_push($notCompletedOrders, $move);
//                                                array_push($exportedArr,array('remainingAmount' =>floatval($remainingAmount),'AmountSell'=>$remainingAmount,'price'=>floatval($price),'coin'=>$_REQUEST['getCoin']));
//                                            }else{
//                                                array_push($exportedOrdersCompleted, $move);
//                                                array_push($exportedArr,array('remainingAmount' =>floatval($remainingAmount),'AmountSell'=>$remainingAmount,'price'=>floatval($price),'coin'=>$_REQUEST['getCoin']));
//                                                $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
//                                                $boughtAmount = $boughtAmount + $remainingAmount;
//                                                $remainingAmount = 0;
//                                            }
//                                            /** afta einai gia debug
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$_REQUEST['getCoin'],'way'=>'SELL'));
//                                            $remainingAmount = 0;
//                                            /** afta einai gia debug*/
//                                        }else{
//                                            $hasError = true;
//                                            $errorMsg = 'Not Enough '.$_REQUEST['giveCoin'].' You want to buy '.floatval($_REQUEST['withdrawAmount']).' now price for that is '.$price*$remainingAmount;
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$_REQUEST['getCoin']));
//                                        }
//                                    } else if ($amount < $remainingAmount && $meterinio > 0 && $summPrice < $sumBuyPrice) {
//                                        $move = $binanceApi->buy($mixedSymbolASC,$amount,$price);
//                                        if ($move['code']) {
//                                            $hasError = true;
//                                            array_push($notCompletedOrders, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$amount,'price'=>$price,'coin'=>$_REQUEST['getCoin']));
//                                        }else{
//                                            array_push($exportedOrdersCompleted, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$amount,'price'=>$price,'coin'=>$_REQUEST['getCoin']));
//                                            $summPrice = floatval($summPrice) + floatval($price * $amount);
//                                            $boughtAmount = $boughtAmount + $amount;
//                                            $remainingAmount = (floatval($remainingAmount) - floatval($amount)) + ($move['origQty'] * $binanceFeesCounted);
//                                        }
//                                        /** afta einai gia debug
//                                        array_push($exportedOrders,array('sumPrice' =>$summPrice,'Fee on this trate'=>$binanceFeesCounted,'remainingAmount' =>$remainingAmount,'Amount'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol,'way'=>'Buy'));
//                                        $remainingAmount = floatval($remainingAmount) - floatval($amount);
//                                        $summPrice = floatval($summPrice) + floatval($price * $amount);
//                                        /** afta einai gia debug*/
//                                    }else if($amount >= $remainingAmount && $meterinio > 0 && $summPrice < $sumBuyPrice) {
//                                        $move = $binanceApi->buy($mixedSymbolASC,$remainingAmount,$price);
//                                        if ($move['code']) {
//                                            $hasError = true;
//                                            array_push($notCompletedOrders, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$_REQUEST['getCoin']));
//                                        }else{
//                                            array_push($exportedOrdersCompleted, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$_REQUEST['getCoin']));
//                                        }
//                                        $boughtAmount = $boughtAmount + $remainingAmount;
//                                        $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
//                                        $remainingAmount = 0;
//                                        /** afta einai gia debug
//                                        $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
//                                        array_push($exportedOrders,array('sumPrice' =>$summPrice,'Fee on this trate'=>$stexFeesCounted,'remainingAmount' =>$remainingAmount,'Amount'=>$amount,'price'=>$price,'coin'=>$binancePrices[$serchedASC]->symbol,'way'=>'Buy'));
//                                        $remainingAmount = 0;
//                                        /** afta einai gia debug*/
//                                    }else{
//                                        if($boughtAmount < $_REQUEST['withdrawAmount']) {
//                                            $hasError = true;
//                                            $errorMsg = 'Transaction is not completed \n You Bought : ' . $boughtAmount . ' ' . $_REQUEST['getCoin'] . ' \n Remaining to complete transaction: ' . $remainingAmount . ' ' . $_REQUEST['getCoin'];
//                                            array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $_REQUEST['getCoin']));
//                                        }else{
//                                            $hasError = false;
//                                            $errorMsg = 'Transaction is completed \n You Bought : ' . $boughtAmount . ' ' . $_REQUEST['getCoin'];
//                                            array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $_REQUEST['getCoin']));
//                                        }
//                                    }
//                                }
//                                $meterinio++;
//                            }
//                        }
//                        if (count($exportedOrdersCompleted) > 0) {
//                            $calculateReceivedAmountFromSell = 0;
//                            foreach ($exportedOrdersCompleted as $ord =>$value){
//                                $sumRecieve = $exportedOrdersCompleted[$ord]['origQty'] * $exportedOrdersCompleted[$ord]['price'];
//                                $calculateReceivedAmountFromSell = $calculateReceivedAmountFromSell + $sumRecieve;
//                                $chargedTradingFees = $chargedTradingFees + ($exportedOrdersCompleted[$ord]['origQty'] * $binanceFeesCounted);
//                            }
//                            /** edw mporei na mpei ena koumpi pou na to energopoiei h na to apenergopoiei
//                            gia ta cross pair profit maximzie apla prepei na koitas ti pernei kai ti dinei*/
//                            if($_REQUEST['orderType'] == 'firstCross'){
//                                $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'btcGetVal',$calculateReceivedAmountFromSell);
//                            }
//                            /* if($_REQUEST['orderType'] == 'firstNormal'){
//                                 $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'resultedReceivingAmount',($calculateReceivedAmountFromSell -  floatval($withdrawalFee)));
//                             }*/
//                            /** --- */
//                            $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 2);
//                            $swapReturn['response'] = 'Order is full';
//                            $swapReturn['generalResponse'] = $exportedOrders;
//                            $swapReturn['stateResponse'] = $changeOrderState;
//                            $swapReturn['notCompletedOrders'] = $notCompletedOrders;
//                            $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
//                            $swapReturn['Bids'] = $depth['bids'];
//                            $swapReturn['Asks'] = $depth['asks'];
//                            $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
//                            $swapReturn['tradingFeeCharged'] = $chargedTradingFees;
//                            $swapReturn['orderType'] = $_REQUEST['orderType'];
//                            $swapReturn['errorMsg'] = $errorMsg;
//                            $swapReturn['boughtAmount'] = $boughtAmount;
//                            $swapReturn['boughtAmountMsg'] = 'Bought Amount: '.$boughtAmount.' '.$_REQUEST['getCoin'];
//                            $swapReturn['receivedAmount'] = 'You Sold: '.$calculateReceivedAmountFromSell.' '.$_REQUEST['giveCoin'];
//                            $swapReturn['receivedAmountMsg'] = $calculateReceivedAmountFromSell;
//                        }else{
//                            $swapReturn['generalResponse'] = $exportedOrders;
//                            $swapReturn['response'] = 'Order is not full filled';
//                            $swapReturn['stateResponse'] = $notCompletedOrders;
//                            $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
//                            $swapReturn['Bids'] = $depth['bids'];
//                            $swapReturn['Asks'] = $depth['asks'];
//                            $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
//                            $swapReturn['orderType'] = $_REQUEST['orderType'];
//                            $swapReturn['errorMsg'] = $errorMsg;
//                            $swapReturn['boughtAmount'] = $boughtAmount;
//                            $swapReturn['boughtAmountMsg'] = 'Bought Amount: '.$boughtAmount.' '.$_REQUEST['getCoin'];
//                        }
                    }
                    $swapReturn['DEBUG']['TYPE'] = 'DESC';
                    $swapReturn['DEBUG']['withdrawFee'] = $withdrawalFee;
                    $swapReturn['DEBUG']['assetInfos'] = $assetInfos;
                    $swapReturn['DEBUG']['assetInfos2'] = $assetInfos2;
                    $swapReturn['DEBUG']['giveCoinInfos'] = $givenCoinInfos;
                    $swapReturn['DEBUG']['getCoinInfos'] = $getCoinInfos;
                    $swapReturn['DEBUG']['ClientPreviewWithdrawAmount'] = $ClientPreviewWithdrawAmount;
                    $swapReturn['DEBUG']['spendAmount'] = $spendAmount;
                    $swapReturn['DEBUG']['feeAsset'] = $feeAsset;
                    $swapReturn['DEBUG']['orderResponse'] = $orderinio;
                    $swapReturn['DEBUG']['boughtAmount'] = $boughtAmount;
                    $swapReturn['DEBUG']['ourFee'] = $ourProfit;
                    $swapReturn['DEBUG']['partnerFee'] = $partnerFee;
                }
            } else if (is_bool($resultDESC) && $resultDESC == true) {
                $exchangeInfos = $binanceApi->exchangeInfo2($mixedSymbolDESC);
                $searchinio = array_search($mixedSymbolDESC, array_column($exchangeInfos['symbols'], 'symbol'));
                if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'retryFirstBuyNormal' || $_REQUEST['orderType'] == 'secondCross') {
                    $withdrawalFee = $assetInfos['withdrawFee'];
                }else{
                    $withdrawalFee = 0;
                }
                $tradeFeeInfo = $binanceApi->tradeFees($mixedSymbolDESC);
                if($getCoinInfos['shortname'] == 'USDC' || $getCoinInfos['shortname'] == 'USDT'){
                    $_REQUEST['depositAmount'] = floatval($_REQUEST['depositAmount']);
                }else{
                    $preTradeFeeDepositAmount = floatval($_REQUEST['depositAmount']) - (floatval($_REQUEST['depositAmount']) * floatval($tradeFeeInfo[0]['takerCommission']));
                    $binanceWithdrawAmountCounted = numberOfDecimals($preTradeFeeDepositAmount);
                    $binanceWithdrawAmountCountedSTEP = numberOfDecimals(num_format($exchangeInfos['symbols'][$searchinio]['filters'][2]['stepSize'],4,0));
                    if($binanceWithdrawAmountCounted > $binanceWithdrawAmountCountedSTEP){
                        if($binanceWithdrawAmountCountedSTEP == 0){ $binanceWithdrawAmountCountedSTEP = $binanceWithdrawAmountCountedSTEP -1; }
                        $_REQUEST['depositAmount'] = substr($preTradeFeeDepositAmount,0,-($binanceWithdrawAmountCounted - $binanceWithdrawAmountCountedSTEP));
                    }
                }

                if ($_REQUEST['type'] == 'market') {
                    $searchinio = array_search($mixedSymbolDESC, array_column($exchangeInfos['symbols'], 'symbol'));
                    $searchinio2 = array_search('MARKET',$exchangeInfos[$searchinio]['orderTypes']);
                    if(!is_bool($searchinio2)){
                        $swapReturn['test'] = $_REQUEST['depositAmount'];
                        $swapReturn['desct'] = $binanceWithdrawAmountCountedSTEP;
                        $swapReturn['$binanceWithdrawAmountCounted'] = $binanceWithdrawAmountCounted;
                       $orderinio = $binanceApi->marketSell($mixedSymbolDESC, $_REQUEST['depositAmount']);
                        $swapReturn['response'] = $orderinio;
                        if($orderinio && !$orderinio['code']) {
                            $boughtAmount = 0;
                            $tradeFees = 0;
                            foreach ($orderinio['fills'] as $trades =>$value){
                                $fill = $orderinio['fills'][$trades]['qty'] * $orderinio['fills'][$trades]['price'];
                                $tradeFees = $tradeFees + $orderinio['fills'][$trades]['commission'];
                                $feeAsset = $orderinio['fills'][$trades]['commissionAsset'];
                                if($orderinio['fills'][$trades]['commissionAsset'] == $orderInfos['getCoin']){
                                    $boughtAmount = $boughtAmount + ($fill - $orderinio['fills'][$trades]['commission']);
                                }else{
                                    $boughtAmount = $boughtAmount + $fill;
                                }
                            }
                            $ClientPreviewWithdrawAmount = $boughtAmount - $withdrawalFee;
                            array_push($exportedOrdersCompleted, $orderinio);
                            $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
                            $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
                            if($_REQUEST['orderType'] == 'firstCross'){
                                $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'btcGetVal',$orderinio['cummulativeQuoteQty']);
                            }else{
                                if($_REQUEST['orderType'] == 'secondCross'){
                                    $order->update_decrypted($orderInfos['id'], 'feeCurrency', 'BTC');
                                }else{
                                    $order->update_decrypted($orderInfos['id'], 'feeCurrency', $orderInfos['getCoin']);
                                }
                                $order->update_decrypted($orderInfos['id'], 'lockedCurrency', $orderinio['cummulativeQuoteQty']);
                                if($gather_partner_fees_for_provided_pair > 0){
                                    $ourProfit = $ClientPreviewWithdrawAmount * ($gather_is_fees_for_provided_pair / 100);
                                    $partnerFee = $ClientPreviewWithdrawAmount * ($gather_partner_fees_for_provided_pair / 100);
                                    $ClientPreviewWithdrawAmount = $ClientPreviewWithdrawAmount - $ourProfit - $partnerFee;
                                    $order->update_decrypted($orderInfos['id'], 'resultedReceivingAmount', $ClientPreviewWithdrawAmount);
                                    $order->update_decrypted($orderInfos['id'],'profit',$ourProfit);
                                    $order->update_decrypted($orderInfos['id'],'partnerProfit',$partnerFee);
                                    $order->update_decrypted($orderInfos['id'],'ourProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($ourProfit,$orderInfos['getCoin']),2));
                                    $order->update_decrypted($orderInfos['id'],'partnerProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($partnerFee,$orderInfos['getCoin']),2));
                                }else{
                                    $ourProfit = $ClientPreviewWithdrawAmount * ($gather_is_fees_for_provided_pair / 100);
                                    $ClientPreviewWithdrawAmount = $ClientPreviewWithdrawAmount - $ourProfit;
                                    $order->update_decrypted($orderInfos['id'], 'resultedReceivingAmount', $ClientPreviewWithdrawAmount);
                                    $order->update_decrypted($orderInfos['id'],'profit',$ourProfit);
                                    $order->update_decrypted($orderInfos['id'],'ourProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($ourProfit,$orderInfos['getCoin']),2));
                                }
                                $changeOrderState = $order->update_order_state($orderInfos['id'], 2);
                            }
                            $swapReturn['receivedAmountMsg'] = $orderinio['cummulativeQuoteQty'];
                            $swapReturn['stateResponse'] = $changeOrderState;
                        }
                        $swapReturn['DEBUG']['TYPE'] = 'DESC';
                        $swapReturn['DEBUG']['withdrawFee'] = $withdrawalFee;
                        $swapReturn['DEBUG']['assetInfos'] = $assetInfos;
                        $swapReturn['DEBUG']['assetInfos2'] = $assetInfos2;
                        $swapReturn['DEBUG']['giveCoinInfos'] = $givenCoinInfos;
                        $swapReturn['DEBUG']['getCoinInfos'] = $getCoinInfos;
                        $swapReturn['DEBUG']['ClientPreviewWithdrawAmount'] = $ClientPreviewWithdrawAmount;
                        $swapReturn['DEBUG']['spendAmount'] = $_REQUEST['depositAmount'];
                        $swapReturn['DEBUG']['feeAsset'] = $feeAsset;
                        $swapReturn['DEBUG']['orderResponse'] = $orderinio;
                        $swapReturn['DEBUG']['boughtAmount'] = $boughtAmount;
                        $swapReturn['DEBUG']['ourFee'] = $ourProfit;
                        $swapReturn['DEBUG']['partnerFee'] = $partnerFee;
                    }else{
                        //   $binanceFees = $binanceApi->tradeFees($mixedSymbolDESC);
                        //   if($binanceFees['success']){
                        //       $binanceFeesCounted = $binanceFees['tradeFee'][0]['maker'] + $binanceFees['tradeFee'][0]['taker'];
                        //  }else{
                        //      $binanceFeesCounted = 0;
                        //  }
//                        $binanceFeesCounted = 0;
//                        $chargedTradingFees = 0;
//                        $depth = $binanceApi->depth($mixedSymbolDESC);
//                        $climateCurrency = 0;
//                        $countedResponse = count($depth['asks']);
//                        if ($countedResponse != 0) {
//                            $hasError = false;
//                            $sumBuyPrice = floatval($_REQUEST['withdrawAmount']);
//                            $remainingAmount = floatval($_REQUEST['depositAmount']);
//                            $meterinio = 0;
//                            $boughtAmount = 0;
//                            $summPrice = 0;
//                            foreach ($depth['asks'] as $orderr => $value) {
//                                $price = array_search($depth['asks'][$orderr], $depth['asks']);
//                                $amount = floatval($depth['asks'][$orderr]);
//                                if ($remainingAmount != 0) {
//                                    $hasError = false;
//                                    $sumBuyPrice = floatval($_REQUEST['withdrawAmount']);
//                                    $remainingAmount = floatval($_REQUEST['depositAmount']);
//                                    $meterinio = 0;
//                                    $boughtAmount = 0;
//                                    $summPrice = 0;
//                                    if($amount >= ($remainingAmount - ($remainingAmount * $binanceFeesCounted)) && $meterinio === 0){
//                                        $remainingAmount = $remainingAmount - ($remainingAmount * $binanceFeesCounted);
//                                        if(($price * $remainingAmount) <= $_REQUEST['depositAmount']) {
//                                            $move = $binanceApi->sell($mixedSymbolDESC, '' . $remainingAmount . '', '' . $price . '');
//                                            if ($move['code']) {
//                                                $hasError = true;
//                                                array_push($notCompletedOrders, $move);
//                                                array_push($exportedArr,array('remainingAmount' =>floatval($remainingAmount),'AmountSell'=>$remainingAmount,'price'=>floatval($price),'coin'=>$_REQUEST['giveCoin']));
//                                            }else{
//                                                array_push($exportedOrdersCompleted, $move);
//                                                array_push($exportedArr,array('remainingAmount' =>floatval($remainingAmount),'AmountSell'=>$remainingAmount,'price'=>floatval($price),'coin'=>$_REQUEST['giveCoin']));
//                                                $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
//                                                $boughtAmount = $boughtAmount + $remainingAmount;
//                                                $remainingAmount = 0;
//                                            }
//                                            /** afta einai gia debug
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountSell'=>$remainingAmount,'price'=>$price,'coin'=>$assetInfos['shortname'],'way'=>'SELL'));
//                                            $remainingAmount = 0;
//                                            /** afta einai gia debug*/
//                                        }else{
//                                            $hasError = true;
//                                            $errorMsg = 'Not Enough '.$_REQUEST['giveCoin'].' You want to buy '.floatval($_REQUEST['withdrawAmount']).' now price for that is '.$price*$remainingAmount;
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$_REQUEST['giveCoin']));
//                                        }
//                                    } else if ($amount < $remainingAmount && $meterinio > 0 && $summPrice < $sumBuyPrice) {
//                                        $move = $binanceApi->sell($mixedSymbolDESC, '' . $amount . '', '' . $price . '');
//                                        if ($move['code']) {
//                                            $hasError = true;
//                                            array_push($notCompletedOrders, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$amount,'price'=>$price,'coin'=>$_REQUEST['giveCoin']));
//                                        }else{
//                                            array_push($exportedOrdersCompleted, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$amount,'price'=>$price,'coin'=>$_REQUEST['giveCoin']));
//                                            $summPrice = floatval($summPrice) + floatval($price * $amount);
//                                            $boughtAmount = $boughtAmount + $amount;
//                                            $remainingAmount = (floatval($remainingAmount) - floatval($amount)) + ($move['origQty'] * $binanceFeesCounted);
//                                        }
//                                        /** afta einai gia debug
//                                        array_push($exportedOrders,array('sumPrice' =>$summPrice,'Fee on this trate'=>$binanceFeesCounted,'remainingAmount' =>$remainingAmount,'Amount'=>$amount,'price'=>$price,'coin'=>$_REQUEST['giveCoin'],'way'=>'Buy'));
//                                        $remainingAmount = floatval($remainingAmount) - floatval($amount);
//                                        $summPrice = floatval($summPrice) + floatval($price * $amount);
//                                        /** afta einai gia debug*/
//                                    } else if($amount >= $remainingAmount && $meterinio > 0 && $summPrice < $sumBuyPrice) {
//                                        $move = $binanceApi->sell($mixedSymbolDESC, '' . $remainingAmount . '', '' . $price . '');
//                                        if ($move['code']) {
//                                            $hasError = true;
//                                            array_push($notCompletedOrders, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$_REQUEST['giveCoin']));
//                                        }else{
//                                            array_push($exportedOrdersCompleted, $move);
//                                            array_push($exportedOrders,array('remainingAmount' =>$remainingAmount,'AmountBuy'=>$remainingAmount,'price'=>$price,'coin'=>$_REQUEST['giveCoin']));
//                                        }
//                                        $boughtAmount = $boughtAmount + $remainingAmount;
//                                        $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
//                                        $remainingAmount = 0;
//                                        /** afta einai gia debug
//                                        $summPrice = floatval($summPrice) + floatval($price * $remainingAmount);
//                                        array_push($exportedOrders,array('sumPrice' =>$summPrice,'Fee on this trate'=>$binanceFeesCounted,'remainingAmount' =>$remainingAmount,'Amount'=>$amount,'price'=>$price,'coin'=>$_REQUEST['giveCoin'],'way'=>'Buy'));
//                                        $remainingAmount = 0;
//                                        /** afta einai gia debug*/
//                                    }else{
//                                        if($boughtAmount < $_REQUEST['withdrawAmount']) {
//                                            $hasError = true;
//                                            $errorMsg = 'Transaction is not completed \n You Bought : ' . $boughtAmount . ' ' . $_REQUEST['getCoin'] . ' \n Remaining to complete transaction: ' . $remainingAmount . ' ' . $_REQUEST['getCoin'];
//                                            array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $_REQUEST['giveCoin']));
//                                        }else{
//                                            $hasError = false;
//                                            $errorMsg = 'Transaction is completed \n You Bought : ' . $boughtAmount . ' ' . $_REQUEST['getCoin'];
//                                            array_push($exportedOrders, array('remainingAmount' => $remainingAmount, 'AmountBuy' => $amount, 'price' => $price, 'coin' => $_REQUEST['giveCoin']));
//                                        }
//                                    }
//                                }
//                                $meterinio++;
//                            }
//                        }
//                        if (count($exportedOrdersCompleted) > 0) {
//                            $calculateReceivedAmountFromSell = 0;
//                            foreach ($exportedOrdersCompleted as $ord =>$value){
//                                $sumRecieve = $exportedOrdersCompleted[$ord]['origQty'] * $exportedOrdersCompleted[$ord]['price'];
//                                $calculateReceivedAmountFromSell = $calculateReceivedAmountFromSell + $sumRecieve;
//                                $chargedTradingFees = $chargedTradingFees + ($exportedOrdersCompleted[$ord]['origQty'] * $binanceFeesCounted);
//                            }
//
//                            /** edw mporei na mpei ena koumpi pou na to energopoiei h na to apenergopoiei
//                            gia ta cross pair profit maximzie apla prepei na koitas ti pernei kai ti dinei*/
//                            if($_REQUEST['orderType'] == 'firstCross'){
//                                $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'btcGetVal',$calculateReceivedAmountFromSell);
//                            }
//                            /* if($_REQUEST['orderType'] == 'firstNormal'){
//                                 $changeOrderState = $order->update_decrypted($_REQUEST['txId'],'resultedReceivingAmount',($calculateReceivedAmountFromSell -  floatval($withdrawalFee)));
//                             }*/
//                            /** --- */
//                            $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 2);
//                            $swapReturn['response'] = 'Order is full';
//                            $swapReturn['generalResponse'] = $exportedOrders;
//                            $swapReturn['stateResponse'] = $changeOrderState;
//                            $swapReturn['notCompletedOrders'] = $notCompletedOrders;
//                            $exportedOrdersCompleted['length'] = count($exportedOrdersCompleted);
//                            $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
//                            $swapReturn['Bids'] = $depth['bids'];
//                            $swapReturn['Asks'] = $depth['asks'];
//                            $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
//                            $swapReturn['tradingFeeCharged'] = $chargedTradingFees;
//                            $swapReturn['orderType'] = $_REQUEST['orderType'];
//                            $swapReturn['errorMsg'] = $errorMsg;
//                            $swapReturn['boughtAmount'] = $boughtAmount;
//                            $swapReturn['boughtAmountMsg'] = 'Bought Amount: '.$boughtAmount.' '.$_REQUEST['getCoin'];
//                            $swapReturn['receivedAmount'] = 'You Sold: '.$calculateReceivedAmountFromSell.' '.$_REQUEST['giveCoin'];
//                            $swapReturn['receivedAmountMsg'] = $calculateReceivedAmountFromSell;
//                        }else{
//                            $swapReturn['generalResponse'] = $exportedOrders;
//                            $swapReturn['response'] = 'Order is not full filled';
//                            $swapReturn['stateResponse'] = $notCompletedOrders;
//                            $swapReturn['ordersCompleted'] = $exportedOrdersCompleted;
//                            $swapReturn['Bids'] = $depth['bids'];
//                            $swapReturn['Asks'] = $depth['asks'];
//                            $swapReturn['withdrawFeeCharged'] = $withdrawalFee;
//                            $swapReturn['orderType'] = $_REQUEST['orderType'];
//                            $swapReturn['errorMsg'] = $errorMsg;
//                            $swapReturn['boughtAmount'] = $boughtAmount;
//                            $swapReturn['boughtAmountMsg'] = 'Bought Amount: '.$boughtAmount.' '.$_REQUEST['getCoin'];
//                        }
                    }
                }
            } else {
                $swapReturn['response'] = 'ERROR';
                $swapReturn['stateResponse'] = 'ERROR PAIR IS NOT RECOGNISED';
            }
        }else{
            $swapReturn['response'] = 'ERROR';
            $swapReturn['stateResponse'] = 'Order is not full filled';
        }
    }

    if($_REQUEST['action'] == 'transferBalance'){
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        if($orderInfos['isFiatTransaction'] == 2){
            $depositCoinInfos = $cryptoCoins->show_coins_by_name($orderInfos['givenCoin']);
            if($depositCoinInfos['showDestinationTag'] == 1){
                $tagPhrase = $orderInfos['destinationTagPhrase'];
            }else{
                $tagPhrase = 'is not required';
            }
            $walletInfos = $order->gather_wallet_rotation_address_info($orderInfos['ourReceiveAddress'],$tagPhrase);
            $transfer = $binanceApi->transferBalanceToMainAccount($walletInfos['apiKeyUsed'],'BTC',floatval($orderInfos['btcGetVal']));
            $selectedTransferType = 'toMainAccount';
            if(!$transfer['code']){
                $order->insert_or_update_transfers_table($orderInfos['id'],$orderInfos['btcGetVal'],'BTC',$orderInfos['exchangerId'],$selectedTransferType,'success');
            }else{
                $order->insert_or_update_transfers_table($orderInfos['id'],$orderInfos['btcGetVal'],'BTC',$orderInfos['exchangerId'],$selectedTransferType,'failed',$transfer['msg']);
            }
            $swapReturn['response'] = $transfer;
            if(!$transfer['code']){
                $swapReturn['responseMsg'] = 'Convert Succeed';
            }else{
                $swapReturn['responseMsg'] = $transfer['msg'];
            }
        }else{
            $depositCoinInfos = $cryptoCoins->show_coins_by_name($orderInfos['givenCoin']);
            if($depositCoinInfos['showDestinationTag'] == 1){
                $tagPhrase = $orderInfos['destinationTagPhrase'];
            }else{
                $tagPhrase = 'is not required';
            }
            $walletInfos = $order->gather_wallet_rotation_address_info($orderInfos['ourReceiveAddress'],$tagPhrase);
            $transfer = $binanceApi->transferBalanceToMainAccount($walletInfos['apiKeyUsed'],$orderInfos['givenCoin'],floatval($orderInfos['depositAmount']));
            $selectedTransferType = 'toMainAccount';
            if(!$transfer['code']){
                $order->insert_or_update_transfers_table($orderInfos['id'],$orderInfos['depositAmount'],$orderInfos['givenCoin'],$orderInfos['exchangerId'],$selectedTransferType,'success');
            }else{
                $order->insert_or_update_transfers_table($orderInfos['id'],$orderInfos['depositAmount'],$orderInfos['givenCoin'],$orderInfos['exchangerId'],$selectedTransferType,'failed',$transfer['msg']);
            }
            $swapReturn['response'] = $transfer;
            if(!$transfer['code']){
                $swapReturn['responseMsg'] = 'Convert Succeed';
            }else{
                $swapReturn['responseMsg'] = $transfer['msg'];
            }
        }
    }

    if($_REQUEST['action'] == 'checkOrderInfo'){
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        if($orderInfos){
            if($orderInfos['depositExplTxId'] == null){
                $swapReturn['responseState'] = 'ERROR2';
                $swapReturn['response'] = 'You must detect deposit first in order to create order';
            }else{
                $orderExtraInfos = $order->transaction_extra_moves($_REQUEST['txId'],$_REQUEST['orderInfoWay']);
                if($orderInfos['getCoinInfos']['destinationTagPhrase'] == 'none'){
                    $orderInfos['destinationTagPhrase'] = 'is not required';
                }
                $swapReturn['responseState'] = 'OK';
                $swapReturn['response'] = $orderInfos;
                $swapReturn['extraResponse'] = $orderExtraInfos;
            }
        }else{
            $swapReturn['responseState'] = 'ERROR';
            $swapReturn['response'] = 'Something went wrong please try again!';
        }
    }

    if($_REQUEST['action'] == 'checkBalances') {
        $balances = $binanceApi->balances();
        foreach ($balances as $balance =>$value){
            $toDelete = false;
            $toDelete2 = false;
            if(floatval($balances[$balance]['available']) == 0){ $toDelete = true; }
            if(floatval($balances[$balance]['onOrder']) == 0){ $toDelete2 = true; }
            $balances[$balance]['coinName'] = $balance;
            if($toDelete && $toDelete2){ unset($balances[$balance]); }
        }
         foreach ($balances as $balance =>$value){
             $coinInfos = $cryptoCoins->show_coins_by_name($balance);
             if($coinInfos){
                 $balances[$balance]['coinImage'] = $setting['website_url'].$setting['images'].'small-'.$coinInfos['image'];
             }else{
                 $balances[$balance]['coinImage'] = false;
             }
        }
        $swapReturn['response'] =  array_values($balances);
    }

    if($_REQUEST['action'] == 'checkCoinBalance') {
        $balances = $binanceApi->balances();
        foreach ($balances as $balance =>$value){
            $toDelete = false;
            $toDelete2 = false;
            if(floatval($balances[$balance]['available']) == 0){ $toDelete = true; }
            if(floatval($balances[$balance]['onOrder']) == 0){ $toDelete2 = true; }
            $balances[$balance]['coinName'] = $balance;
            if($toDelete && $toDelete2){ unset($balances[$balance]); }
        }
         foreach ($balances as $balance =>$value){
             if($balances[$balance]['coinName'] == $_REQUEST['giveCoin'] || $balances[$balance]['coinName'] == $_REQUEST['getCoin']){
                 $coinInfos = $cryptoCoins->show_coins_by_name($balance);
                 if($coinInfos){
                     $balances[$balance]['coinImage'] = $setting['website_url'].$setting['images'].'small-'.$coinInfos['image'];
                 }else{
                     $balances[$balance]['coinImage'] = false;
                 }
             }else{
                 unset($balances[$balance]);
             }
        }
        $swapReturn['response'] =  array_values($balances);
    }

    if($_REQUEST['action'] == 'checkOrdersHistory') {
        $binancePrices = $binanceApi->prices();
        $mixedSymbolDESC = $_REQUEST['giveCoin'] . $_REQUEST['getCoin'];
        $mixedSymbolASC = $_REQUEST['getCoin'] . $_REQUEST['giveCoin'];

        $resultASC = array_key_exists($mixedSymbolASC, $binancePrices);
        $resultDESC = array_key_exists($mixedSymbolDESC, $binancePrices);

        if($resultASC){
            $balances = $binanceApi->orders($mixedSymbolASC,1000,0);
            $giveCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['getCoin']);
            $getCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['giveCoin']);
        }else{
            $balances = $binanceApi->orders($mixedSymbolDESC,1000,0);
            $giveCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['giveCoin']);
            $getCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['getCoin']);
        }
         foreach ($balances as $balance =>$value){
             $balances[$balance]['giveCoinImage'] = $setting['website_url'].$setting['images'].'small-'.$giveCoinInfos['image'];
             $balances[$balance]['giveCoin'] = $giveCoinInfos['shortname'];
             $balances[$balance]['getCoinImage'] = $setting['website_url'].$setting['images'].'small-'.$getCoinInfos['image'];
             $balances[$balance]['getCoin'] = $getCoinInfos['shortname'];
        }
        $swapReturn['response'] =  $balances;
    }

    if($_REQUEST['action'] == 'checkDepositHistory') {
        $depositCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['giveCoin']);
        if($depositCoinInfos['showDestinationTag'] == 1){
            $tagPhrase = $_REQUEST['destinationTagPhrase'];
        }else{
            $tagPhrase = 'is not required';
        }
        $gather_wallet_infos = $order->gather_wallet_rotation_address_info($_REQUEST['ourReceiveAddress'],$tagPhrase);
        $balances = $binanceApi->sub_account_depositHistory_with_paging($gather_wallet_infos['apiKeyUsed'],$_REQUEST['giveCoin'],$_REQUEST['pageResultsCount'],$_REQUEST['offset']);
        if($balances['msg']){
            $swapReturn['responseState'] = 'ERROR';
            $swapReturn['response'] = $balances['msg'];
            $swapReturn['test'] = $_REQUEST['ourReceiveAddress'];
            $swapReturn['test2'] = $tagPhrase;
        }else{
            $transactionInfos = $order->swap_by_id($_REQUEST['transactionId']);
            $useaLL = false;
            foreach ($balances as $balance => $value) {
                $balances[$balance]->txid = $balances[$balance]->hash;
                $balances[$balance]->currency_code = $balances[$balance]->currency;
                if($transactionInfos['isFiatTransaction'] == 2){
                    if ($transactionInfos['depositExplTxId'] == $balances[$balance]['txId'] && $transactionInfos['btcGetVal'] == $balances[$balance]['amount']) {
                        $balances[$balance]['isSelectedForThisTransaction'] = true;
                        $useaLL = true;
                    } else {
                        $balances[$balance]['isSelectedForThisTransaction'] = false;
                    }
                }else{
                    if ($transactionInfos['depositExplTxId'] == $balances[$balance]['txId'] && $transactionInfos['depositAmount'] == $balances[$balance]['amount']) {
                        $balances[$balance]['isSelectedForThisTransaction'] = true;
                        $useaLL = true;
                    } else {
                        $balances[$balance]['isSelectedForThisTransaction'] = false;
                    }
                }
                $coinInfos = $cryptoCoins->show_coins_by_name($balances[$balance]['coin']);
                $balances[$balance]['coinImage'] = $setting['website_url'] . $setting['images'] . 'small-' . $coinInfos['image'];
                if ($balances[$balance]['status'] == 1) {
                    $balances[$balance]['statusResponse'] = '<span style="color:green">SUCCESS</span>';
                }
                if ($balances[$balance]['status'] == 0) {
                    $balances[$balance]['statusResponse'] = '<span style="color:orange">PENDING</span>';
                }
                if ($balances[$balance]['status'] == 6) {
                    $balances[$balance]['statusResponse'] = '<span style="color:darkseagreen">Credited (can\'t withdraw)</span>';
                }
                $balances[$balance]['noAnyColor'] = false;
                $checkOrd = $order->check_if_transaction_has_already_assigned_to_exchanger_transaction('deposit', $balances[$balance]['txId'],$balances[$balance]['amount']);
                $checkIfExistsOutsideTxReason = $order->check_if_deposit_exists_on_god_table_by_transaction_hash_and_deposit_amount($balances[$balance]['txId'],$balances[$balance]['amount']);
                if ($checkOrd && !$checkIfExistsOutsideTxReason) {
                    if($checkOrd['signedBy']){
                        $adminDetails = $user->is_admin($checkOrd['signedBy']);
                        $selectedImage = 'https://gintonic.instaswap.io/images/admph/small-'.$adminDetails['image'];
                    }else{
                        $selectedImage = 'https://gintonic.instaswap.io/images/server.png';
                    }
                    $balances[$balance]['extraInfo'] = '<div class="flex-center"><img src="'. $selectedImage .'" style="width:20px;border-radius:100%;"><a target="_blank" href="https://gintonic.instaswap.io/proOrder?id=' . $checkOrd['id'] . '">#' . $checkOrd['id'] . '</a></div>';
                    $balances[$balance]['instaTxId'] = $checkOrd['id'];
                } else if($checkIfExistsOutsideTxReason && !$checkOrd && $checkIfExistsOutsideTxReason['isUsedForProcessTransaction'] == 0){
                    $adminDetails = $user->is_admin($checkIfExistsOutsideTxReason['userHandler']);
                    $selectedImage = 'https://gintonic.instaswap.io/images/admph/small-'.$adminDetails['image'];
                    $balances[$balance]['extraInfo'] = '<td><img src="'. $selectedImage .'" style="width:35px;border-radius:100%;"></td><td><div style="width: 70px;height: 35px;overflow: auto;">'. $checkIfExistsOutsideTxReason['statusMessage'] .'</div></td>';
                    $balances[$balance]['instaTxId'] = '9999999a';
                } else {
                    $balances[$balance]['instaTxId'] = false;
                    $balances[$balance]['extraInfo'] = 'Unused';
                }
            }
            if ($useaLL) {
                foreach ($balances as $balance => $value) {
                    $balances[$balance]['noAnyColor'] = true;
                }
            }
            $swapReturn['response'] = $balances;
        }
    }
    if($_REQUEST['action'] == 'checkWithdrawHistory') {
        $balances = $binanceApi->withdrawHistory($_REQUEST['getCoin']);
       // $balances = $balances['withdrawList'];
        foreach ($balances as $balance =>$value){
            $balances[$balance]['asset'] = $balances[$balance]['coin'];//fix gia to asset sto front
            $coinInfos = $cryptoCoins->show_coins_by_name($balances[$balance]['coin']);
            $balances[$balance]['coinImage'] = $setting['website_url'].$setting['images'].'small-'.$coinInfos['image'];
            if($balances[$balance]['status'] == 0){
                $balances[$balance]['statusResponse'] = '<span style="color:green">Email Sent</span>';
            }
            if($balances[$balance]['status'] == 1){
                $balances[$balance]['statusResponse'] = '<span style="color:darkred">cancelled</span>';
            }
            if($balances[$balance]['status'] == 2){
                $balances[$balance]['statusResponse'] = '<span style="color:darkseagreen">Awaiting Approval</span>';
            }
            if($balances[$balance]['status'] == 3){
                $balances[$balance]['statusResponse'] = '<span style="color:darkseagreen">Rejected</span>';
            }
            if($balances[$balance]['status'] == 4){
                $balances[$balance]['statusResponse'] = '<span style="color:orange">Processing</span>';
            }
            if($balances[$balance]['status'] == 5){
                $balances[$balance]['statusResponse'] = '<span style="color:red">Failure</span>';
            }
            if($balances[$balance]['status'] == 6){
                $balances[$balance]['statusResponse'] = '<span style="color:green">Completed</span>';
            }
        }
        $swapReturn['response'] =  $balances;
    }

    if($_REQUEST['action'] == 'assignDepositHistory'){
        if($_REQUEST['additionType'] == 'assign'){
            if($_REQUEST['typeOfAssign'] == 'AbnormalNoTx'){
                $passedParams = array(
                    'blockHash'=>$_REQUEST['depositExplTxId'],
                    'depositAmount'=>$_REQUEST['depositAmount'],
                    'exchangerId'=> 13,
                    'instaTxId'=> 0,
                    'subAccount'=> $_REQUEST['subAccount'],
                    'status'=> $_REQUEST['status'],
                    'confirmations'=> $_REQUEST['confirmations'],
                    'userHandler'=> USER,
                    'isUsedForProcessTransaction'=> 0,
                    'statusMessage'=> $_REQUEST['reason']
                );
                $response = $order->pass_deposit_on_god($passedParams);
            }else{
                $response = $order->update_ztd($_REQUEST['transactionId'],'depositExplTxId',$_REQUEST['depositExplTxId'],$_REQUEST['depositAmount']);
            }
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
        $assetDetails = json_decode(file_get_contents('https://binanceraw.instaswap.io/raw/exports/binanceCoinInfos.json.txt?ver='.time(),true),true);
        $withdrawIntegerMultiple = 0;
        if($assetDetails){
            $searchedCoin = array_search($transactionInfos['getCoin'],array_column($assetDetails,'coin'));
            if(!is_bool($searchedCoin)){
                foreach ($assetDetails[$searchedCoin]['networkList'] as $network =>$value){
                    if($assetDetails[$searchedCoin]['networkList'][$network]['isDefault']){
                        $withdrawIntegerMultiple = $assetDetails[$searchedCoin]['networkList'][$network]['withdrawIntegerMultiple'];
                    }
                }
            }
        }
        $assetInfos = $binanceApi->assetDetails($transactionInfos['getCoin']);
        $withdrawalFee = $assetInfos['withdrawFee'];
        if($withdrawIntegerMultiple == '0'){
            $allowedDecimals = 8;
        }else if($withdrawIntegerMultiple == '1'){
            $allowedDecimals = 0;
        }else{
            $allowedDecimals = numberOfDecimals(floatval($withdrawIntegerMultiple));
        }
        if($_REQUEST['amount'] != $transactionInfos['resultedReceivingAmount']){
            $transactionInfos['resultedReceivingAmount'] = $_REQUEST['amount'];
        }
        $integerMultipliedWithdrawAmount = number_format($transactionInfos['resultedReceivingAmount'] + $withdrawalFee,$allowedDecimals,'.','');
        $swapReturn['response'] = $integerMultipliedWithdrawAmount;

        if(isset($_REQUEST['failedTry']) && $_REQUEST['failedTry'] == 1){
            $network = $_REQUEST['network'];
        }else{
            if(isset($transactionInfos['network']) && $transactionInfos['network'] !== '' && $transactionInfos['networkType'] == 'getCoin'){
                $network = $transactionInfos['network'];
            }else{
                $network = '';
            }
        }
        $swapReturn['selectedNetwork'] = $network;
        if($_REQUEST['destinationTagPhrase'] == 'is not required'){
            $response = $binanceApi->withdraw($_REQUEST['withdrawCoin'], $_REQUEST['wallet'], $integerMultipliedWithdrawAmount,'',$network);
            if($response){
                if($response['msg']){
                    $swapReturn['responseState'] = 'ERROR';
                    $swapReturn['response'] = $response['msg'];
                }else{
                    $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 3);
                    $updateOrderWithdrawAmount = $order->update_decrypted(intval($_REQUEST['txId']),'resultedReceivingAmount',floatval($_REQUEST['amount']));
                    $assignWithdraw = $order->update_decrypted(intval($_REQUEST['txId']),'withdrawExplTxId',$response['id']);
                    $swapReturn['responseState'] = 'OK';
                    $swapReturn['response'] = $response;
                }
            }else{
                $swapReturn['responseState'] = 'ERROR';
                $swapReturn['response'] = $response;
            }
        }else{
           // $swapReturn['responseState'] = 'ERROR';
           // $swapReturn['response'] = 'DESTINATION ADDRESS REQUIRED AND IS UNDER MAINTENANCE';
            $response = $binanceApi->withdraw($_REQUEST['withdrawCoin'], $_REQUEST['wallet'], $integerMultipliedWithdrawAmount, $_REQUEST['destinationTagPhrase'],$network);
            if($response){
                if($response['msg']){
                    $swapReturn['responseState'] = 'ERROR';
                    $swapReturn['response'] = $response['msg'];
                }else{
                    $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 3);
                    $updateOrderWithdrawAmount = $order->update_decrypted(intval($_REQUEST['txId']),'resultedReceivingAmount',floatval($_REQUEST['amount']) + floatval($withdrawalFee));
                    $assignWithdraw = $order->update_decrypted(intval($_REQUEST['txId']),'withdrawExplTxId',$response['id']);
                    $swapReturn['responseState'] = 'OK';
                    $swapReturn['response'] = $response;
                }
            }else{
                $swapReturn['responseState'] = 'ERROR';
                $swapReturn['response'] = $response;
            }
        }
    }

    if($_REQUEST['action'] == 'refundAction'){
        $transactionInfos = $order->swap_by_id(intval($_REQUEST['txId']));
        if(!is_null($transactionInfos['withdrawExplTxId'])){
            $swapReturn['responseState'] = 'ERROR4';
            $swapReturn['response'] = 'Refund already executed';
            echo json_encode($swapReturn);
            exit();
        }

        if($transactionInfos['signedBy'] != $_SESSION['curr_user']){
            $swapReturn['responseState'] = 'ERROR4';
            $swapReturn['response'] = 'Refund must be executed by '.$transactionInfos['signedBy'];
            echo json_encode($swapReturn);
            exit();
        }
        $withdrawFeeResopnse = $cryptoCoins->gather_withdraw_fee($transactionInfos['getCoin'],$transactionInfos['givenCoin']);
        $giveCoinInfos = $cryptoCoins->show_coins_by_name($transactionInfos['givenCoin']);
        $withdrawFee = $withdrawFeeResopnse->networkFee;
        $finalRefund = 0;
        switch ($_REQUEST['refundType']){
            case 'full':
                $finalRefund = floatval($transactionInfos['depositAmount']) + floatval($withdrawFee);
                break;
            case 'partialNetwork':
                $finalRefund = floatval($transactionInfos['depositAmount']);
                break;
            case 'partialCustom':
                $customWithdrawAmount = $_REQUEST['customRefundAmount'];
                $finalRefund = floatval($customWithdrawAmount);
                break;
            case 'kycDeny':
                $finalRefund = floatval($transactionInfos['depositAmount']) / 1.05;
                break;
            default:
                break;
        }
        $finalRefund = number_format($finalRefund, $giveCoinInfos['allowedDecimals'], '.', '');
        if($_REQUEST['destinationTagPhrase'] == 'is not required'){
            $response = $binanceApi->withdraw($_REQUEST['withdrawCoin'], $_REQUEST['wallet'], floatval($_REQUEST['amount']));
            if($response){
                if($response['msg']){
                    $swapReturn['responseState'] = 'ERROR';
                    $swapReturn['response'] = $response['msg'];
                }else{
                    $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 5);
                    $assignWithdraw = $order->update_decrypted(intval($_REQUEST['txId']),'withdrawExplTxId',$response['id']);
                    $swapReturn['responseState'] = 'OK';
                    $swapReturn['response'] = $response;
                }
            }else{
                $swapReturn['responseState'] = 'ERROR';
                $swapReturn['response'] = $response;
            }
        }else{
            $response = $binanceApi->withdraw($_REQUEST['withdrawCoin'], $_REQUEST['wallet'], floatval($_REQUEST['amount']), $_REQUEST['destinationTagPhrase']);
            if($response){
                $changeOrderState = $order->update_order_state(intval($_REQUEST['txId']), 5);
                $assignWithdraw = $order->update_decrypted(intval($_REQUEST['txId']),'withdrawExplTxId',$response['id']);
                $swapReturn['responseState'] = 'OK';
                $swapReturn['response'] = $response;
            }else{
                $swapReturn['responseState'] = 'ERROR';
                $swapReturn['response'] = $response;
            }
        }
    }


    if($_REQUEST['action'] == 'checkNetworks'){
        $assetDetails = json_decode(file_get_contents('https://binanceraw.instaswap.io/raw/exports/binanceCoinInfos.json.txt?ver='.time(),true),true);
        $giveCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['giveCoin']);
        $getCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['getCoin']);
        $giveAssetShortname = $giveCoinInfos['shortname'];
        $getAssetShortname = $getCoinInfos['shortname'];
        $baseCurrencyInfos = array_values(array_filter($assetDetails,function($perm) use ($giveAssetShortname){ return $perm['coin'] == $giveAssetShortname; }));
        $quoteCurrencyInfos = array_values(array_filter($assetDetails,function($perm) use ($getAssetShortname){ return $perm['coin'] == $getAssetShortname; }));
        $withdrawCurrNetInfos = '';
        $depositCoinInfos = '';
        $withdrawCurrNetInfosState = true;
        $depositCurrNetInfosState = true;
        foreach ($quoteCurrencyInfos[0]['networkList'] as $network =>$value){
            $selectedStateColor = '';
            if($quoteCurrencyInfos[0]['networkList'][$network]['depositEnable']){
                $depositStatus =  '<span><i class="fa fa-download" aria-hidden="true"  style=\'color:limegreen\'></i> <i style="font-size: 8px;">Dep.</i></>';
            }else{
                $withdrawCurrNetInfosState = false;
                $depositStatus = '<span style="border: 1px solid red;"><i class="fa fa-download" aria-hidden="true"  style=\'color:red\'></i> <i style="font-size: 8px;">Dep.</i></>';
                $selectedStateColor = 'background-color:red;';
            }
            if($quoteCurrencyInfos[0]['networkList'][$network]['withdrawEnable']){
                $withdrawStatus = '<span><i class="fa fa-upload" aria-hidden="true"  style=\'color:limegreen\'></i> <i style="font-size: 8px;">With.</i></span>';
            }else{
                $withdrawCurrNetInfosState = false;
                $withdrawStatus = '<span style="border: 1px solid red;"><i class="fa fa-upload" aria-hidden="true"  style=\'color:red\'></i> <i style="font-size: 8px;">With.</i></>';
                $selectedStateColor = 'background-color:red;';
            }
            $withdrawCurrNetInfos .= '<div class="checkBoot col-sm-2 col-xs-2" style="padding:0px;text-align: center;background-color: #2d2d2d;">
            <div class="col-sm-12" style="padding:0px;'.$selectedStateColor.'">'.$quoteCurrencyInfos[0]['networkList'][$network]['network'].' NET</div>
            <div class="col-sm-12" style="padding:0px;">'.$depositStatus.'&nbsp;'.$withdrawStatus.' </div></div>';
        }
        foreach ($baseCurrencyInfos[0]['networkList'] as $network =>$value){
            $selectedStateColor = '';
            if($baseCurrencyInfos[0]['networkList'][$network]['depositEnable']){
                $depositStatus =  '<span><i class="fa fa-download" aria-hidden="true"  style=\'color:limegreen\'></i> <i style="font-size: 8px;">Dep.</i></>';
            }else{
                $depositCurrNetInfosState = false;
                $depositStatus = '<span style="border: 1px solid red;"><i class="fa fa-download" aria-hidden="true"  style=\'color:red\'></i> <i style="font-size: 8px;">Dep.</i></>';
                $selectedStateColor = 'background-color:red;';
            }
            if($baseCurrencyInfos[0]['networkList'][$network]['withdrawEnable']){
                $withdrawStatus = '<span><i class="fa fa-upload" aria-hidden="true"  style=\'color:limegreen\'></i> <i style="font-size: 8px;">With.</i></span>';
            }else{
                $depositCurrNetInfosState = false;
                $withdrawStatus = '<span style="border: 1px solid red;"><i class="fa fa-upload" aria-hidden="true"  style=\'color:red\'></i> <i style="font-size: 8px;">With.</i></>';
                $selectedStateColor = 'background-color:red;';
            }
            $depositCoinInfos .= '<div class="checkBoot col-sm-2 col-xs-2" style="padding:0px;text-align: center;background-color: #2d2d2d;">
            <div class="col-sm-12" style="padding:0px;'.$selectedStateColor.'">'.$baseCurrencyInfos[0]['networkList'][$network]['network'].' NET</div>
            <div class="col-sm-12" style="padding:0px;">'.$depositStatus.'&nbsp;'.$withdrawStatus.' </div></div>';
        }
        $swapReturn['withdrawCurrNetInfosState'] = $withdrawCurrNetInfosState;
        $swapReturn['depositCurrNetInfosState'] = $depositCurrNetInfosState;
        $swapReturn['withdrawCurrNetInfos'] = $withdrawCurrNetInfos;
        $swapReturn['depositCurrNetInfos'] = $depositCoinInfos;
        $swapReturn['giveCoin']['shortname'] = $giveCoinInfos['shortname'];
        $swapReturn['getCoin']['shortname'] = $getCoinInfos['shortname'];
        $swapReturn['giveCoin']['image'] = $setting['website_url'].$setting['images'].'small-'.$giveCoinInfos['image'];
        $swapReturn['getCoin']['image'] = $setting['website_url'].$setting['images'].'small-'.$getCoinInfos['image'];
    }

    echo json_encode($swapReturn);
}

?>
