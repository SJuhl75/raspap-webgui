<?php

require_once 'includes/status_messages.php';
require_once 'config.php';

/**
 * Manage VPN configuration
 */
function DisplayModemConfig()
{
    $tmpnwc = '/tmp/modem.con';
    $status = new StatusMessages();

    exec('mmcli -L|sed -n "s/.*\([0-9]\) .*/\1/p"',$M); $MN=$M[0]; 
    $avail=strlen($MN)?1:0;

    if (isset($_POST['RestartModemManager'])) {
            $status->addMessage('Attempting to restart ModemManager, please reload in a few seconds', 'info');
            exec('sudo service ModemManager restart', $return);
            foreach ($return as $line) { 
            if (!empty($line)) { $logoutput=$logoutput . $line . "\r\n"; } }
    }

    if ($avail) { // wenn ein Modem da sein sollte ...
    foreach (array_keys($_POST) as $post) {
        if (preg_match('/delete(\d+)/', $post, $post_match)) {       
        exec("mmcli -m " . $MN . " --messaging-delete-sms=" . $post_match[1],$Ret);
        }
    }
    // you have to create /etc/polkit-1/localauthority/50-local.d/ModemManager.pkla
    // [ModemManager]
    // Identity=unix-user:*
    // Action=org.freedesktop.ModemManager1.*
    // ResultAny=yes
    // ResultActive=yes
    // ResultInactive=yes

    exec("mmcli -m " . $MN . "|sed -n 's/.*manufacturer: //p;s/.*model: //p;s/.*revision: /(/p'| xargs", $ModemInfo);
    $modem=$ModemInfo[0] . ')';
    exec("mmcli -m " . $MN . "|sed -n 's/.*imei: //p;s/.*  operator name: //p'", $ModemInfo2);
    $imei=$ModemInfo2[0]; $provider=$ModemInfo2[1];

    exec("mmcli -m " . $MN . "|sed -n 's/.*own: //p;s/.*  state: //p;s/.*quality://p'|sed 's/\x1b\[[0-9;]*m//g'", $ModemInfo3);
    $phonenr=$ModemInfo3[0]; 
    exec("mmcli -m " . $MN . "|sed -n 's/.*tech: //p'|sed 's/\x1b\[[0-9;]*m//g'", $MI3);
    
    $constat=ucfirst($ModemInfo3[1]) . (empty($MI3)?'':' to ' . strtoupper($MI3[0]));
    $signal=floatval($ModemInfo3[2]);
    if ($signal > 25 ) { $signal_status = "success"; }
    else { $signal_status = "warning"; }

    exec("mmcli -m " . $MN . "|sed -n 's/.*tech: //p'|sed 's/\x1b\[[0-9;]*m//g'", $ModemInfo3);

    $messages = array(); exec("mmcli -m " . $MN . ' --messaging-list-sms|sed -n "s/.*SMS.\([0-9]*\).*/\1/p"',$ModemInfo4);
    foreach ($ModemInfo4 as $l => $val) { 
        $e="mmcli -m " . $MN . " -s " . $val . "|sed -n 's/.*timestamp: //p;s/.*number: //p;s/.*text: //p'"; // $status->addMessage('Running ' . $e, 'info');
        exec($e, $MI5); $timestamp=substr($MI5[2],4,2) . '.' . substr($MI5[2],2,2) . '.' . substr($MI5[2],0,2) . ' ' . substr($MI5[2],6,2) . ':' . substr($MI5[2],8,2);
        array_push($messages, array('id'=>$val,'sender'=>$MI5[0],'content'=>$MI5[1],'timestamp'=>$timestamp ));
        unset($MI5);
    }

    exec('wget https://ipinfo.io/ip -qO -', $return);
    $public_ip = $return[0];
    
    if (!RASPI_MONITOR_ENABLED) {
        $logoutput = "";
        if (isset($_POST['SaveModemConfig'])) {
            if (isset($_POST['authUser'])) {
                $authUser = strip_tags(trim($_POST['authUser']));
            }
            if (isset($_POST['authPassword'])) {
                $authPassword = strip_tags(trim($_POST['authPassword']));
            }
            if (isset($_POST['dialin'])) {
                $dialin = strip_tags(trim($_POST['dialin']));
            }
            if (isset($_POST['APN'])) {
                $APN = strip_tags(trim($_POST['APN']));
            }
            $return = SaveModemConfig($status, $authUser, $authPassword, $dialin, $APN);
        } elseif (isset($_POST['StartVPNC'])) {
            $status->addMessage('Attempting to start VPN', 'info');
            exec('sudo /usr/sbin/vpnc-connect --debug 2', $return);
            foreach ($return as $line) { 
            if (!empty($line)) { $logoutput=$logoutput . $line . "\r\n"; } }
        } elseif (isset($_POST['StopVPNC'])) {
            $status->addMessage('Attempting to stop VPN', 'info');
            exec('sudo /usr/sbin/vpnc-disconnect', $return);
            foreach ($return as $line) { 
            if (!empty($line)) { $logoutput=$logoutput . $line . "\r\n"; } }
        }
    }
    } // gehoert zur avail-if
    
    exec('pidof pppd | wc -l', $pppdstatus);
    $serviceStatus = $pppdstatus[0] == 0 ? "down" : "up";

    system("sudo cp " . RASPI_NW_LTE_CONFIG . " $tmpnwc ", $return);
    if ($return ==0) {
        system("sudo chmod o+r " . $tmpnwc);
    } else {
        $status->addMessage('Unable to get modem configuration', 'danger');
    }
    $auth = file($tmpnwc,FILE_IGNORE_NEW_LINES); 

    // parse client auth credentials
    if (!empty($auth)) {
        $array = array('dialin'=>"number=",'APN'=>"apn=",'authUser'=>"username=",'authPassword'=>"password=");
        foreach ($auth as $i => $value) { 
            foreach ($array as $j => $value) {
                if (strpos(strtoupper(trim($auth[$i])),strtoupper($array[$j]))!==FALSE) {
                    $pos=strpos(strtoupper(trim($auth[$i])),strtoupper($array[$j]));
                    if ($pos>0) break;     // echo $j . '=' . trim(substr(trim($auth[$i]), strlen($array[$j]))) . "\r\n" ;
                    ${$j}=trim(substr(trim($auth[$i]), strlen($array[$j])));
                }
             }
        }
    }

    echo renderTemplate(
        "modem", compact(
            "status","avail",
            "serviceStatus",
            "pppdstatus",
            "modem","imei","provider","phonenr","constat","signal","signal_status","messages","public_ip",
            "authUser",
            "authPassword",
            "dialin","APN","logoutput"
        )
    );
}

