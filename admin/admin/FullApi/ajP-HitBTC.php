<?php
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


    if($_REQUEST['action'] == 'checkWithdrawHistory') {
       /* $ch = curl_init();
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
            $coinInfos = $cryptoCoins->show_coins_by_name($balances[$balance]->currency);
            $balances[$balance]->coinImage = $setting['website_url'].$setting['images'].'small-'.$coinInfos['image'];
            $balances[$balance]->statusResponse = '<span style="color:'. $balances[$balance]->status_color .'">'. $balances[$balance]->status .'</span>';
            $balances[$balance]->amount = number_format($balances[$balance]->amount,$coinInfos['allowedDecimals'],'.','');
            $balances[$balance]->explorerFullUrl = $balances[$balance]->block_explorer_url.$balances[$balance]->hash;
        }
        $swapReturn['response'] =  $balances;*/
    }

    if($_REQUEST['action'] == 'checkOrdersHistory') {
       /* $stex_coin_pairs = $se->publicCurrencyPairsList('ALL');
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
        $swapReturn['response'] =  array_values($balances->data);*/
    }

    if($_REQUEST['action'] == 'checkCoinBalance') {
      /*  $availableCoins = $cryptoCoins->show_coins();
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
        $swapReturn['response'] = array_values($balances);*/
    }

    if($_REQUEST['action'] == 'checkBalanceOfCoin'){
        /*  $decodedResponse  = $se->wallets('');
          $searched = array_search($_REQUEST['getCoin'],array_column($decodedResponse->data,'currency'));
          $searched2 = array_search($_REQUEST['giveCoin'],array_column($decodedResponse->data,'currency'));
          $swapReturn['response']['giveCoin'] = $decodedResponse->data[$searched2]->balance;
          $swapReturn['response']['getCoin'] = $decodedResponse->data[$searched]->balance;*/
    }


    if($_REQUEST['action'] == 'sendAllToMainAccount'){

        $swapReturn['response'] = $hitbtc->transfer_all_balance_from_sub_to_main('','');


    }



    if($_REQUEST['action'] == 'checkBalances') {
        $balances = $hitbtc->gather_balances('removeNoBalance');
        $swapReturn['response'] = $balances;
        $meter = 0;
        foreach ($balances as $balance){
            $balance->coinName = $balance->currency;
            $coinInfos = $cryptoCoins->show_coins_by_name($balance->currency);
            if($coinInfos){
                $balance->coinImage = $setting['website_url'].$setting['images'].'small-'.$coinInfos['image'];
            }else{
                $balance->coinImage = false;
            }
            $meter++;
        }
        $swapReturn['response'] = array_values($balances);
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

    if($_REQUEST['action'] == 'assignDepositHistory'){
        if($_REQUEST['additionType'] == 'assign'){
            if($_REQUEST['typeOfAssign'] == 'AbnormalNoTx'){
                $passedParams = array(
                    'blockHash'=>$_REQUEST['depositExplTxId'],
                    'depositAmount'=>$_REQUEST['depositAmount'],
                    'exchangerId'=> 19,
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

    if($_REQUEST['action'] == 'transferBalance'){
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        $transfer = $hitbtc->transfer_balance_between_accounts($_REQUEST['type'], $orderInfos);
        $swapReturn['response'] = $transfer;
        if($transfer){
            $swapReturn['responseMsg'] = 'Convert Succeed';
        }else{
            $swapReturn['responseMsg'] = $hitbtc->error;
        }
    }

    if($_REQUEST['action'] == 'convertBalance'){
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        $convert = $hitbtc->convert_coins($_REQUEST['type'], $orderInfos);
        $swapReturn['response'] = $convert;
        if($convert){
            $swapReturn['responseMsg'] = 'Convert Succeed';
        }else{
            $swapReturn['responseMsg'] = 'Convert Failed Please try again reason: '.$hitbtc->error;
        }
    }

    if($_REQUEST['action'] == 'placeOrder') {
        $exportedOrders = array();
        $notCompletedOrders = array();
        $exportedOrdersCompleted = array();
        $orderInfos = $order->swap_by_id($_REQUEST['txId']);
        $mixedSymbolDESC = $_REQUEST['giveCoin'] . $_REQUEST['getCoin'];
        $mixedSymbolASC = $_REQUEST['getCoin'] . $_REQUEST['giveCoin'];
        $giveCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['giveCoin']);
        $getCoinInfos = $cryptoCoins->show_coins_by_name($_REQUEST['getCoin']);
        $gather_is_fees_for_provided_pair = $cryptoCoins->gather_our_fees_by_pair($giveCoinInfos,$getCoinInfos);
        if($orderInfos['placeDone'] != 'www.instaswap.io'){
            $gather_partner_fees_for_provided_pair = $apiPartners->gather_partner_fee_by_pair($orderInfos['placeDone'],$giveCoinInfos,$getCoinInfos);
        }else{
            $gather_partner_fees_for_provided_pair = 0;
        }
        if($orderInfos['depositExplTxId']!= null) {
            /** AFTO EINAI TO FIX GIA TO USDT GIATI STO HitBtc TO EXEI USD*/
            if($_REQUEST['giveCoin'] == 'USDT'){ $mixedSymbolDESC = 'USD' . $_REQUEST['getCoin']; $mixedSymbolASC = $_REQUEST['getCoin'] . 'USD';}
            if($_REQUEST['getCoin'] == 'USDT'){ $mixedSymbolDESC =  $_REQUEST['giveCoin'] . 'USD'; $mixedSymbolASC =   'USD'.  $_REQUEST['giveCoin'];}
            $serchedASC = $hitbtc->gather_symbol_infos($mixedSymbolASC);
            $serchedDESC = $hitbtc->gather_symbol_infos($mixedSymbolDESC);
            if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'secondCross') {
                $hitbtcWithdrawFees = $hitbtc->gather_withdraw_fees($_REQUEST['getCoin'],$_REQUEST['withdrawAmount']);
                if(!$hitbtcWithdrawFees->error){ $withdrawalFee = floatval($hitbtcWithdrawFees); }else{ $withdrawalFee = 0; }
            }else{
                $withdrawalFee = 0;
            }
            if(!$serchedASC->error){//BUY
                $asksOrderBook = $hitbtc->gather_order_book($mixedSymbolASC,$_REQUEST['withdrawAmount']);
                /** AFTO EINAI TO FIX GIA TO USDT GIATI STO HitBtc TO EXEI USD*/
                if($serchedASC->feeCurrency == 'USD'){ $serchedASC->feeCurrency = 'USDT';}
                if($_REQUEST['giveCoin'] == $serchedASC->feeCurrency){
                    $tradingCost = $_REQUEST['depositAmount'] * $serchedASC->takeLiquidityRate;
                    if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'secondCross') {
                        $IS_fee = $_REQUEST['depositAmount'] * ($gather_is_fees_for_provided_pair / 100);
                        if($orderInfos['placeDone'] != 'www.instaswap.io') {
                            $partnerFee = $_REQUEST['depositAmount'] * ($gather_partner_fees_for_provided_pair / 100);
                        }else{
                            $partnerFee = 0;
                        }
                    }else{
                        $partnerFee = 0;
                        $IS_fee = 0;
                    }
                    $net_deposit_amount = $_REQUEST['depositAmount'] - $tradingCost - $IS_fee - $partnerFee;
                    $orderBookExtraInfos = $hitbtc->find_buy_price_and_weight_average('ask',$asksOrderBook,$net_deposit_amount);
                    $decimalsAllowed = strlen($serchedASC->quantityIncrement) - strrpos($serchedASC->quantityIncrement, '.') - 1;
                    $buyAmount = floatval(number_format($net_deposit_amount / $orderBookExtraInfos['weightAv'],$decimalsAllowed,'.',''));
                    $move = $hitbtc->addTradingOrdersByPair($mixedSymbolASC, 'BUY','limit', $buyAmount, $orderBookExtraInfos['lastSelectedPrice'],$orderInfos['id'],'IOC');
                    if($move->code){ http_response_code(450); echo json_encode($move); exit(); }
                  //  if($move->code){ http_response_code(450); $test['$net_deposit_amount']=$net_deposit_amount;$test['buyAmount']=$buyAmount;$test['lastSelectedPrice']=$orderBookExtraInfos['lastSelectedPrice']; echo json_encode($test); exit(); }
                    if($move->status !== 'filled'){ http_response_code(450); $jsonReturn['repeat'] = 'Order is not filled and was canceled. Try again'; echo json_encode($jsonReturn); exit(); }
                    $boughtAmount = 0;$spendAmount = 0;
                    foreach ($move->tradesReport as $tradeReport =>$value){
                        $boughtAmount = $boughtAmount + $move->tradesReport[$tradeReport]->quantity;
                        $spendAmount = $spendAmount + ($move->tradesReport[$tradeReport]->price * $move->tradesReport[$tradeReport]->quantity);
                    }
                    if($withdrawalFee == 0){ $gasFees = $tradingCost; }else{ $gasFees = $tradingCost + ($move->price / $withdrawalFee); }
                    $ClientPreviewWithdrawAmount = $boughtAmount - $withdrawalFee;
                }else{
                    $net_deposit_amount = $_REQUEST['depositAmount'];
                    $orderBookExtraInfos = $hitbtc->find_buy_price_and_weight_average('ask',$asksOrderBook,$net_deposit_amount);
                    $decimalsAllowed = strlen($serchedASC->quantityIncrement) - strrpos($serchedASC->quantityIncrement, '.') - 1;
                    $buyAmount = floatval(number_format($net_deposit_amount / $orderBookExtraInfos['weightAv'],$decimalsAllowed,'.',''));
                    $move = $hitbtc->addTradingOrdersByPair($mixedSymbolASC, 'BUY','limit', $buyAmount, $orderBookExtraInfos['lastSelectedPrice'],$orderInfos['id'],'IOC');
                    if($move->code){ http_response_code(450); echo json_encode($move); exit(); }
                    //  if($move->code){ http_response_code(450); $test['$net_deposit_amount']=$net_deposit_amount;$test['buyAmount']=$buyAmount;$test['lastSelectedPrice']=$orderBookExtraInfos['lastSelectedPrice']; echo json_encode($test); exit(); }
                    if($move->status !== 'filled'){ http_response_code(450); $jsonReturn['repeat'] = 'Order is not filled and was canceled. Try again'; echo json_encode($jsonReturn); exit(); }
                    $boughtAmount = 0;$tradingCost = 0;$spendAmount = 0;
                    foreach ($move->tradesReport as $tradeReport =>$value){
                        $boughtAmount = $boughtAmount + $move->tradesReport[$tradeReport]->quantity;
                        $tradingCost = $tradingCost + $move->tradesReport[$tradeReport]->fee;
                        $spendAmount = $spendAmount + ($move->tradesReport[$tradeReport]->price * $move->tradesReport[$tradeReport]->quantity);
                    }
                    if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'secondCross') {
                        $IS_fee = $boughtAmount * ($gather_is_fees_for_provided_pair / 100);
                        if($orderInfos['placeDone'] != 'www.instaswap.io') {
                            $partnerFee = $_REQUEST['depositAmount'] * ($gather_partner_fees_for_provided_pair / 100);
                        }else{
                            $partnerFee = 0;
                        }
                    }else{
                        $partnerFee = 0;
                        $IS_fee = 0;
                    }
                    $ClientPreviewWithdrawAmount = $boughtAmount - $tradingCost - $IS_fee - $partnerFee - $withdrawalFee;
                    $gasFees = $tradingCost + $withdrawalFee;
                }
                if($_REQUEST['orderType'] == 'firstCross'){
                    $changeOrderState = $order->update_decrypted($orderInfos['id'],'btcGetVal',$boughtAmount);
                }else{
                    $order->update_decrypted($orderInfos['id'], 'feeCurrency', $serchedASC->feeCurrency);
                    $order->update_decrypted($orderInfos['id'],'profit',$IS_fee);
                    $order->update_decrypted($orderInfos['id'], 'lockedCurrency', $boughtAmount);
                    $order->update_decrypted($orderInfos['id'], 'resultedReceivingAmount', $ClientPreviewWithdrawAmount);
                    $order->update_decrypted($orderInfos['id'],'ourProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($IS_fee,$serchedASC->feeCurrency),2));
                    if($partnerFee > 0){
                        $order->update_decrypted($orderInfos['id'],'partnerProfit',$partnerFee);
                        $order->update_decrypted($orderInfos['id'],'partnerProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($partnerFee,$serchedASC->feeCurrency),2));
                    }
                    $changeOrderState = $order->update_order_state($orderInfos['id'], 2);
                }

                /**  OUTPUTS  START */
                foreach ($move->tradesReport as $tradeReport =>$value){
                    $swapReturn['Transaction']['move'][$tradeReport]['quantity'] = $move->tradesReport[$tradeReport]->quantity;
                    $swapReturn['Transaction']['move'][$tradeReport]['price'] = $move->tradesReport[$tradeReport]->price;
                }

                $swapReturn['Transaction']['spendAmount'] = $spendAmount;
                $swapReturn['Transaction']['spendAmountCurrency'] = $giveCoinInfos['shortname'];
                $swapReturn['Transaction']['gatheredAmount'] = $boughtAmount;
                $swapReturn['Transaction']['gatheredAmountCurrency'] = $getCoinInfos['shortname'];
                $swapReturn['Transaction']['tradingFees'] = $tradingCost;
                $swapReturn['Transaction']['feeCurrency'] = $serchedASC->feeCurrency;
                /**  OUTPUTS  END */

                /**  DEBUG OUTPUTS  START */
                $swapReturn['Transaction']['DEBUG']['transactionType'] = 'BUY';
                $swapReturn['Transaction']['DEBUG']['orderinfos'] = $orderInfos;
                $swapReturn['Transaction']['DEBUG']['symbolInfos'] = $serchedASC;
                $swapReturn['Transaction']['DEBUG']['move'] = $move;
                $swapReturn['Transaction']['DEBUG']['gasFees'] = $gasFees;
                $swapReturn['Transaction']['DEBUG']['withdrawFee'] = $withdrawalFee;
                $swapReturn['Transaction']['DEBUG']['tradingCost'] = $tradingCost;
                $swapReturn['Transaction']['DEBUG']['IS_fee'] = $IS_fee;
                $swapReturn['Transaction']['DEBUG']['IS_partner_fee'] = $partnerFee;
                $swapReturn['Transaction']['DEBUG']['clientReceiveAmount'] = $ClientPreviewWithdrawAmount;
                $swapReturn['Transaction']['DEBUG']['clientWithdrawAmount'] = $ClientPreviewWithdrawAmount + $withdrawalFee;
                $swapReturn['Transaction']['DEBUG']['orderBookFull'] = $asksOrderBook;
                $swapReturn['Transaction']['DEBUG']['orderBookUsed'] = $orderBookExtraInfos['ordersUsed'];
                $swapReturn['Transaction']['DEBUG']['weightedAv'] = $orderBookExtraInfos['weightAv'];
                $swapReturn['Transaction']['DEBUG']['buyPrice'] = $orderBookExtraInfos['lastSelectedPrice'];
                $swapReturn['Transaction']['DEBUG']['soldDepositAmount'] = $spendAmount;
                $swapReturn['Transaction']['DEBUG']['boughtAmount'] = $boughtAmount;
                $swapReturn['Transaction']['DEBUG']['netDepositAmount'] = $net_deposit_amount;
                /**  DEBUG OUTPUTS  END */
            }
            if(!$serchedDESC->error){//sell
                $bidsOrderbook = $hitbtc->gather_order_book($mixedSymbolDESC,$_REQUEST['depositAmount']);
                /** AFTO EINAI TO FIX GIA TO USDT GIATI STO HitBtc TO EXEI USD*/
                if($serchedDESC->feeCurrency == 'USD'){ $serchedDESC->feeCurrency = 'USDT';}
                if($_REQUEST['giveCoin'] == $serchedDESC->feeCurrency){
                    $tradingCost = $_REQUEST['depositAmount'] * $serchedDESC->takeLiquidityRate;
                    if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'secondCross') {
                        $IS_fee = $_REQUEST['depositAmount'] * ($gather_is_fees_for_provided_pair / 100);
                        if($orderInfos['placeDone'] != 'www.instaswap.io') {
                            $partnerFee = $_REQUEST['depositAmount'] * ($gather_partner_fees_for_provided_pair / 100);
                        }else{
                            $partnerFee = 0;
                        }
                    }else{
                        $IS_fee = 0;
                        $partnerFee = 0;
                    }
                    $net_deposit_amount = $_REQUEST['depositAmount'] - $tradingCost - $IS_fee - $partnerFee;
                    $orderBookExtraInfos = $hitbtc->find_buy_price_and_weight_average('bid',$bidsOrderbook,$net_deposit_amount);
                    if($withdrawalFee == 0){ $gasFees = $tradingCost; }else{ $gasFees = $tradingCost + ($orderBookExtraInfos['weightAv'] * $withdrawalFee); }
                    $decimalsAllowed = strlen($serchedDESC->quantityIncrement) - strrpos($serchedDESC->quantityIncrement, '.') - 1;
                    $sellAmount = floatval(number_format($net_deposit_amount,$decimalsAllowed,'.',''));
                    $move = $hitbtc->addTradingOrdersByPair($mixedSymbolDESC, 'SELL','limit', $sellAmount, $orderBookExtraInfos['lastSelectedPrice'],$orderInfos['id'],'IOC');
                    //  if($move->code){ http_response_code(450); $test['$net_deposit_amount']=$net_deposit_amount;$test['buyAmount']=$buyAmount;$test['lastSelectedPrice']=$orderBookExtraInfos['lastSelectedPrice']; echo json_encode($test); exit(); }
                    if($move->code){ http_response_code(450); echo json_encode($move); exit(); }
                    if($move->status !== 'filled'){ http_response_code(450); $jsonReturn['repeat'] = 'Order is not filled and was canceled. Try again'; echo json_encode($jsonReturn); exit(); }
                     $boughtAmount = 0;
                     foreach ($move->tradesReport as $tradeReport =>$value){
                         $boughtAmount = $boughtAmount + ($move->tradesReport[$tradeReport]->price * $move->tradesReport[$tradeReport]->quantity);
                     }
                    $ClientPreviewWithdrawAmount = $boughtAmount - $withdrawalFee;
                }else{
                    $net_deposit_amount = $_REQUEST['depositAmount'];
                    $orderBookExtraInfos = $hitbtc->find_buy_price_and_weight_average('bid',$bidsOrderbook,$net_deposit_amount);
                    $decimalsAllowed = strlen($serchedDESC->quantityIncrement) - strrpos($serchedDESC->quantityIncrement, '.') - 1;
                    $sellAmount = floatval(number_format($net_deposit_amount,$decimalsAllowed,'.',''));
                    $move = $hitbtc->addTradingOrdersByPair($mixedSymbolDESC, 'SELL','limit', $sellAmount, $orderBookExtraInfos['lastSelectedPrice'],$orderInfos['id'],'IOC');
                    //  if($move->code){ http_response_code(450); $test['$net_deposit_amount']=$net_deposit_amount;$test['buyAmount']=$buyAmount;$test['lastSelectedPrice']=$orderBookExtraInfos['lastSelectedPrice']; echo json_encode($test); exit(); }
                    if($move->code){ http_response_code(450); echo json_encode($move); exit(); }
                    if($move->status !== 'filled'){ http_response_code(450); $jsonReturn['repeat'] = 'Order is not filled and was canceled. Try again'; echo json_encode($jsonReturn); exit(); }
                    $boughtAmount = 0;$tradingCost = 0;$spendAmount = 0;
                    foreach ($move->tradesReport as $tradeReport =>$value){
                        $boughtAmount = $boughtAmount + ($move->tradesReport[$tradeReport]->price * $move->tradesReport[$tradeReport]->quantity);
                        $tradingCost = $tradingCost + $move->tradesReport[$tradeReport]->fee;
                        $spendAmount = $spendAmount + $move->tradesReport[$tradeReport]->quantity;
                    }
                    if($_REQUEST['orderType'] == 'firstNormal' || $_REQUEST['orderType'] == 'secondCross') {
                        $IS_fee = $boughtAmount * ($gather_is_fees_for_provided_pair / 100);
                        if($orderInfos['placeDone'] != 'www.instaswap.io') {
                            $partnerFee = $boughtAmount * ($gather_partner_fees_for_provided_pair / 100);
                        }else{
                            $partnerFee = 0;
                        }
                    }else{
                        $partnerFee = 0;
                        $IS_fee = 0;
                    }
                    $ClientPreviewWithdrawAmount = $boughtAmount - $tradingCost - $IS_fee - $partnerFee - $withdrawalFee;
                    $gasFees = $tradingCost + $withdrawalFee;
                }
              if($_REQUEST['orderType'] == 'firstCross'){
                  $changeOrderState = $order->update_decrypted($orderInfos['id'],'btcGetVal',$boughtAmount);
              }else{
                  $order->update_decrypted($orderInfos['id'],'feeCurrency',$serchedDESC->feeCurrency);
                  $order->update_decrypted($orderInfos['id'],'profit',$IS_fee);
                  $order->update_decrypted($orderInfos['id'],'lockedCurrency',$boughtAmount);
                  $order->update_decrypted($orderInfos['id'],'resultedReceivingAmount',$ClientPreviewWithdrawAmount);
                  $order->update_decrypted($orderInfos['id'],'ourProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($IS_fee,$serchedDESC->feeCurrency),2));
                  if($partnerFee > 0){
                      $order->update_decrypted($orderInfos['id'],'partnerProfit',$partnerFee);
                      $order->update_decrypted($orderInfos['id'],'partnerProfitEurStamp',round($cryptoCoins->calculate_profit_in_euro($partnerFee,$serchedDESC->feeCurrency),2));
                  }
                  $changeOrderState = $order->update_order_state($orderInfos['id'], 2);
              }

               /**  OUTPUTS  START */
                foreach ($move->tradesReport as $tradeReport =>$value){
                    $swapReturn['Transaction']['move'][$tradeReport]['quantity'] = $move->tradesReport[$tradeReport]->quantity;
                    $swapReturn['Transaction']['move'][$tradeReport]['price'] = $move->tradesReport[$tradeReport]->price;
                }
                $swapReturn['Transaction']['spendAmount'] = $spendAmount;
                $swapReturn['Transaction']['spendAmountCurrency'] = $giveCoinInfos['shortname'];
                $swapReturn['Transaction']['gatheredAmount'] = $boughtAmount;
                $swapReturn['Transaction']['gatheredAmountCurrency'] = $getCoinInfos['shortname'];
                $swapReturn['Transaction']['tradingFees'] = $tradingCost;
                $swapReturn['Transaction']['feeCurrency'] = $serchedDESC->feeCurrency;

                /**  OUTPUTS  END */

               /**  DEBUG OUTPUTS  START */
                $swapReturn['Transaction']['DEBUG']['transactionType'] = 'SELL';
                $swapReturn['Transaction']['DEBUG']['orderinfos'] = $orderInfos;
                $swapReturn['Transaction']['DEBUG']['symbolInfos'] = $serchedDESC;
                $swapReturn['Transaction']['DEBUG']['move'] = $move;
                $swapReturn['Transaction']['DEBUG']['gasFees'] = $gasFees;
                $swapReturn['Transaction']['DEBUG']['withdrawFee'] = $withdrawalFee;
                $swapReturn['Transaction']['DEBUG']['tradingCost'] = $tradingCost;
                $swapReturn['Transaction']['DEBUG']['IS_fee'] = $IS_fee;
                $swapReturn['Transaction']['DEBUG']['IS_partner_fee'] = $partnerFee;
                $swapReturn['Transaction']['DEBUG']['clientReceiveAmount'] = $ClientPreviewWithdrawAmount;
                $swapReturn['Transaction']['DEBUG']['clientWithdrawAmount'] = $ClientPreviewWithdrawAmount + $withdrawalFee;
                $swapReturn['Transaction']['DEBUG']['orderBookFull'] = $bidsOrderbook;
                $swapReturn['Transaction']['DEBUG']['orderBookUsed'] = $orderBookExtraInfos['ordersUsed'];
                $swapReturn['Transaction']['DEBUG']['weightedAv'] = $orderBookExtraInfos['weightAv'];
                $swapReturn['Transaction']['DEBUG']['sellPrice'] = $orderBookExtraInfos['lastSelectedPrice'];
                $swapReturn['Transaction']['DEBUG']['soldDepositAmount'] = $spendAmount;
                $swapReturn['Transaction']['DEBUG']['boughtAmount'] = $boughtAmount;
                $swapReturn['Transaction']['DEBUG']['netDepositAmount'] = $net_deposit_amount;
                /**  DEBUG OUTPUTS  END */
            }
            if($_REQUEST['giveCoin'] == 'USDT'){ $mixedSymbolDESC = $_REQUEST['giveCoin'] . $_REQUEST['getCoin']; $mixedSymbolASC = $_REQUEST['getCoin'] .$_REQUEST['giveCoin'];}
            if($_REQUEST['getCoin'] == 'USDT'){ $mixedSymbolDESC = $_REQUEST['giveCoin'] . $_REQUEST['getCoin']; $mixedSymbolASC = $_REQUEST['getCoin'] .$_REQUEST['giveCoin'];}
        }else{
            http_response_code(450);
            $swapReturn['response'] = 'Order is not assigned to a deposit, Assign First and try again';
            echo json_encode($swapReturn);
            exit();
        }
    }

    if($_REQUEST['action'] == 'checkDepositHistory') {
        $transactionInfos = $order->swap_by_id($_REQUEST['transactionId']);
        $deposits = $hitbtc->gather_deposits(array('currency'=>$_REQUEST['giveCoin'], 'limit'=>$_REQUEST['pageResultsCount'], 'offset'=>$_REQUEST['offset']));
        $swapReturn['response'] = $deposits;
        $balances = $deposits;
        $useaLL = false;
        foreach ($balances as $balance => $value) {
            switch ($balances[$balance]->status){
                case 'created':
                    $balances[$balance]->status_color = 'grey';
                    break;
                case 'pending':
                    $balances[$balance]->status_color = 'darkOrange';
                    break;
                case 'failed':
                    $balances[$balance]->status_color = 'red';
                    break;
                case 'success':
                    $balances[$balance]->status_color = '#00BE75';
                    break;
            }
            $balances[$balance]->txid = $balances[$balance]->hash;
            $balances[$balance]->currency_code = $balances[$balance]->currency;
            $balances[$balance]->test1 = $transactionInfos['depositExplTxId'];
            $balances[$balance]->test2 = $balances[$balance]->hash;
            $coinInfos = $cryptoCoins->show_coins_by_name($balances[$balance]->currency);
            if($transactionInfos['isFiatTransaction'] == 2){
                if ($transactionInfos['depositExplTxId'] == $balances[$balance]->hash && number_format($transactionInfos['btcGetVal'],$coinInfos['allowedDecimals'],'.','')  == number_format($balances[$balance]->amount,$coinInfos['allowedDecimals'],'.','')) {
                    $balances[$balance]->isSelectedForThisTransaction = true;
                    $useaLL = true;
                } else {
                    $balances[$balance]->isSelectedForThisTransaction = false;
                }
            }else{
                if ($transactionInfos['depositExplTxId'] == $balances[$balance]->hash && number_format($transactionInfos['depositAmount'],$coinInfos['allowedDecimals'],'.','')  == number_format($balances[$balance]->amount,$coinInfos['allowedDecimals'],'.','')) {
                    $balances[$balance]->isSelectedForThisTransaction = true;
                    $useaLL = true;
                } else {
                    $balances[$balance]->isSelectedForThisTransaction = false;
                }
            }
            $balances[$balance]->coinImage = $setting['website_url'] . $setting['images'] . 'small-' . $coinInfos['image'];
            $balances[$balance]->statusResponse = '<span style="color:'. $balances[$balance]->status_color .'">'. $balances[$balance]->status .'</span>';
            $balances[$balance]->noAnyColor = false;
            $checkIfExistsOutsideTxReason = $order->check_if_deposit_exists_on_god_table_by_transaction_hash_and_deposit_amount($balances[$balance]->hash,$balances[$balance]->amount);
            $checkOrd = $order->check_if_transaction_has_already_assigned_to_exchanger_transaction('deposit', $balances[$balance]->hash,$balances[$balance]->amount);
            if ($checkOrd && !$checkIfExistsOutsideTxReason) {
                $adminDetails = $user->is_admin($checkOrd['signedBy']);
                if($adminDetails){
                    $balances[$balance]->extraInfo = '<div class="flex-center"><img src="https://gintonic.instaswap.io/images/admph/small-' . $adminDetails['image'] . '" style="width:20px;border-radius:100%;"><a target="_blank" href="https://gintonic.instaswap.io/proOrder?id=' . $checkOrd['id'] . '">#' . $checkOrd['id'] . '</a></div>';
                }else{
                    $balances[$balance]->extraInfo = '<div class="flex-center"><img src="/images/server.png" style="width:20px;"><a target="_blank" href="https://gintonic.instaswap.io/proOrder?id=' . $checkOrd['id'] . '">#' . $checkOrd['id'] . '</a></div>';
                }
                $balances[$balance]->instaTxId = $checkOrd['id'];
            }else if($checkIfExistsOutsideTxReason && !$checkOrd && $checkIfExistsOutsideTxReason['isUsedForProcessTransaction'] == 0){
                $adminDetails = $user->is_admin($checkIfExistsOutsideTxReason['userHandler']);
                $selectedImage = 'https://gintonic.instaswap.io/images/admph/small-'.$adminDetails['image'];
                $balances[$balance]->extraInfo = '<td><img src="'. $selectedImage .'" style="width:35px;border-radius:100%;"></td><td><div style="width: 70px;height: 35px;overflow: auto;">'. $checkIfExistsOutsideTxReason['statusMessage'] .'</div></td>';
                $balances[$balance]->instaTxId = '9999999a';
            } else {
                $balances[$balance]->instaTxId = false;
                $balances[$balance]->extraInfo = 'Unused';
            }
        }
        if ($useaLL) {
            foreach ($balances as $balance => $value) {
                $balances[$balance]->noAnyColor = true;
            }
        }
        $swapReturn['response'] = $balances;
        $swapReturn['USED'] = $_SESSION['HITCredsUsedForThisTx'];
    }

    if($_REQUEST['action'] == 'withdrawAction') {
        $transactionInfos = $order->swap_by_id(intval($_REQUEST['txId']));
        if (!is_null($transactionInfos['withdrawExplTxId'])) {
            $swapReturn['responseState'] = 'ERROR2';
            $swapReturn['response'] = 'Withdraw already executed';
            echo json_encode($swapReturn);
            exit();
        }
        if ($transactionInfos['signedBy'] != $_SESSION['curr_user']) {
            $swapReturn['responseState'] = 'ERROR3';
            $swapReturn['response'] = 'Withdraw must be executed by ' . $transactionInfos['signedBy'];
            echo json_encode($swapReturn);
            exit();
        }
        $withdrawCurrHit = $hitbtc->gather_withdraw_fees($transactionInfos['getCoin'], $transactionInfos['resultedReceivingAmount']);
        if (!$withdrawCurrHit->error) {
            $withdrawalFee = floatval($withdrawCurrHit);
        } else {
            $swapReturn['responseState'] = 'ERROR';
            $swapReturn['response'] = 'Cant declare withdraw fee reason: ' . $withdrawalFee->message;
            echo json_encode($swapReturn);
            exit();
        }
        $withdrawAmount = floatval($_REQUEST['amount']);
        if ($transactionInfos['destinationTagPhrase'] == 'is not required') {
            $response = $hitbtc->addWithdrawal($transactionInfos['getCoin'], $withdrawAmount, $transactionInfos['clientRefundAddress'], $encryption_class->safeEncrypt(($transactionInfos['id'])));
        } else {
            $response = $hitbtc->addWithdrawal($transactionInfos['getCoin'], $withdrawAmount, $transactionInfos['clientRefundAddress'], $encryption_class->safeEncrypt(($transactionInfos['id'])), $transactionInfos['destinationTagPhrase']);
        }
        if ($response) {
            $swapReturn['responseState'] = 'OK';
            $swapReturn['response'] = $response->id;
               $changeOrderState = $order->update_order_state(intval($transactionInfos['id']), 3);
               $updateOrderWithdrawAmount = $order->update_decrypted(intval($transactionInfos['id']),'resultedReceivingAmount',$withdrawAmount);
               $assignWithdraw = $order->update_decrypted(intval($transactionInfos['id']),'withdrawExplTxId',$response->id);
        } else {
            $swapReturn['responseState'] = 'ERROR4';
            if($hitbtc->error->description){
                $swapReturn['response'] = $hitbtc->error->description;
            }else{
                $swapReturn['response'] = $hitbtc->error->message;
            }
        }
    }

    if($_REQUEST['action'] == 'refundAction') {
        $transactionInfos = $order->swap_by_id(intval($_REQUEST['txId']));
        if (!is_null($transactionInfos['withdrawExplTxId'])) {
            $swapReturn['responseState'] = 'ERROR2';
            $swapReturn['response'] = 'Refund already executed';
            echo json_encode($swapReturn);
            exit();
        }
        if ($transactionInfos['signedBy'] != $_SESSION['curr_user']) {
            $swapReturn['responseState'] = 'ERROR4';
            $swapReturn['response'] = 'Refund must be executed by ' . $transactionInfos['signedBy'];
            echo json_encode($swapReturn);
            exit();
        }
        $withdrawCurrBeq = $hitbtc->gather_withdraw_fees($transactionInfos['givenCoin'], $transactionInfos['depositAmount']);
        if (!$withdrawCurrBeq->error) {
            $withdrawalFee = floatval($withdrawCurrBeq);
        } else {
            $swapReturn['responseState'] = 'ERROR';
            $swapReturn['response'] = 'Cant declare withdraw fee reason: ' . $withdrawalFee->message;
            echo json_encode($swapReturn);
            exit();
        }
        $giveCoinInfos = $cryptoCoins->show_coins_by_name($transactionInfos['givenCoin']);
        $finalRefund = 0;
        switch ($_REQUEST['refundType']){
            case 'full':
                $finalRefund = $transactionInfos['depositAmount'];
                break;
            case 'partialNetwork':
                $finalRefund = $transactionInfos['depositAmount'] - $withdrawalFee;
                break;
            case 'partialCustom':
                $customWithdrawAmount = $_REQUEST['customRefundAmount'];
                $finalRefund = $customWithdrawAmount;
                break;
            case 'kycDeny':
                $finalRefund = $transactionInfos['depositAmount'] / 1.05;
                break;
            default:
                break;
        }
        $finalRefund = number_format($finalRefund, $giveCoinInfos['allowedDecimals'], '.', '');
        $hitbtc->move_balance_for_refund_if_needed($transactionInfos);
        sleep(0.2);
        if ($transactionInfos['destinationTagPhrase'] == 'is not required') {
            $response = $hitbtc->addWithdrawal($transactionInfos['givenCoin'], $finalRefund, $transactionInfos['clientReceiveAddress'], $encryption_class->safeEncrypt(($transactionInfos['id'])));
        } else {
            $response = $hitbtc->addWithdrawal($transactionInfos['givenCoin'], $finalRefund, $transactionInfos['clientReceiveAddress'], $encryption_class->safeEncrypt(($transactionInfos['id'])), $transactionInfos['destinationTagPhrase']);
        }
        if ($response) {
            $swapReturn['responseState'] = 'OK';
            $swapReturn['response'] = $response->id;
            $changeOrderState = $order->update_order_state(intval($transactionInfos['id']), 5);
            $assignWithdraw = $order->update_decrypted(intval($transactionInfos['id']),'withdrawExplTxId',$response->id);
        } else {
            $swapReturn['responseState'] = 'ERROR4';
            if($hitbtc->error->description){
                $swapReturn['response'] = $hitbtc->error->description;
            }else{
                $swapReturn['response'] = $hitbtc->error->message;
            }
        }
    }


    echo json_encode($swapReturn);
}

?>
