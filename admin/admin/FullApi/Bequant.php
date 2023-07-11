<?php require_once('../include/admin-load.php');
$txDepositAddressAndPayId = $order->gather_order_deposit_wallet_by_txId($_REQUEST['transactionId']);
$bequant->gather_sub_account_creds_by_wallet($txDepositAddressAndPayId);

if($_SESSION['BQCredsUsedForThisTx']['username'] == 'akontos@blocktech.gr'){

}else{
   // $gatherAccountBalance = $bequant->gather_sub_account_balance_by_session();
   // $settings->arrayVarDump($gatherAccountBalance);exit();

}
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet">
    <script src="../js/jquery-3.1.1.min.js"></script>
    <script type="text/javascript" src="../js/symbiotic.js"></script>
    <link href="../css/animate.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <meta name="mobile-web-app-capable" content="yes">
    <link href="../css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">
    <link href="../css/plugins/c3/c3.min.css" rel="stylesheet">
    <link href="../css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="../css/plugins/toastr/toastr.min.css" rel="stylesheet">

    <style>
        .trCustomClass:hover{
            background-color: #ffffff82;
            color:black;
            cursor:pointer;
        }
        .customSwallClass{
            position: absolute;
            top: 300px;
            background-color: #000000!important;
            border: 2px solid #00976d!important;
            box-shadow:none;
            background-image:none;
        }
        #toast-container{
            position:absolute;
            top:0px;
            right:0px;
            width:100%;
            height:60px!important;
            padding:10px;

        }

        .trCustomAlreadyUsed{
            background-color: #00000057!important;
            color: #ffffff82!important;
            cursor:not-allowed;
        }

        .animTran{
            -webkit-animation: usedForThisTx 3s infinite alternate;
            -moz-animation: usedForThisTx 3s infinite alternate;
            -ms-animation: usedForThisTx 3s infinite alternate;
            -o-animation: usedForThisTx 3s infinite alternate;
            animation: usedForThisTx 3s infinite alternate;
        }

        @keyframes usedForThisTx {
            from {box-shadow: inset 0px 0px 0px 0px black;}
            to {box-shadow: inset 0px 0px 40px 0px black;}

        }
        @keyframes usedForThisTx {
            from {box-shadow: inset 0px 0px 40px 0px black;}
            to  {box-shadow: inset 0px 0px 0px 0px black;}
        }

        .extraInfoOnExchangerDropdown{
            position: absolute;
            width:210px;
        }
        @media (max-width: 768px) {
            .extraInfoOnExchangerDropdown{
                position: absolute;
                width: 95%;
                margin-top: 35px;
            }
            .extraInfoOnExchangerDropdownFloat{
                float: left!important;
                margin-left: 10px;
                font-size: 10px;
            }
        }
        .border-bottom{
            border-bottom:10px solid #2d2d2d!important;
            border-bottom-left-radius:10px;
            border-bottom-right-radius:10px;

        }

        .customRowBalanceSubAccount{
            background-color: #2d2d2d;
            border-radius: 10px;
            height: 50px;
            padding: 5.5px;
        }
        @media (max-width: 768px) {
            .customRowBalanceSubAccount {
                height: 80px !important;
            }
        }
    </style>
</head>
<body style="background:none!important;overflow:hidden;">
<div id="floatingButtonsSection" style="display:none;">
    <div class="row">
        <div class="col-sm-6">
            <button id="placeMarketOrderButton" onclick="placeOrder('market')" class="btn btn-success btn-lg">PLACE ORDER WITH MARKET PRICE</button>
        </div>
        <div class="col-sm-6">
            <button id="placeLimitOrderButton" onclick="placeOrder('limit')" class="btn btn-warning btn-lg">PLACE ORDER WITH LIMIT PRICE</button>
        </div>
        <div class="col-sm-6">
            <button  id="placeWithdraw" class="btn btn-info btn-lg" onclick="withdraw('extraBTN')">WITHDRAW</button>
            <button  id="placeRefund" class="btn btn-info btn-lg" onclick="refund()">REFUND</button>
        </div>
    </div>
</div>