/**
 * Validates uploaded .ovpn file, adds auth-user-pass and
 * stores auth credentials in login.conf. Copies files from
 * tmp to OpenVPN
 *
 * @param  object $status
 * @param  object $file
 * @param  string $authUser
 * @param  string $authPassword
 * @return object $status
 */
function SaveModemConfig($status, $authUser, $authPassword, $dialin, $APN)
{
    $auth_flag = 0;
    $tmpnwc = '/tmp/modem.con';

    try {
        $array = array('dialin'=>"number=",'APN'=>"apn=",'authUser'=>"username=",'authPassword'=>"password=");
        system("sudo cp " . RASPI_NW_LTE_CONFIG . " $tmpnwc ", $return);
        if ($return ==0) {
            system("sudo chmod o+r " . $tmpnwc);
            system("sudo chmod a+w " . $tmpnwc);
        } else {
            $status->addMessage('Unable to get modem configuration', 'danger');
        }
        $auth = file($tmpnwc); 

        // Update of VPN configuration settings by environment variabeles        
        foreach ($auth as $i => $value) { 
            foreach ($array as $j => $value) {
                if (strpos(strtoupper(trim($auth[$i])),strtoupper($array[$j]))!==FALSE) {
                    $pos=strpos(strtoupper(trim($auth[$i])),strtoupper($array[$j]));
                    if ($pos>0) break; // $status->addMessage('Update of ' . $j . "=" .${$j}, 'danger');
                    $auth[$i]=$array[$j] . ${$j} . "\r\n";
                    unset($array[$j]);
                } 
            } 
        }
        foreach ($array as $j => $value) array_push($auth, $array[$j] . '=' . ${$j} . "\r\n");
        file_put_contents($tmpnwc, $auth);

        // Set iptables rules and, optionally, auth-user-pass
        // exec("sudo /etc/raspap/openvpn/configauth.sh $tmp_ovpnclient $auth_flag " .RASPI_WIFI_CLIENT_INTERFACE, $return);
        // foreach ($return as $line) {
           // $status->addMessage($line, 'info');
        //}

        // Copy tmp client config to /etc/openvpn/client
        system("sudo cp $tmpnwc " . RASPI_NW_LTE_CONFIG, $return);
        if ($return ==0) {
            $status->addMessage('Network Manager configuration updated successfully', 'info');
            system("sudo service NetworkManager restart");
        } else {
            $status->addMessage('Unable to update configuration', 'danger');
        }
        return $status;
    } catch (RuntimeException $e) {
        $status->addMessage($e->getMessage(), 'danger');
        return $status;
    }
}
