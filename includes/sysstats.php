<?php

require_once 'app/lib/system.php';

$system = new System();

$hostname = $system->hostname();
$uptime   = $system->uptime();
$cores    = $system->processorCount();

// mem used
$memused  = $system->usedMemory();
$memused_status = "primary";
if ($memused > 90) {
    $memused_status = "danger";
    $memused_led = "service-status-down";
} elseif ($memused > 75) {
    $memused_status = "warning";
    $memused_led = "service-status-warn";
} elseif ($memused >  0) {
    $memused_status = "success";
    $memused_led = "service-status-up";
}

// cpu load
$cpuload = $system->systemLoadPercentage();
if ($cpuload > 90) {
    $cpuload_status = "danger";
} elseif ($cpuload > 75) {
    $cpuload_status = "warning";
} elseif ($cpuload >=  0) {
    $cpuload_status = "success";
}

// cpu temp
$cputemp = $system->systemTemperature();
if ($cputemp > 70) {
    $cputemp_status = "danger";
    $cputemp_led = "service-status-down";
} elseif ($cputemp > 50) {
    $cputemp_status = "warning";
    $cputemp_led = "service-status-warn";
} else {
    $cputemp_status = "success";
    $cputemp_led = "service-status-up";
}

// Battery capacity
exec('cat /sys/bus/platform/drivers/axp20x-battery-power-supply/axp20x-battery-power-supply/power_supply/axp20x-battery/capacity', $bc);
$batcap=(int)$bc[0];
if ($batcap < 10) { $batcap_status = "warning"; $batcap_led = "service-status-warn"; }
else { $batcap_status = "success"; $batcap_led = "service-status-up"; }
exec('cat /sys/bus/platform/drivers/axp20x-battery-power-supply/axp20x-battery-power-supply/power_supply/axp20x-battery/status', $bs);
$pwrstate=$bs[0];
if ( $pwrstate == "Discharging" ) {
    exec('cat /sys/bus/platform/drivers/axp20x-battery-power-supply/axp20x-battery-power-supply/power_supply/axp20x-battery/current_now', $bd);
    $mr=round(4000*1000*60/(float)$bd[0],1); 
    $pwrstate=$pwrstate . " (" . $mr . " Minutes remaining)";
}

// Uplink status
exec('pidof pppd | wc -l', $pppdstat);
$ServiceStat = $pppdstat[0] == 0 ? "down" : "up";
if ($ServiceStat == "up" ) { $pppd_status = "active"; $pppd_led = "service-status-up"; }
else { $pppd_status = "inactive"; $pppd_led = "service-status-down"; }

// hostapd status
$hostapd = $system->hostapdStatus();
if ($hostapd[0] ==1) {
    $hostapd_status = "active";
    $hostapd_led = "service-status-up";
} else {
    $hostapd_status = "inactive";
    $hostapd_led = "service-status-down";
}

// gpsd status
exec('pidof str2str | wc -l', $strstat);
$ServiceStat = $strstat[0] == 0 ? "down" : "up";
if ($ServiceStat == "up" ) { $str_status = "active"; $str_led = "service-status-up"; }
else { $str_status = "inactiv"; $str_led = "service-status-down"; }

