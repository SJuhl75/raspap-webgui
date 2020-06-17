<?php
// Use a PSR-4 autoloader for the `proj4php` root namespace.
include("proj4php/vendor/autoload.php");

require_once 'includes/status_messages.php';
require_once 'config.php';
require_once 'includes/wgsecef.php';

use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;

// Initialise Proj4
$proj4 = new Proj4php();
$projWGS84  = new Proj('EPSG:4326', $proj4);
$projUTM  = new Proj('EPSG:25832', $proj4);
$projITRF  = new Proj('EPSG:4326', $proj4); // not working=4385,4896,4919

// Create your projection
//$proj4->addDef("EPSG:27700",'+proj=tmerc +lat_0=49 +lon_0=-2 +k=0.9996012717 +x_0=400000 +y_0=-100000 +ellps=airy +datum=OSGB36 +units=m +no_defs');
//$proj4->addDef("BRD","+proj=longlat +datum=WGS84 +no_defs +nadgrids=/var/www/html/grid/BWTA2017.gsb,./grid/BETA2007.gsb,null +to +proj=utm +ellps=GRS80 +zone=32 +nadgrids=@null -vvv -f %.3f");
//$projBU = new Proj('BRD', $proj4);
$projBU = new Proj("+proj=longlat +datum=WGS84 +no_defs +nadgrids=./grid/BWTA2017.gsb,./grid/BETA2007.gsb,null +to +proj=utm +ellps=GRS80 +zone=32 +nadgrids=@null -f %.1f", $proj4);

// Kurzform ...? $result = $proj4->transform($projWGS84, $projUTM33N, new proj4phpPoint($long, $lat));

/**
 * Manage GNSS configuration
 */

/*  Converts WGS84 coordinates into the corresponding Maidenhead Locator
    Inputs: $latitude, $longitude					  */
function maidenhead (float $latitude, float $longitude) {
  if ($longitude >= 180 || $longitude <= -180)	{ return "Longitude Value Incorrect"; }
  if ($latitude >= 90 || $latitude <= -90) 	{ return "Latitude Value Incorrect";  }

  $longitude += 180;
  $latitude += 90;

  $letterA = ord('A');
  $numberZero = ord('0');

  $locator = chr($letterA + intval($longitude / 20));
  $locator .= chr($letterA + intval($latitude / 10));
  $locator .= chr($numberZero + intval(($longitude % 20) / 2));
  $locator .= chr($numberZero + intval($latitude % 10));
  $locator .= chr($letterA + intval(($longitude - intval($longitude / 2) * 2) / (2 / 24)));
  $locator .= chr($letterA + intval(($latitude - intval($latitude / 1) * 1 ) / (1 / 24)));

  return $locator;
}

function strfl ($str) {
    $pos = strrpos($str = strtr(trim(strval($str)), ',', '.'), '.');
    return ($pos===false ? floatval($str) : floatval(str_replace('.', '', substr($str, 0, $pos)) . substr($str, $pos))); }

