<?php
$admins = $user->all_users();
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$adminDetails = $user->is_admin(USER);
$cmsUsersDetailes = $user->is_cms_user(USER);
?>

<div id="aud"></div>
<div style="text-align: right;width: 80%;">
    <a class="navbar-minimalize btn buttonMaxim" href="#">
        <i class="fa fa-bars" aria-hidden="true"></i>
    </a>
</div>

<nav class="navbar-default navbar-static-side" role="navigation" style="position: fixed;padding-right: 5px;border-right: 1px solid #2d2d2d;background-size: 140%;background-position: right;height: 100%;background-color: #1d1d1d;">
    <div style="text-align: right;width: 80%;">
        <a class="navbar-minimalize btn buttonMinim" href="#" style="border-bottom-left-radius: 0px;">
            <i class="fa fa-bars" aria-hidden="true"></i>
        </a>
    </div>

    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header" style="padding-left: 5px;">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="/images/admph/small-<?=$cmsUsersDetailes['image']?>" style="height: 35px;float: left;width: 35px;margin-top: 23px;">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#" style="float: right;font-size: 11px;margin-top: 20px;">
                        <span class="block m-t-xs font-bold"><?=$_SESSION['curr_user']?></span>
                        <span class="text-muted text-xs block"><?=$cmsUsersDetailes['nickname']?></span>
                    </a>
                    <span id="activeUserRole" style="display:none;"><?=$cmsUsersDetailes['role']?></span>
                </div>
                <div class="logo-element">
                    <img alt="image" class="rounded-circle" src="/images/admph/<?=$cmsUsersDetailes['image']?>" style="width: 60px;margin-top: 25px;">
                </div>
            </li>
            <li style="border-bottom: 1px solid #2d2d2d;margin-bottom: 12px;width:100%;margin-top: 5.5px;">
                <div class="setings-item" style="padding: 10px;font-size: 10px;width: 90px;float: right;">
                    <div class="switch">
                        <div class="onoffswitch">
                            <input type="checkbox" name="collapsemenu" class="onoffswitch-checkbox" id="hideBalances">
                            <label class="onoffswitch-label" for="hideBalances">
                                <span class="onoffswitch-inner"></span>
                                <span class="onoffswitch-switch"></span>
                            </label>
                        </div>
                    </div>
                    <div>Hide Balances</div>
                </div>
                <a href="login?action=logout" style="float:left;padding: 10px;color: red;text-align: center;background: none;width: 70px;border-color: red;font-size: 12px;">
                    <i class="customFAUpMenu fa fa-power-off" aria-hidden="true"></i><span style="display:block;">Log out</span>
                </a>
            </li>
            <li <?php if ($pagename == 'Overview'){ echo 'class="active"';} ?> >
                <a href="index"><i class="customFA fa fa-th-large"></i> <span class="nav-label">Overview</span></a>
            </li>
            <li  <?php if ($pagename == 'Haircuts'){ echo 'class="active"';} ?>>
                <a href="bulkPreview?short=haircuts&page=1&type=appointments"><i class="customFA fa fa-scissors" aria-hidden="true"></i><span class="nav-label">Haircuts</span></a>
            </li>
            <li <?php if ($pagename == 'Clients'){ echo 'class="active"';} ?>>
                <a href="#"><i class="customFA fa fa-address-book"></i> <span class="nav-label">Clients</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li><a href="#" onclick="add_client('modals','add')" style="color: #a9ff7d;height: 40px;padding-top: 10px;"><span style="padding-top: 2px;" class="fa plus-times"></span>Add new Client</a></li>
                    <li <?php if ($pagename == 'Clients' || $pagename == 'Client'){ echo 'class="active"';} ?>><a href="bulkPreview?short=clients&page=1">Clients</a></li>
                </ul>
            </li>
            <li <?php if ($pagename == 'Barbers'){ echo 'class="active"';} ?>>
                <a href="#"><i class="customFA fa fa-users"></i> <span class="nav-label">Barbers</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li <?php if ($pagename == 'Add New Barber'){ echo 'class="active"';} ?>><a href="addBarber" style="color: #a9ff7d;height: 40px;padding-top: 10px;"><span style="padding-top: 2px;" class="fa plus-times"></span>Add Barber</a></li>
                    <li <?php if ($pagename == 'Barbers' || $pagename == 'Barber'){ echo 'class="active"';} ?>><a href="bulkPreview?short=barbers&page=1">Barbers</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><i class="customFA fa fa-sign-language"></i> <span class="nav-label">Services</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li <?php if ($pagename == 'Add New Service'){ echo 'class="active"';} ?>><a href="#" style="text-decoration: line-through;color: #a9ff7d;height: 40px;padding-top: 10px;"><span style="padding-top: 2px;" class="fa plus-times"></span>Add Service</a></li>
                    <li <?php if ($pagename == 'Services' || $pagename == 'Service'){ echo 'class="active"';} ?>><a style="text-decoration: line-through" href="#">Services</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><i class="customFA fa fa-shopping-bag"></i> <span class="nav-label">E-shop</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li <?php if ($pagename == 'Add New Item'){ echo 'class="active"';} ?>><a href="#" style="text-decoration: line-through;color: #a9ff7d;height: 40px;padding-top: 10px;"><span style="padding-top: 2px;" class="fa plus-times"></span>Add item</a></li>
                    <li <?php if ($pagename == 'Items' || $pagename == 'Item'){ echo 'class="active"';} ?>><a style="text-decoration: line-through" href="#">Active Items</a></li>
                </ul>
            </li>
            <li>
                <a href="qrcode"><i class="customFA fa fa-qrcode" aria-hidden="true"></i><span class="nav-label">QR Code</span></a>
            </li>
            <li <?php if ($pagename == 'Discounts System' || $pagename == 'Account Settings' || $pagename == 'Business Hours' || $pagename == 'Smslog'){ echo 'class="active"';} ?>>
                <a href="#"><i class="customFA fa fa-gears"></i> <span class="nav-label">Settings</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li <?php if ($pagename == 'Discounts System'){ echo 'class="active"';} ?>><a href="discountsSystem">Discounts System</a></li>
                    <li <?php if ($pagename == 'Account Settings'){ echo 'class="active"';} ?>><a href="settings-admin">Manage Your Account</a></li>
                    <li <?php if ($pagename == 'Business Hours'){ echo 'class="active"';} ?>><a href="businessHours">Business Hours</a></li>
                    <li <?php if ($pagename == 'Smslog'){ echo 'class="active"';} ?>><a href="bulkPreview?short=smslog&page=1">SMS Log</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>


