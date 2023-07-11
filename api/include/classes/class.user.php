<?php
class User{
	var $error = '';
	var $msg = '';
	var $key = 'SYMBIOTIC';
	public $sdb;
	
	public function __construct(){
	global $db;
	 $this->sdb = $db;
	}
	public function gather_admins_nick_image_mail(){
        $stmt = "SELECT nickname,email,image FROM  sym_users WHERE active = 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            $export = [];
            foreach ($result as $res =>$value){
                $export[$result[$res]['email']] = $result[$res];
            }
            return $export;
        }
        return false;

    }

	public function gather_admin_by_id($adminId){
        $stmt = "SELECT * FROM  sym_users WHERE id=:id";
        $this->sdb->query($stmt);
        $this->sdb->bind(':id',$adminId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function gather_admin_by_id_no_sensitive($adminId){
        $stmt = "SELECT email,nickname,id,image FROM  sym_users WHERE id=:id";
        $this->sdb->query($stmt);
        $this->sdb->bind(':id',$adminId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function gather_admin_no_sensitive(){
        $stmt = "SELECT email,nickname,id,image FROM  sym_users WHERE 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }

    public function admin_list(){
        $stmt = "SELECT *  FROM  " . PFX . "users WHERE active = 1 AND role <= 2";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }

    public function admin_stamp($link,$pagename,$user){

	   // if ($pagename != 'Dashboard') {
            $stmt = "INSERT INTO sym_users_log (`user`, `stamp`, `webLink`) VALUES (:user, :pagename,:link)";
            $this->sdb->query($stmt);
            $this->sdb->bind(":user", $user);
            $this->sdb->bind(":pagename", $pagename);
            $this->sdb->bind(":link", $link);
            $add = $this->sdb->execute();
            if ($add) {
                return true;
            }
        //}
        return false;

    }

    public function gather_banned_ips_Active(){
        $stmt = "SELECT * FROM  sym_ban_ip WHERE active = 1 ORDER BY id";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }

    public function gather_banned_ips(){
        $stmt = "SELECT * FROM  sym_ban_ip WHERE 1 ORDER BY id";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }

    public function writeBan($ip,$way,$reason,$user,$ipId = null)
    {
        if ($way == 'ban'){
            $stmt = "INSERT INTO sym_ban_ip (`bannedFrom`, `reason`, `ip`) VALUES (:user, :reason,:ip)";
            $this->sdb->query($stmt);
            $this->sdb->bind(":reason", $reason);
            $this->sdb->bind(":user", $user);
            $this->sdb->bind(":ip", $ip);
            $add = $this->sdb->execute();
            $message = 'You have banned ip: '.$ip. ' with reason: '.$reason;
        }

        if ($way == 'unban'){
            $stmt = "UPDATE sym_ban_ip SET `active` = 0 WHERE id =:id";
            $this->sdb->query($stmt);
            $this->sdb->bind(":id", $ipId);
            $update = $this->sdb->execute();
            $message = 'You have unbanned ip: ';
        }

        if ($way == 'reban'){
            $stmt = "UPDATE sym_ban_ip SET `active` = 1 WHERE id =:id";
            $this->sdb->query($stmt);
            $this->sdb->bind(":id", $ipId);
            $update = $this->sdb->execute();
            $message = 'You have rebanned ip: ';
        }

        if ($add || $update) {
            $bannedIps = $this->gather_banned_ips_Active();
            $tz = 'Europe/Athens';
            $timestamp = time();
            $dt = new DateTime("now", new DateTimeZone($tz));
            $dt->setTimestamp($timestamp);
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><xml/>');
            $xml = new DOMDocument();
            $mystore = $xml->createElement('BannedIps');
            foreach ($bannedIps as $ip => $value) {
                $loc = $xml->createElement('ip');
                $loc->nodeValue = $bannedIps[$ip]['ip'];
                $mystore->appendChild($loc);
            }
            $xml->appendChild($mystore);
            $xml->formatOutput = true;
            $xml->save("../sym-website-app-endpoints/bannedIps.xml");

            return $message;
        }
    }

    public function gatherLastLogs(){
        $stmt = "SELECT * FROM  sym_users_log WHERE 1 ORDER BY id DESC LIMIT 10";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
                return $result;
        }
        return false;
    }

    public function gatherAllLogs(){
        $stmt = "SELECT * FROM  sym_users_log WHERE 1 ORDER BY id DESC LIMIT 10";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }

    public function check_admin_now_state(){
        $adminUsers = $this->all_users();
        $logfile = $this->gatherAllLogs();
        foreach ($adminUsers as $admusr =>$value){
        $newLogForUser = array();
            foreach ($logfile as $log =>$value2){
                if ($adminUsers[$admusr]['email'] == $logfile[$log]['user']){
                   array_push($newLogForUser,$logfile[$log]);
                }
            }
            if ($newLogForUser != ''){
                usort($newLogForUser, "sortFunction");
                $this->update_lastMove($adminUsers[$admusr]['email'], $newLogForUser[0]['dateTimeStamp']);
            }
        }
        return true;
    }

    private function sortFunction( $a, $b ) {
        return strtotime($a["date"]) - strtotime($b["date"]);
    }

    public function is_cms_user($email){
        $email = trim($email);

        $stmt = "SELECT * FROM  " . PFX . "users WHERE active = 1 AND email = :email";
        $this->sdb->query($stmt);
        $this->sdb->bind(":email",$email);
        $result = $this->sdb->single();
        if($result){
                return $result;
        }
        return false;
    }
		
    public function is_admin($email){
	$email = trim($email);
	
		$stmt = "SELECT * FROM  " . PFX . "users WHERE active = 1 AND email = :email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$result = $this->sdb->single();
		if($result){
            if($result['role'] <= 2){
                return $result;
            }
		}
		return false;
	}

    public function selectedTheme($email){
        $email = trim($email);

        $stmt = "SELECT * FROM  " . PFX . "users WHERE active = 1 AND email = :email";
        $this->sdb->query($stmt);
        $this->sdb->bind(":email",$email);
        $result = $this->sdb->single();
        if($result){
            if($result['selectedTheme'] != ''){
                return true;
            }
        }
        return false;
    }

    public function update_theme($email)
    {
        $email = trim($email);
        $stmt = "SELECT * FROM  " . PFX . "users WHERE active = 1 AND email = :email";
        $this->sdb->query($stmt);
        $this->sdb->bind(":email", $email);
        $result = $this->sdb->single();
        if ($result) {
            if ($result['selectedTheme'] == '') {
                $stmt2 = "UPDATE " . PFX . "users  SET `selectedTheme` =:theme WHERE email =:email";
                $this->sdb->query($stmt2);
                $this->sdb->bind(":theme", 'theme1');
                $this->sdb->bind(":email", $email);
                $update = $this->sdb->execute();
                if ($update) {
                    return 'theme activated';
                }
            } else {
                $stmt2 = "UPDATE " . PFX . "users  SET `selectedTheme` =:theme WHERE email =:email";
                $this->sdb->query($stmt2);
                $this->sdb->bind(":theme", '');
                $this->sdb->bind(":email", $email);
                $update = $this->sdb->execute();
                if ($update) {
                    return 'theme deactivated';
                }
            }
        }
        return false;
    }

    public function get_pass($email){
		global $encryption;
	
		$email = trim($email);
		$stmt = "SELECT * FROM  " . PFX . "users WHERE email = :email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$result = $this->sdb->single();
		if($result){
		$password = $encryption->decrypt($result['password']);
		
		return $password;	
		
		}
}

	public function get_role($email){
		$email = trim($email);
		$stmt = "SELECT role FROM  " . PFX . "users WHERE email = :email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$result = $this->sdb->single();
		if($result){
		
		return $result['role'];	
		
		}
}

    public function get_last_login($email){
		$email = trim($email);
		$stmt = "SELECT * FROM  " . PFX . "users WHERE email = :email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$result = $this->sdb->single();
		if($result){
		
		return $result['last_login'];	
		
		}
		}

    public function is_active($email){
		$email = trim($email);
		$stmt = "SELECT * FROM  " . PFX . "users WHERE email = :email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$result = $this->sdb->single();
		if($result && $result['active'] == '1'){
		return true;
		}
		$this->error = "User is inactive";
		return false;
	}
	
    public function is_user($email){
		$email = trim($email);
		$stmt = "SELECT * FROM  " . PFX . "users WHERE email = :email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$result = $this->sdb->single();
		if($result){
		return $result;
		}
		$this->error = "User doesn't exists";
		return false;
	}

    public function admin_online(){
        $stmt = "SELECT * FROM  " . PFX . "users WHERE role = 1 AND isLogged = 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return true;
        }
        return false;
    }

    public function is_userById($id){
        global $crypt;
        $id = $crypt->decrypt($id);
        $stmt = "SELECT * FROM  " . PFX . "customers WHERE id = :id";
        $this->sdb->query($stmt);
        $this->sdb->bind(":id",$id);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        $this->error = "User doesn't exists";
        return false;
    }

    public function is_customer_by_email($email){
        $stmt = "SELECT * FROM  " . PFX . "customers WHERE email = :email";
        $this->sdb->query($stmt);
        $this->sdb->bind(":email",$email);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function is_customer_by_phone($phone){
        $stmt = "SELECT * FROM  " . PFX . "customers WHERE phone = :phone";
        $this->sdb->query($stmt);
        $this->sdb->bind(":phone",$phone);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function update_password($email,$password){
		$email = trim($email);
		global $encryption;
		$password = $encryption->encrypt($password);
		$stmt = "UPDATE " . PFX . "users  SET `password` = :password WHERE email =:email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$this->sdb->bind(":password",$password);
		$update = $this->sdb->execute();
		if($update){
		$this->msg = "User updated successfully";
			return true;
			}
		$this->error = "Error updating password";
		return false;
	}

    public function update_role($email,$role){
		$email = trim($email);
	if($email != $_SESSION['curr_user']){
		$stmt = "UPDATE " . PFX . "users  SET `role` = :role WHERE email =:email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$this->sdb->bind(":role",$role);
		$update = $this->sdb->execute();
	if($update){
		$this->msg = "User updated successfully";
		return true;
	}
	$this->error = "An error occurred while updating database";
	return false;
	}
	
	return true;
}

    public function update_status($email,$status){
		$email = trim($email);
	if($email != $_SESSION['curr_user']){
			$stmt = "UPDATE " . PFX . "users  SET `active` = :status WHERE email =:email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$this->sdb->bind(":status",$status);
		$update = $this->sdb->execute();
	if($update){
		$this->msg = "User updated successfully";
		return true;
	}
	}

	return true;
}

    public function update_lastMove($email,$move){
        $email = trim($email);
            $stmt = "UPDATE " . PFX . "users  SET `lastMove` = :move WHERE email =:email";
            $this->sdb->query($stmt);
            $this->sdb->bind(":email",$email);
            $this->sdb->bind(":move",$move);
            $update = $this->sdb->execute();
            if($update){
                $this->msg = "User updated successfully";
                return true;
            }

        return true;
    }

    public function update_image($email,$image){
		$email = trim($email);
			$stmt = "UPDATE " . PFX . "users  SET `image` = :image WHERE email =:email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$this->sdb->bind(":image",$image);
		$update = $this->sdb->execute();
	if($update){
		$this->msg = "image updated successfully";
		return true;
	}


	return true;
}

    public function update_email($email,$new){
	$email = trim($email);
	if($email != $_SESSION['curr_user'] && !$this->is_user($new)){
		$stmt = "UPDATE " . PFX . "users  SET `email` = :new WHERE email =:email";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$this->sdb->bind(":new",$new);
		$update = $this->sdb->execute();
	if($update){
		$this->msg = "User updated successfully";
		$this->error ='';
		return true;
	}
	}
	$this->error ='Email id already in use';
	return false;
}

    public function add_user($email,$password,$role,$adminImage){
		$email = trim($email);
		$email = strtolower($email);
		//$password = base64_encode($password);
		global $encryption;
		$password = $encryption->encrypt($password);
if(empty($email) || empty($password)){
			$this->error = 'Please input Email id and Password';
			return false;
		}
	if(!$this->is_user($email)){
	$stmt = "INSERT INTO " . PFX . "users (`id`, `email`, `password`, `role`, `last_login`, `active`, `image`) VALUES (NULL, :email , :password , :role , 'Never', '1',:image)";
		$this->sdb->query($stmt);
		$this->sdb->bind(":email",$email);
		$this->sdb->bind(":password",$password);
		$this->sdb->bind(":role",$role);
		$this->sdb->bind(":image",$adminImage);
		$add = $this->sdb->execute();
	if($add){
		$this->error = "";
		$this->msg = "User added successfully";
		return true;
		}
	}
	$this->error = "User already exists";
	return false;
}

    public function all_users(){
		$stmt = "SELECT * FROM  " . PFX . "users ORDER BY lastMove DESC";
		$this->sdb->query($stmt);
		return $this->sdb->resultset();
}

    public function new_fixes(){
        $stmt = "SELECT * FROM fixes WHERE 1 AND seen != 'yes' ORDER BY id DESC";
        $this->sdb->query($stmt);
        return $this->sdb->resultset();
    }

    public function gather_admin_by_id_for_edit(){
        global $encryption;
        $settings = new Settings();
        $stmt = "SELECT * FROM  sym_users WHERE id = :id";
        $this->sdb->query($stmt);
        $this->sdb->bind(':id',USERID);
        $result = $this->sdb->single();
        if ($result) {
            $password2 = $encryption->decrypt($result['password']);
            $hidedpass = '';
            for ($i=0; $i <= strlen($password2);$i++){ $hidedpass .= '•'; }
            $result['password'] = $hidedpass;
            //$result['userAccessLevel'] = $settings->gather_access_level_by_id($result['userAccessLevelId']);
            return $result;
        }
        return false;
    }

    public function viewadps(){
        global $encryption;
        $stmt = "SELECT * FROM  sym_users WHERE id = :id";
        $this->sdb->query($stmt);
        $this->sdb->bind(':id',USERID);
        $result = $this->sdb->single();
        if ($result) {
            $password2 = $encryption->decrypt($result['password']);
            return $password2;
        }
        return false;
    }

    public function changeadmpw($pass){
        global $encryption;
        $pass = $encryption->encrypt($pass);
        $stmt = "UPDATE sym_users SET password=:password WHERE id = :id";
        $this->sdb->query($stmt);
        $this->sdb->bind(':id',USERID);
        $this->sdb->bind(':password',$pass);
        $result = $this->sdb->execute();
        if ($result) {
            return true;
        }
        return false;
    }

    public function updtAdmInf($image,$nickname){
        if(empty($nickname)){
            $this->error = 'You must input nickname';
            return false;
        }
        if (empty($image)){
            $stmt = "UPDATE sym_users SET nickname=:nickname WHERE id = :id";
            $this->sdb->query($stmt);
        }else{
            $stmt = "UPDATE sym_users SET image=:image, nickname=:nickname WHERE id = :id";
            $this->sdb->query($stmt);
            $this->sdb->bind(':image',$image);
        }
        $this->sdb->bind(':id',USERID);
        $this->sdb->bind(':nickname',$nickname);
        $result = $this->sdb->execute();
        if ($result) {
            return 'Account Updated Successfully';
        }
        return false;
    }

    public function is_admin_subscribed(){
        $stmt = "SELECT * FROM  sym_admin_subscribers WHERE userId=:id";
        $this->sdb->query($stmt);
        $this->sdb->bind(':id',USERID);
        $result = $this->sdb->single();
        if($result){
            return true;
        }
        return false;
    }












    /**
     * New functions
     */

    public function countCustomers(){
        $stmt ="SELECT * FROM sym_customers WHERE active = 1 ";
        $this->sdb->query($stmt);
        $this->sdb->resultset();
        $resultsCounted = $this->sdb->rowCount();
        if($resultsCounted){
            return $resultsCounted;
        }
        return false;
    }

    public function show_customers(){
        $stmt = "SELECT * FROM  sym_customers WHERE active = 1";
        $this->sdb->query($stmt);
        $result = $this->sdb->resultset();
        if($result){
            return $result;
        }
        return false;
    }


    public function show_customer_by_id($customerId){
        $stmt = "SELECT * FROM  sym_customers WHERE id=:customerId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":customerId", $customerId);
        $result = $this->sdb->single();
        if($result){
            return $result;
        }
        return false;
    }

    public function add_customer($name,$surname,$phone,$note,$email,$referer){
        $name = trim($name);
        $surname = trim($surname);
        $email = trim($email);
        $referer = trim($referer);
        $stmt = "INSERT INTO sym_customers (`name`, `surname`, `phone`, `email`, `note`,`referrer`) VALUES (:name,:surname,:phone, :email,:note,:referer)";
        $this->sdb->query($stmt);
        $this->sdb->bind(":name", $name);
        $this->sdb->bind(":surname", $surname);
        $this->sdb->bind(":phone", $phone);
        $this->sdb->bind(":email", $email);
        $this->sdb->bind(":note", $note);
        $this->sdb->bind(":referer", $referer);
        $add = $this->sdb->execute();
        $customerId = $this->sdb->lastInsertId();
        if ($add) {
            return $this->show_customer_by_id($customerId);
        }
        return false;
    }

    public function update_customer_email($customerId,$email){
        $stmt = "UPDATE sym_customers  SET `email`=:email WHERE `id` =:customerId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":email", $email);
        $this->sdb->bind(":customerId", $customerId);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }

    public function update_customer($customerId,$name,$surname,$phone,$note,$email,$referer){
        $stmt = "UPDATE sym_customers  SET `name`=:name, `surname`=:surname, `phone`=:phone, `email`=:email, `note`=:note, `referrer`=:referer WHERE `id` =:customerId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":name", $name);
        $this->sdb->bind(":surname", $surname);
        $this->sdb->bind(":phone", $phone);
        $this->sdb->bind(":email", $email);
        $this->sdb->bind(":note", $note);
        $this->sdb->bind(":referer", $referer);
        $this->sdb->bind(":customerId", $customerId);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }

    public function gather_clients_pagination($limit,$offset){
        $stmt ="SELECT * FROM sym_customers WHERE active = 1  ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $this->sdb->query($stmt);
        $results = $this->sdb->resultset();
        if($results ){
            return $results;
        }
        return false;
    }

    public function deactive_customer($customerId){
        $stmt = "UPDATE sym_customers  SET active = 0 WHERE `id` =:customerId";
        $this->sdb->query($stmt);
        $this->sdb->bind(":customerId", $customerId);
        $update = $this->sdb->execute();
        if ($update) {
            return true;
        }
        return false;
    }

    public function show_customer_by_haircut_id($haircutId){
        $haircuts = new haircuts();
        $haircutInfos = $haircuts->show_haircut_by_id($haircutId);
        return $haircutInfos['customerId'];
    }
}

?>