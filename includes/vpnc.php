<?php

require_once 'includes/status_messages.php';
require_once 'config.php';

/**
 * Manage VPN configuration
 */
function DisplayVPNConfig()
{
    $tmp_vpnclient = '/tmp/vpn.conf';
    $status = new StatusMessages();
    
    if (!RASPI_MONITOR_ENABLED) {
        $logoutput = "";
        if (isset($_POST['SaveVPNCSettings'])) {
            if (isset($_POST['authUser'])) {
                $authUser = strip_tags(trim($_POST['authUser']));
            }
            if (isset($_POST['authPassword'])) {
                $authPassword = strip_tags(trim($_POST['authPassword']));
            }
            if (isset($_POST['VPNGateway'])) {
                $VPNGateway = strip_tags(trim($_POST['VPNGateway']));
            }
            if (isset($_POST['IPSUser'])) {
                $IPSUser = strip_tags(trim($_POST['IPSUser']));
            }
            if (isset($_POST['IPSPSK'])) {
                $IPSPSK = strip_tags(trim($_POST['IPSPSK']));
            }
            $return = SaveVPNConfig($status, $authUser, $authPassword, $VPNGateway, $IPSUser, $IPSPSK);
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

    exec('pidof vpnc | wc -l', $vpncstatus);
    exec('wget https://ipinfo.io/ip -qO -', $return);
    $public_ip = $return[0];

    $serviceStatus = $vpncstatus[0] == 0 ? "down" : "up";
    
    system("sudo cp " . RASPI_VPNC_CLIENT_CONFIG . " $tmp_vpnclient ", $return);
    if ($return ==0) {
        // $status->addMessage('VPN configuration copied successfully', 'info');
        system("sudo chmod o+r " . $tmp_vpnclient);
    } else {
        $status->addMessage('Unable to copy VPN configuration', 'danger');
    }
    $auth = file($tmp_vpnclient,FILE_IGNORE_NEW_LINES); 

    // parse client auth credentials
    if (!empty($auth)) {
        $array = array('VPNGateway'=>"IPSec gateway",'IPSUser'=>"IPSec ID",'IPSPSK'=>"IPSec secret",'authUser'=>"Xauth username",'authPassword'=>"Xauth password");
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
        "vpnc", compact(
            "status",
            "serviceStatus",
            "vpncstatus",
            "public_ip",
            "authUser",
            "authPassword",
            "VPNGateway","IPSUser","IPSPSK","logoutput"
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
function SaveVPNConfig($status, $authUser, $authPassword, $VPNGateway, $IPSUser, $IPSPSK)
{
    $tmp_vpnclient = '/tmp/vpn.conf';
    $tmp_authdata = '/tmp/authdata';
    $auth_flag = 0;

    try {
        $array = array('VPNGateway'=>"IPSec gateway",'IPSUser'=>"IPSec ID",'IPSPSK'=>"IPSec secret",'authUser'=>"Xauth username",'authPassword'=>"Xauth password");
        // define('RASPI_VPNC_CLIENT_CONFIG', '/etc/vpnc/default.conf');
        system("sudo cp " . RASPI_VPNC_CLIENT_CONFIG . " $tmp_vpnclient ", $return);
        if ($return ==0) {
            system("sudo chmod o+r " . $tmp_vpnclient);
            system("sudo chmod a+w " . $tmp_vpnclient);
        } else {
            $status->addMessage('Unable to copy VPN configuration', 'danger');
        }
        $auth = file($tmp_vpnclient); // file("/etc/vpnc/default.conf");//$vpnconf=file(RASPI_VPNC_CLIENT_CONFIG, FILE_IGNORE_NEW_LINES);

        // Update of VPN configuration settings by environment variabeles        
        foreach ($auth as $i => $value) { 
            foreach ($array as $j => $value) {
                if (strpos(strtoupper(trim($auth[$i])),strtoupper($array[$j]))!==FALSE) {
                    $pos=strpos(strtoupper(trim($auth[$i])),strtoupper($array[$j]));
                    if ($pos>0) break;
                    $auth[$i]=$array[$j] . ' ' . ${$j} . "\r\n";
                    unset($array[$j]);
                } 
            } 
        }
        foreach ($array as $j => $value) array_push($auth, $array[$j] . '=' . ${$j} . "\r\n");
        file_put_contents($tmp_vpnclient, $auth);

        // Set iptables rules and, optionally, auth-user-pass
        // exec("sudo /etc/raspap/openvpn/configauth.sh $tmp_ovpnclient $auth_flag " .RASPI_WIFI_CLIENT_INTERFACE, $return);
        // foreach ($return as $line) {
           // $status->addMessage($line, 'info');
        //}

        // Copy tmp client config to /etc/openvpn/client
        system("sudo cp $tmp_vpnclient " . RASPI_VPNC_CLIENT_CONFIG, $return);
        if ($return ==0) {
            $status->addMessage('VPN configuration updated successfully', 'info');
        } else {
            $status->addMessage('Unable to update VPN configuration', 'danger');
        }
        return $status;
    } catch (RuntimeException $e) {
        $status->addMessage($e->getMessage(), 'danger');
        return $status;
    }
}