function DisplayGPS()
{
    global $loca, $lat, $lon, $epv;
    global $proj4, $projWGS84, $projUTM, $projBU;
//$projBU = new Proj('BRD', $proj4);

    $status = new StatusMessages();

    // RTKLib Stream Server running?
    exec('pidof str2str | wc -l', $strstatus);
    $STRStat = $strstatus[0] == 0 ? "down" : "up";

    // Snippets taken from gpsd.php.in
    $rtksock = @fsockopen('localhost',5005, $rerrno, $rerrstr, 2);
    //for($tries = 0; $tries < 10; $tries++) { $resp = @fread($sock, 10000); }
    $rtkresp = @fread($rtksock, 10000); @fclose($rtksock);

/* array(10) {
x  ["ECEFZ"]=> string(14) "  4795041.6335"
  ["UTMR"]=>  float(484669.26826104)
x  ["ECEFX"]=> string(14) "  4138451.4323"
x  ["ECEFY"]=> string(14) "   640012.0515"
  ["UTMH"]=>  float(5433911.755698)
  ["UTMZ"]=>  string(3) "32U"
  ["R95"]=>   float(0.03617286)
  ["PDOP"]=>  float(0.0003)
  ["TS"]=>    string(19) "2020-06-12 17:37:52"
  ["ALT"]=>   float(246.12629555704) }

RTK-FIX: ECEF X = 4138451,431±0,000m Y = 640012,051±0,000m Z = 4795041,627±0,000m
ETRS89/UTM = 484742,669m / 5434027,704m / 246,120m
ITRF = 484742,669m / 5434027,704m / 246,120m
R95% = 0,0723cm ( 6 sats used)
Baseline = 28.281,327m

2020-06-12 18:59:36|0.0003-0.0003|6-6|100.0%|32U|484669.267|5433911.752|246.120|
 */

    $RTKJSON = file_get_contents("rtkpos.json");
    $RTKV = json_decode($RTKJSON, true);
    //$xecef=$RTKV['ECEFX']; $yecef=$RTKV['ECEFY'];  $zecef=$RTKV['ECEFZ'];

    if (!empty($rtkresp)) {
        $rtkdata = explode(";", $rtkresp);
        // foreach ($rtkdata as $rtkkey => $rtkvalue) { $status->addMessage("RTKdata[" . $rtkkey . "]=" . $rtkvalue, 'info'); }
        //%  GPST           x-ecef(m)      y-ecef(m)      z-ecef(m)   Q  ns   sdx(m)   sdy(m)   sdz(m)  sdxy(m)  sdyz(m)  sdzx(m) age(s)  ratio
        //2106 601309   4138451.7897    640011.7954   4795039.4922   2   5   3.7878   1.7982   3.4559   0.7144   0.7729   3.0019   0.99    0.0
        $xecef=$rtkdata[1]; $yecef=$rtkdata[2]; $zecef=$rtkdata[3];
        $rtkfix = array_search($rtkdata[4], array('RTK-FIX' => 1, 'FLOAT' => 2, 'DGPS' => 4, 'Single' => 5));
        $rsvcnt=$rtkdata[5];
        $sdx=2*$rtkdata[6]; $sdy=2*$rtkdata[7]; $sdz=2*$rtkdata[8]; $sdxy=$rtkdata[9];	// 2 Sigma = 95,45% 
        // Calculation of R95 according to Novatel APN-029 GPS Position Accuracy Measures
        $R95=2.0789*(62*$sdy+56*$sdx);	// report centimeters
        $wga=ecef2wga(array('x'=>$xecef,'y'=>$yecef,'z'=>$zecef));
        $rtklat=$wga['lat']; $rtklon=$wga['lon'];
//        $pointSrc = new Point($rtklon, $rtklat, $wga['alt'], $projWGS84);
//        error_log("Source: " . $pointSrc->toShortString() . " in WGS84 <br>" . PHP_EOL,0);
        // Transform the point between datums.
//        $UTMpoint = $proj4->transform($projUTM, $pointSrc);
//        $BUpoint  = $proj4->transform($projBU, $pointSrc);
//        $ITRFpoint = $proj4->transform($projITRF, $pointSrc);
        // error_log("DEBUG=" . $pointDest->toShortString());
//        $UTM=$UTMpoint->toArray(); 
//        $ITRF=$BUpoint->toArray();
//        $utme=str_replace(",",".",$UTM[0]); $utmn=str_replace(",",".",$UTM[1]); $utma=str_replace(",",".",$UTM[2]); 
        $utme=$RTKV['UTMR']; $utmn=$RTKV['UTMH']; $utma=$wga['alt'];
        $utmz=$RTKV['UTMZ']; $utmdat=$RTKV['TS']; 
    } else { $rtkfix='down'; }

    $sock = @fsockopen(GPSDSERVER, GPSDPORT, $errno, $errstr, 2);
    @fwrite($sock, "?WATCH={\"enable\":true}\n");
    usleep(1000);
    @fwrite($sock, "?POLL;\n");
    usleep(1000);
    for($tries = 0; $tries < 10; $tries++){
        $resp = @fread($sock, 10000); # SKY can be pretty big
        if (preg_match('/{"class":"POLL".+}/i', $resp, $m)){
            $resp = $m[0]; break; }
        }
        @fclose($sock);
        //$status->addMessage(GPSDSERVER . ":" . GPSDPORT . " resp=" . $resp, 'info');
        // if (!$resp) $resp = '{"class":"ERROR","message":"no response from GPS daemon"}';

        $GPS = json_decode($resp, true);
        if ($GPS['class'] != 'POLL'){
            $status->addMessage("json_decode error: resp=" . $resp, 'info');
            die("json_decode error: $resp");
        }

        // Extract GPS meta data        
        if (!array_key_exists('sky', $GPS))                  { $GPS['sky'] = array();       	       }
        if (!array_key_exists(0, $GPS['sky']))               { $GPS['sky'][0] = array();    	       }
        if (!array_key_exists('satellites', $GPS['sky'][0])) { $GPS['sky'][0]['satellites'] = array(); }
        
        if (!array_key_exists('tpv', $GPS)) 		     { $GPS['tpv'] = array(); 		       }
        if (!array_key_exists(0, $GPS['tpv'])) 		     { $GPS['tpv'][0] = array(); 	       }
        if (!array_key_exists('lat', $GPS['tpv'][0]) ||
            !array_key_exists('lon', $GPS['tpv'][0]))        { $GPS['tpv'][0]['lat']  = 0.0;
                                                               $GPS['tpv'][0]['lon']  = 0.0; 	       }
        if (!array_key_exists('mode', $GPS['tpv'][0]))       { $GPS['tpv'][0]['mode'] = 0;  	       }
        if (!array_key_exists('time', $GPS['tpv'][0]))       { $GPS['tpv'][0]['time'] = 0;  	       }

        $lat   = (float)$GPS['tpv'][0]['lat'];
        $lon   = (float)$GPS['tpv'][0]['lon'];

        $alt   = (float)$GPS['tpv'][0]['alt'];
        $epv   = (float)$GPS['tpv'][0]['epv']*2; // U-Blox returns 1-sigma = 68% ... to be comparable expand by f=2
        $hdop  = (float)$GPS['sky'][0]['hdop'];
        $pdop  = (float)$GPS['sky'][0]['pdop'];
        // $status->addMessage("PDOP=" . $pdop . " HDOP=" . $hdop, 'info');
        
        $fix = $GPS['tpv'][0];
        $sky = $GPS['sky'][0];
        $sats = $sky['satellites'];
        $fixtype = array('Unknown' => 0, 'No Fix' => 1, '2D Fix' => 2, '3D Fix' => 3);
        $type = array_search($fix['mode'], $fixtype);
        $svcnt = count($sats);
        $qth = maidenhead(strfl("{$lat}"),strfl("{$lon}"));
        $rtklattat = $rtkstatus[0] == 0 ? "down" : "up";

        // $status->addMessage("lat=" . $lat . " lon=" . $lon . " alt=" . $alt . " hdop=" . $hdop . " sv=" . $svcnt . " qth=" . $qth , 'info');
        $loca=array('BASE'=>array("name"=>"Continuously Operating Mobile Reference Station@" . $qth,
                          "url"=>"http://sjuhl.de",
                          "lat"=>$lat,
                          "lng"=>$lon));
        if ($rtkfix!='down') { $loca['RTK'] = array("name"=>"RTK Solution\r\n" . $rtklat . " " . $rtklon,
                          "url"=>"http://sjuhl.de",
                          "lat"=>$rtklat,
                          "lng"=>$rtklon); }
        $loca['EUREF']=array("name"=>"KARL00DEU (EUREF Class A station)",
                          "url"=>"http://epncb.oma.be/_networkdata/siteinfo4onestation.php?station=KARL00DEU",
                          "lat"=>"49.01124241",
                          "lng"=>"8.41125530");
        if ($rtkfix!='down') { $baseline=vincenty($loca['BASE']['lat'],$loca['BASE']['lng'],$loca['EUREF']['lat'],$loca['EUREF']['lng']); }

        // NOTE Load Custom GPS Locations
        foreach (json_decode(file_get_contents("customgps.json"), true) as $entry) { array_push($loca,$entry); }

    $baseline=vincenty($loca['BASE']['lat'],$loca['BASE']['lng'],$loca['EUREF']['lat'],$loca['EUREF']['lng']);

    if (!RASPI_MONITOR_ENABLED) {
        if (isset($_POST['SaveOpenVPNSettings'])) {
            if (isset($_POST['authUser'])) {
                $authUser = strip_tags(trim($_POST['authUser']));
            }
            if (isset($_POST['authPassword'])) {
                $authPassword = strip_tags(trim($_POST['authPassword']));
            }
            $return = SaveOpenVPNConfig($status, $_FILES['customFile'], $authUser, $authPassword);
        } elseif (isset($_POST['StartMeas'])) {
            $rtksock = @fsockopen('localhost',5005, $rerrno, $rerrstr, 2);
            do {
                $rtkresp = @fread($rtksock, 10000);
                error_log("DEBUG=" . $rtkresp . PHP_EOL,0); 
                if (!empty($rtkresp)) {
                    $rtkdata = explode(";", $rtkresp);
                    // foreach ($rtkdata as $rtkkey => $rtkvalue) { $status->addMessage("RTKdata[" . $rtkkey . "]=" . $rtkvalue, 'info'); }
                    //%  GPST           x-ecef(m)      y-ecef(m)      z-ecef(m)   Q  ns   sdx(m)   sdy(m)   sdz(m)  sdxy(m)  sdyz(m)  sdzx(m) age(s)  ratio
                    //2106 601309   4138451.7897    640011.7954   4795039.4922   2   5   3.7878   1.7982   3.4559   0.7144   0.7729   3.0019   0.99    0.0
                    $xecef=$rtkdata[1]; $yecef=$rtkdata[2]; $zecef=$rtkdata[3];
                    $rtkfix = array_search($rtkdata[4], array('RTK-FIX' => 1, 'FLOAT' => 2, 'DGPS' => 4, 'Single' => 5));
                    $rsvcnt=$rtkdata[5];
                    $sdx=2*$rtkdata[6]; $sdy=2*$rtkdata[7]; $sdz=2*$rtkdata[8]; $sdxy=$rtkdata[9];	// 2 Sigma = 95,45% 
                    // Calculation of R95 according to Novatel APN-029 GPS Position Accuracy Measures
                    $R95=2.0789*(62*$sdy+56*$sdx);	// report centimeters
                    error_log("R95=" . $R95 . PHP_EOL,0);
                } 
            } while ($R95 > 1);	// So lange, bis kleiner als 1cm erreicht ...
            $out=array(); $i=0; $arr=array(); do {
                sleep(1);
                $rtkresp = @fread($rtksock, 10000); 
                if (!empty($rtkresp)) {
                    $rtkdata = explode(";", $rtkresp);
                    $xecef=$rtkdata[1]; $yecef=$rtkdata[2]; $zecef=$rtkdata[3];
                    array_push($arr, array('x'=>$xecef,'y'=>$yecef,'z'=>$zecef));
                    array_push($out, $xecef . " " . $yecef . " " . $zecef . "\r\n");
                    $i=$i+1;
            } } while ($i < 30);
            @fclose($rtksock);
            file_put_contents('/tmp/rawgeo.out', $out);
            exec('cs2cs +proj=geocent +datum=WGS84 +no_defs +nadgrids=@./grid/BWTA2017.gsb,@./grid/BETA2007.gsb,null +to +proj=utm +ellps=GRS80 +zone=32 +nadgrids=@null -f %.4f < /tmp/rawgeo.out > /tmp/raw.out', $XX);
            exec('awk -f mwsd.awk /tmp/raw.out',$rtko);
            foreach ($rtko as $ro) { $status->addMessage($ro, 'info'); } 
            $ITRFE=$rtko[0]; $ITRFN=$rtko[1]; $ITRFA=$rtko[2]; 

        } elseif (isset($_POST['StopOpenVPN'])) {
            $status->addMessage('Attempting to stop OpenVPN', 'info');
            exec('sudo /bin/systemctl stop openvpn-client@client', $return);
            exec('sudo /bin/systemctl disable openvpn-client@client', $return);
            foreach ($return as $line) {
                $status->addMessage($line, 'info');
            }
        }
    }

    exec('pidof rtkrcv | wc -l', $rtkstatus);

    $RTKStat = $rtkstatus[0] == 0 ? "down" : "up";

    echo renderTemplate(
        "gps", compact(
            "status",
            "serviceStatus",
            "STRStat","RTKStat",
            "authUser",
            "authPassword","lat","lon","alt","type","qth","epv","svcnt",
            "rtkfix","xecef","yecef","zecef","R95","rsvcnt","sdx","sdy","sdz","baseline",
            "utmn","utme","utma","utmz","utmdat"
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
function SaveSomething($status, $file, $authUser, $authPassword)
{
    $tmp_ovpnclient = '/tmp/ovpnclient.ovpn';
    $tmp_authdata = '/tmp/authdata';
    $auth_flag = 0;

    try {
        // If undefined or multiple files, treat as invalid
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid parameters');
        }

        // Parse returned errors
        switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('OpenVPN configuration file not sent');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit');
        default:
            throw new RuntimeException('Unknown errors');
        }

        // Validate extension
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($ext != 'ovpn') {
            throw new RuntimeException('Invalid file extension');
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($file['tmp_name']),
            array(
                'ovpn' => 'text/plain'
            ),
            true
        )
        ) {
            throw new RuntimeException('Invalid file format');
        }

        // Validate filesize
        define('KB', 1024);
        if ($file['size'] > 64*KB) {
            throw new RuntimeException('File size limit exceeded');
        }

        // Use safe filename, save to /tmp
        if (!move_uploaded_file(
            $file['tmp_name'],
            sprintf(
                '/tmp/%s.%s',
                'ovpnclient',
                $ext
            )
        )
        ) {
            throw new RuntimeException('Unable to move uploaded file');
        }
        // Good file upload, update auth credentials if present
        if (!empty($authUser) && !empty($authPassword)) {
            $auth_flag = 1;
            // Move tmp authdata to /etc/openvpn/login.conf
            $auth = $authUser .PHP_EOL . $authPassword .PHP_EOL;
            file_put_contents($tmp_authdata, $auth);
            system("sudo cp $tmp_authdata " . RASPI_OPENVPN_CLIENT_LOGIN, $return);
            if ($return !=0) {
                $status->addMessage('Unable to save client auth credentials', 'danger');
            }
        }

        // Set iptables rules and, optionally, auth-user-pass
        exec("sudo /etc/raspap/openvpn/configauth.sh $tmp_ovpnclient $auth_flag " .RASPI_WIFI_CLIENT_INTERFACE, $return);
        foreach ($return as $line) {
            $status->addMessage($line, 'info');
        }

        // Copy tmp client config to /etc/openvpn/client
        system("sudo cp $tmp_ovpnclient " . RASPI_OPENVPN_CLIENT_CONFIG, $return);
        if ($return ==0) {
            $status->addMessage('OpenVPN client.conf uploaded successfully', 'info');
        } else {
            $status->addMessage('Unable to save OpenVPN client config', 'danger');
        }

        return $status;
    } catch (RuntimeException $e) {
        $status->addMessage($e->getMessage(), 'danger');
        return $status;
    }
}
?>