<div id="page-wrapper" class="gray-bg">
    <div class="generalShadow" id="generalShadowActive"></div>
    <div class="row menuRow">
        <div class="search-custom-container">
            <input type="text" style="font-size:18px;width:100%;height:40px;background-color: #1d1d1d;-webkit-appearance: none;" placeholder="Type here to search clients by phone number" name="top-search" id="top-search" autocomplete="off">
            <label class="float-left label customButton" id="takisButton" style="display:none;width: 70px;height: 70px;background-image: url('images/botIcon.png');background-size: 50px 50px;background-repeat: no-repeat;background-position: center center;background-color: rgba(255, 255, 255, 0) !important;padding-top: 70px;padding-left: 0px;">BOT TAKIS</label>
            <div id="topSearchResults" class="topSearchResults"></div>
        </div>
        <div style="display: flex;margin-top: auto;margin-bottom: auto;width: 10%;padding-left: 30px;">
            <ul class="nav navbar-top-links navbar-right customNavbar" style="position: absolute;right: 10px;top: 20px;">
                <li class="dropdown">
                    <a class="dropdown-toggle count-info" data-toggle="dropdown" href="#">
                        <i class="customFAUpMenu fa fa-bell"></i> <span style="height: auto;min-width: auto;" class="label label-primary notifNumTransactions">0</span>
                    </a>
                    <ul class="dropdown-menu custom-drop-drop dropdown-menu-transactions dropdown-aletrs-transactions dropdown-alerts" style="background-color: #2d2d2d;color: white;max-height: 450px;overflow: auto;box-shadow: rgb(12 134 86) 0px 0px 10px;"></ul>
                </li>
            </ul>
        </div>
    </div>


    <link href="css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <script src="js/plugins/toastr/toastr.min.js"></script>
