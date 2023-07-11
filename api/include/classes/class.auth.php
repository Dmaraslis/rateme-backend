<?php 

class Auth
{
    var $error = '';
    var $key = 'SYMBIOTIC';
    public $sdb;

    public function __construct()
    {
        global $db;
        $this->sdb = $db;
    }


    public function pass_manual_kyc($internalCustomerId)
    {
        $stmtn2 = "UPDATE sym_orders_for_kyc  SET `isPassed` = 1 WHERE userId =:userId";
        $this->sdb->query($stmtn2);
        $this->sdb->bind(":userId", $internalCustomerId);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }


    public function select_customer_by_internal_email($internal)
    {
        $stmt = "SELECT * FROM  sym_customers_kyc WHERE emailDec =:internal";
        $this->sdb->query($stmt);
        $this->sdb->bind(':internal', $internal);
        $products = $this->sdb->single();
        if ($products) {
            return $products;
        }
        return false;
    }


    public function gatherAllLogsCRON()
    {
        $stmt = "SELECT * FROM  sym_users_log WHERE 1 ORDER BY id DESC";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if ($result) {
            return $result;
        }
        return false;
    }

    public function clear_admin_moves()
    {
        $logfile = $this->gatherAllLogsCRON();
        date_default_timezone_set('Europe/Athens');
        $DateTime = new DateTime();
        $nowDateTime = $DateTime->format('Y-m-d H:i:s');
        $i = 0;
        if ($logfile) {
            foreach ($logfile as $log => $value) {
                $datetime2 = new DateTime($logfile[$log]['dateTimeStamp']);
                $interval = $datetime2->diff($DateTime);
                $sum = $interval->format('%Y-%m-%d %H:%i:%s');
                $explodedDays = explode('-', $sum);
                $explodedHours = explode(':', $explodedDays[2]);
                $explodedSum = explode(' ', $explodedHours[0]);

                if ($explodedSum[0] >= 7 || $explodedDays[1] > 0) {
                    $this->delete_old_move($logfile[$log]['id']);
                }
            }
            return true;
        }
    }


    public function delete_old_move($permId)
    {
        $stmt = "DELETE FROM sym_users_log WHERE id = :iddd";
        $this->sdb->query($stmt);
        $this->sdb->bind(":iddd", $permId);
        $delete = $this->sdb->execute();
        if ($delete) {
            return true;
        }
        return false;
    }


    public function is_loggedin()
    {

        if (isset($_SESSION['adminauth']) && isset($_SESSION['curr_user']) && isset($_SESSION['token'])) {
            $checksum = md5($_SESSION['curr_user'] . 'symbiotic' . date('ymd'));
            if ($checksum == $_SESSION['token']) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function is_admin()
    {
        if ($this->is_loggedin()) {
            if ($_SESSION['role'] == '1') {
                return true;
            }
            return false;
        }
        return false;
    }

    public function login($email, $password)
    {
        $settings = new Settings();
        $setting = $settings->get_all();
        global $encryption;
        $email = strtolower($email);
        $stmt = "SELECT * FROM  " . PFX . "users WHERE active = 1 AND email = :email";
        $this->sdb->query($stmt);
        $this->sdb->bind(":email", $email);
        $result = $this->sdb->single();
        if ($result) {
            $password2 = $encryption->decrypt($result['password']);
            if ($password2 === $password) {
                if ($result['role'] == 1) {
                    $_SESSION['roleName'] = 'Administrator';
                }
                if ($result['role'] == 2) {
                    $_SESSION['role'] = 'Worker';
                }
                $_SESSION['curr_user'] = $result['email'];
                $_SESSION['userId'] = $result['id'];
                $_SESSION['role'] = $result['role'];
                $_SESSION['token'] = md5($result['email'] . 'symbiotic' . date('ymd'));
                $_SESSION['adminauth'] = true;
                $_SESSION['admUserImageUrl'] = 'https://gintonic.insbot.org/images/admph/small-' . $result['image'];

                $stmt = "UPDATE " . PFX . "users  SET `last_login` = '" . $result['latest_login'] . "' WHERE email =:email";
                $stmtn = "UPDATE " . PFX . "users  SET `latest_login` = '" . date('d M Y') . "' WHERE email =:email";
                $this->sdb->query($stmt);
                $this->sdb->bind(":email", $result['email']);
                $this->sdb->execute();
                $this->sdb->query($stmtn);
                $this->sdb->bind(":email", $result['email']);
                $this->sdb->execute();

                $stmtn2 = "UPDATE " . PFX . "users  SET `isLogged` = 1 WHERE email =:email";
                $this->sdb->query($stmtn2);
                $this->sdb->bind(":email", $result['email']);
                $this->sdb->execute();
                return true;
            } else {
                $this->error = 'Wrong username/password';
                return false;
            }
        }
        $this->error = 'ERROR';
        return false;
    }

    public function logout()
    {
        $stmtn2 = "UPDATE " . PFX . "users  SET `isLogged` = 0 WHERE email =:email";
        $this->sdb->query($stmtn2);
        $this->sdb->bind(":email", USER);
        $this->sdb->execute();
        session_destroy();
        session_name('SYMBIOTIC');
        header("location:login");
        exit;
    }


    public function disconnect_user($currentUserEmail, $encryptedUserId)
    {
        $stmt = "UPDATE sym_customers SET user_token = :token, isLoggedIn = :auth WHERE email =:email AND id =:customerId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":token", 0);
        $this->sdb->bind(":auth", 0);
        $this->sdb->bind(":email", $currentUserEmail);
        $this->sdb->bind(":customerId", $encryptedUserId);
        $add = $this->sdb->execute();
        if ($add) {
            $this->msg = "User Disconnected";
            return true;
        }
        $this->error = "User Disconnect error";
        return false;
    }
}

?>