<div class="col-lg-12" style="margin-bottom: 10px;">
    <?php if($_SESSION['BQCredsUsedForThisTx']['username'] == 'akontos@blocktech.gr'){?>
        <div class="row customRowBalanceSubAccount">
            <div class="col-sm-8" style="text-align: left"><span >Handler Head Account: <i style="color:#f8ba11"><?=$_SESSION['BQCredsUsedForThisTx']['username']?></i></span></div>
            <div class="col-sm-4" style="text-align: right"><div class="row" id="balanceOnWithdrawCoin">
                    <div class="sk-spinner sk-spinner-wave">
                        <div class="sk-rect1"></div>
                        <div class="sk-rect2"></div>
                        <div class="sk-rect3"></div>
                        <div class="sk-rect4"></div>
                        <div class="sk-rect5"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php }else{ ?>
        <div class="row customRowBalanceSubAccount">
            <div class="col-sm-8" style="text-align: left; padding-top: 10px;"><span >Handler Sub-Account: <i style="color:#f8ba11"><?=$_SESSION['BQCredsUsedForThisTx']['username']?></i></span></div>
            <div class="col-sm-4" style="text-align: right;    padding-top: 5px;">
                <div class="row" id="balanceOnWithdrawCoin">
                    <div class="sk-spinner sk-spinner-wave">
                        <div class="sk-rect1"></div>
                        <div class="sk-rect2"></div>
                        <div class="sk-rect3"></div>
                        <div class="sk-rect4"></div>
                        <div class="sk-rect5"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php }  ?>
</div>
<div class="ibox">
    <div class="ibox-title" style="padding:10px;">
        <div class="extraInfoOnExchangerDropdown">
            <div class="extraInfoOnExchangerDropdownFloat" style="height: 20px;float: left;"><div style="float:left;width:10px;height:10px;background-color:#1cc30f"></div><span style="float:left;margin-top: -4px;margin-left: 4px;">100% match</span></div>
            <div class="extraInfoOnExchangerDropdownFloat" style="height: 20px;float: right;"><div style="float:left;width: 10px;height:10px;background-color:#acc306"></div><span style="float:left;margin-top: -4px;margin-left: 4px;">+-5% Diff</span></div>

            <div class="extraInfoOnExchangerDropdownFloat" style="height: 20px;float: left;"><div style="float:left;width:10px;height:10px;background-color:#ef6a38c9"></div><span style="float:left;margin-top: -4px;margin-left: 4px;">Selected</span></div>
            <div class="extraInfoOnExchangerDropdownFloat" style="width:105px;height: 20px;float: right;"><div style="float:left;width: 10px;height:10px;background-color:#00000057"></div><span style="float:left;margin-top: -4px;margin-left: 4px;">In Use</span></div>
        </div>
        <h3 style="color: #f9bb11;text-align: center;"><?=$_REQUEST['giveCoin']?> DEPOSIT HISTORY</h3>
        <div class="ibox-tools">
            <a class="collapse-link collapse-custom depositHistory">
                <i class="fa fa-chevron-down" style="font-size: 25px;"></i>
            </a>
            <a id="depositHistoryRef" class="refresh-link">
                <i class="fa fa-refresh" style="font-size: 25px;"></i>
            </a>
        </div>
    </div>
    <div class="ibox-content">
        <div class="row" id="depositHistory"> </div>
    </div>
</div>
<div class="ibox border-bottom">
    <div class="ibox-title" style="padding:10px;">
        <h3 style="color: #f9bb11;text-align: center;"><?=$_REQUEST['getCoin']?> WITHDRAW HISTORY</h3>
        <div class="ibox-tools">
            <a id="loadWithdrawHistory" class="collapse-link collapse-custom">
                <i class="fa fa-chevron-up" style="font-size: 25px;"></i>
            </a>
            <a id="withdrawHistoryRef" class="refresh-link">
                <i class="fa fa-refresh" style="font-size: 25px;"></i>
            </a>
        </div>
    </div>
    <div class="ibox-content" style="display:none;">
        <div class="row" id="withdrawHistory">
            <div class="sk-spinner sk-spinner-wave">
                <div class="sk-rect1"></div>
                <div class="sk-rect2"></div>
                <div class="sk-rect3"></div>
                <div class="sk-rect4"></div>
                <div class="sk-rect5"></div>
            </div>
        </div>
    </div>
</div>
<div class="ibox border-bottom">
    <div class="ibox-title" style="padding:10px;">
        <h3 style="color: #f9bb11;text-align: center;">ACTIVE ORDERS</h3>
        <div class="ibox-tools">
            <a id="loadActiveOrders" class="collapse-link collapse-custom">
                <i class="fa fa-chevron-up" style="font-size: 25px;"></i>
            </a>
            <a id="activeOrdersRef" class="refresh-link">
                <i class="fa fa-refresh" style="font-size: 25px;"></i>
            </a>
        </div>
    </div>
    <div class="ibox-content" style="display:none;">
        <div class="row" id="activeOrders">
            <div class="sk-spinner sk-spinner-wave">
                <div class="sk-rect1"></div>
                <div class="sk-rect2"></div>
                <div class="sk-rect3"></div>
                <div class="sk-rect4"></div>
                <div class="sk-rect5"></div>
            </div>
        </div>
    </div>
</div>
<div class="ibox border-bottom">
    <div class="ibox-title" style="padding:10px;">
        <h3 style="color: #f9bb11;text-align: center;">ORDERS HISTORY for (<?=$_REQUEST['giveCoin']?> / <?=$_REQUEST['getCoin']?>)</h3>
        <a href="#" style="color:rgb(2, 192, 118)">BUY ORDERS </a>|
        <a href="#" style="color: rgb(248, 73, 96)"> SELL ORDERS</a>
        <div class="ibox-tools">
            <a id="loadOrdersHistory" class="collapse-link collapse-custom">
                <i class="fa fa-chevron-up" style="font-size: 25px;"></i>
            </a>
            <a id="ordersHistoryRef" class="refresh-link">
                <i class="fa fa-refresh" style="font-size: 25px;"></i>
            </a>
        </div>
    </div>
    <div class="ibox-content" style="display:none">
        <div class="row" id="ordersHistory">
            <div class="sk-spinner sk-spinner-wave">
                <div class="sk-rect1"></div>
                <div class="sk-rect2"></div>
                <div class="sk-rect3"></div>
                <div class="sk-rect4"></div>
                <div class="sk-rect5"></div>
            </div>
        </div>
    </div>
</div>
<div class="ibox border-bottom">
    <div class="ibox-title" style="padding:10px;">
        <h3 id="balancesScroll" style="color: #f9bb11;text-align: center;">BALANCES</h3>
        <div class="ibox-tools">
            <a id="loadBalances" class="collapse-link collapse-custom">
                <i class="fa fa-chevron-up" style="font-size: 25px;"></i>
            </a>
            <a id="balancesRef" class="refresh-link">
                <i class="fa fa-refresh" style="font-size: 25px;"></i>
            </a>
        </div>
    </div>
    <div class="ibox-content" style="display:none">
        <div class="row" id="balances" style="overflow-y: auto;max-height: 300px;">
            <div class="sk-spinner sk-spinner-wave">
                <div class="sk-rect1"></div>
                <div class="sk-rect2"></div>
                <div class="sk-rect3"></div>
                <div class="sk-rect4"></div>
                <div class="sk-rect5"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bd-example-modal-lg" id="explorerModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="    z-index: 999999!important;">
    <div class="modal-dialog modal-lg" style="max-width: 100%;margin-top: 0px;">
        <div class="modal-content">
            <div>
                <div id="explorer-modal-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</body>

<script src="../js/popper.min.js"></script>
<script src="../js/bootstrap.js"></script>
<script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../js/inspinia.js"></script>
<script src="../js/plugins/pace/pace.min.js"></script>
<script src="../js/plugins/video/responsible-video.js"></script>
<script src="../js/plugins/toastr/toastr.min.js"></script>
<script src="../js/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../js/plugins/touchpunch/jquery.ui.touch-punch.min.js"></script>
<script src="../js/plugins/sweetalert/sweetalert.min.js"></script>
<script src="../js/plugins/slick/slick.min.js"></script>
<script src="../js/plugins/footable/footable.all.min.js"></script>
<script src="../js/plugins/sweetalert/sweetalert.min.js"></script>

<?php include 'JS/bequantJS.php' ?>
