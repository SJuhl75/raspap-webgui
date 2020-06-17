<?php $xxy=77; 
    global $lat, $lon, $epv;
    $lat=51.505; $lon=-0.091; $epv=20;
    $loca=array(
    array("name"=>"Mark1","url"=>"https://en.wikipedia.org/wiki/Scotland","lat"=>"50.490671","lng"=>"-0.202646"),
    array("name"=>"Mark2","url"=>"https://en.wikipedia.org/wiki/Scotland","lat"=>"50.390671","lng"=>"-0.102646"));

/**
 * Raspbian WiFi Configuration Portal (RaspAP)
 *
 * Enables use of simple web interface rather than SSH to control wifi and hostapd on the Raspberry Pi.
 * Recommended distribution is Raspbian Buster Lite. Specific instructions to install the supported software are
 * in the README and original post by @SirLagz. For a quick run through, the packages required for the WebGUI are:
 * lighttpd (version 1.4.53 installed via apt)
 * php-cgi (version 7.3.14-1 installed via apt)
 * along with their supporting packages, php7.3 will also need to be enabled.
 *
 * @author  Lawrence Yau <sirlagz@gmail.com>
 * @author  Bill Zimmerman <billzimmerman@gmail.com>
 * @license GNU General Public License, version 3 (GPL-3.0)
 * @version 2.4
 * @link    https://github.com/billz/raspap-webgui
 * @see     http://sirlagz.net/2013/02/08/raspap-webgui/
 */

require 'includes/csrf.php';
ensureCSRFSessionToken();

require_once 'includes/config.php';
require_once 'includes/defaults.php';
require_once RASPI_CONFIG.'/raspap.php';
require_once 'includes/locale.php';
require_once 'includes/functions.php';
require_once 'includes/dashboard.php';
require_once 'includes/authenticate.php';
require_once 'includes/admin.php';
require_once 'includes/dhcp.php';
require_once 'includes/hostapd.php';
require_once 'includes/adblock.php';
require_once 'includes/system.php';
require_once 'includes/sysstats.php';
require_once 'includes/configure_client.php';
require_once 'includes/networking.php';
require_once 'includes/themes.php';
require_once 'includes/data_usage.php';
require_once 'includes/about.php';
require_once 'includes/openvpn.php';
require_once 'includes/torproxy.php';
require_once 'includes/vpnc.php';
require_once 'includes/modem.php';
require_once 'includes/gps.php';

$output = $return = 0;
$page = $_GET['page'];

if (!isset($_COOKIE['theme'])) {
    $theme = "custom.css";
} else {
    $theme = $_COOKIE['theme'];
}
$theme_url = 'app/css/'.htmlspecialchars($theme, ENT_QUOTES);

if ($_COOKIE['sidebarToggled'] == 'true' ) {
    $toggleState = "toggled";
}

// Get Bridged AP mode status
$arrHostapdConf = parse_ini_file('/etc/raspap/hostapd.ini');
// defaults to false
$bridgedEnabled = $arrHostapdConf['BridgedEnable'];

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <?php echo CSRFMetaTag() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo _("RaspAP WiFi Configuration Portal"); ?></title>

    <!-- Bootstrap Core CSS -->
    <link href="dist/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- SB-Admin-2 CSS -->
    <link href="dist/sb-admin-2/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="dist/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="dist/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
          integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
          crossorigin=""/>
          
    <!-- Custom CSS -->
    <link href="<?php echo $theme_url; ?>" title="main" rel="stylesheet">

    <link rel="shortcut icon" type="image/png" href="app/icons/favicon.png?ver=2.0">
    <link rel="apple-touch-icon" sizes="180x180" href="app/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="app/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="app/icons/favicon-16x16.png">
    <link rel="icon" type="image/png" href="app/icons/favicon.png" />
    <link rel="manifest" href="app/icons/site.webmanifest">
    <link rel="mask-icon" href="app/icons/safari-pinned-tab.svg" color="#b91d47">
    <meta name="msapplication-config" content="app/icons/browserconfig.xml">
    <meta name="msapplication-TileColor" content="#b91d47">
    <meta name="theme-color" content="#ffffff">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
      <!-- Sidebar -->
      <ul class="navbar-nav sidebar sidebar-light d-none d-md-block accordion <?php echo (isset($toggleState)) ? $toggleState : null ; ?>" id="accordionSidebar">
        <!-- Sidebar - Brand -->
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php?page=wlan0_info">
          <!-- div class="sidebar-brand-text ml-1">RaspAP</div-->
                      <img src="app/img/rtk.jpg" class="navbar-logo" width="64" height="64">

        </a>
        <!-- Divider -->
        <hr class="sidebar-divider my-0">
        <div class="row">
          <div class="col-xs ml-3 sidebar-brand-icon">
            <!-- img src="app/img/raspAP-logo.svg" class="navbar-logo" width="64" height="64"-->
          </div>
          <div class="col-xs ml-2">
            <div class="ml-1">Status</div>
            <div class="info-item-xs"><span class="icon">
              <i class="fas fa-circle <?php echo ($str_led); ?>"></i></span> <?php echo _("GNSS Service").' '. _($str_status); ?>
            </div>
            <div class="info-item-xs"><span class="icon">
              <i class="fas fa-circle <?php echo ($pppd_led); ?>"></i></span> <?php echo _("GSM Uplink").' '. _($pppd_status); ?>
            </div>
            <div class="info-item-xs"><span class="icon">
              <i class="fas fa-circle <?php echo ($hostapd_led); ?>"></i></span> <?php echo _("Hotspot").' '. _($hostapd_status); ?>
            </div>
            <div class="info-item-xs"><span class="icon">
              <i class="fas fa-circle <?php echo ($batcap_led); ?>"></i></span> <?php echo _("Battery").': '. htmlspecialchars($batcap, ENT_QUOTES); ?>%
            </div>
            <div class="info-item-xs"><span class="icon">
              <i class="fas fa-circle <?php echo ($cputemp_led); ?>"></i></span> <?php echo _("CPU Temp").': '. htmlspecialchars($cputemp, ENT_QUOTES); ?>°C
            </div>
            <div class="info-item-xs"><span class="icon">
              <i class="fas fa-circle <?php echo ($memused_led); ?>"></i></span> <?php echo _("Memory Use").': '. htmlspecialchars($memused, ENT_QUOTES); ?>%
            </div>
          </div>
        </div>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=wlan0_info"><i class="fas fa-tachometer-alt fa-fw mr-2"></i><span class="nav-label"><?php echo _("Dashboard"); ?></span></a>
        </li>
          <?php if (RASPI_MODEM_ENABLED) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=modem_conf"><i class="fas fa-broadcast-tower fa-fw mr-2"></i><span class="nav-label"><?php echo _("Modem"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_GPS_ENABLED) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=GPS"><i class="fas fa-map-marked-alt fa-fw mr-2"></i><span class="nav-label"><?php echo _("GNSS"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_HOTSPOT_ENABLED) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=hostapd_conf"><i class="far fa-dot-circle fa-fw mr-2"></i><span class="nav-label"><?php echo _("Hotspot"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_DHCP_ENABLED && !$bridgedEnabled) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=dhcpd_conf"><i class="fas fa-exchange-alt fa-fw mr-2"></i><span class="nav-label"><?php echo _("DHCP Server"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_ADBLOCK_ENABLED) : ?>
        <li class="nav-item">
           <a class="nav-link" href="index.php?page=adblock_conf"><i class="far fa-hand-paper fa-fw mr-2"></i><span class="nav-label"><?php echo _("Ad Blocking"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_NETWORK_ENABLED) : ?>
        <li class="nav-item">
           <a class="nav-link" href="index.php?page=network_conf"><i class="fas fa-network-wired fa-fw mr-2"></i><span class="nav-label"><?php echo _("Networking"); ?></a>
        </li> 
          <?php endif; ?>
          <?php if (RASPI_WIFICLIENT_ENABLED && !$bridgedEnabled) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=wpa_conf"><i class="fas fa-wifi fa-fw mr-2"></i><span class="nav-label"><?php echo _("WiFi client"); ?></span></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_OPENVPN_ENABLED) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=openvpn_conf"><i class="fas fa-key fa-fw mr-2"></i><span class="nav-label"><?php echo _("OpenVPN"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_VPNC_ENABLED) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=vpnc_conf"><i class="fas fa-key fa-fw mr-2"></i><span class="nav-label"><?php echo _("IPSec VPN"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_TORPROXY_ENABLED) : ?>
        <li class="nav-item">
           <a class="nav-link" href="index.php?page=torproxy_conf"><i class="fas fa-eye-slash fa-fw mr-2"></i><span class="nav-label"><?php echo _("TOR proxy"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_CONFAUTH_ENABLED) : ?>
        <li class="nav-item">
        <a class="nav-link" href="index.php?page=auth_conf"><i class="fas fa-user-lock fa-fw mr-2"></i><span class="nav-label"><?php echo _("Authentication"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_CHANGETHEME_ENABLED) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=theme_conf"><i class="fas fa-paint-brush fa-fw mr-2"></i><span class="nav-label"><?php echo _("Change Theme"); ?></a>
        </li>
          <?php endif; ?>
          <?php if (RASPI_VNSTAT_ENABLED) : ?>
        <li class="nav-item">
          <a class="nav-link" href="index.php?page=data_use"><i class="fas fa-chart-bar fa-fw mr-2"></i><span class="nav-label"><?php echo _("Data usage"); ?></a>
        </li>
          <?php endif; ?>
            <?php if (RASPI_SYSTEM_ENABLED) : ?>
          <li class="nav-item">
          <a class="nav-link" href="index.php?page=system_info"><i class="fas fa-cube fa-fw mr-2"></i><span class="nav-label"><?php echo _("System"); ?></a>
          </li>
            <?php endif; ?>
         <li class="nav-item">
          <a class="nav-link" href="index.php?page=about"><i class="fas fa-info-circle fa-fw mr-2"></i><span class="nav-label"><?php echo _("About RaspAP"); ?></a>
        </li>
        <!-- Divider -->
        <hr class="sidebar-divider d-none d-md-block">

        <!-- Sidebar Toggler (Sidebar) -->
        <div class="text-center d-none d-md-block">
          <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

    <!-- Main Content -->
    <div id="content">
      <!-- Topbar -->
      <nav class="navbar navbar-expand navbar-light topbar mb-1 static-top">
        <!-- Sidebar Toggle (Topbar) -->
        <div class="sidebar-brand-text ml-1">Mobile RTK Hotspot</div>
        <button id="sidebarToggleTopbar" class="btn btn-link d-md-none rounded-circle mr-3">
          <i class="fa fa-bars"></i>
        </button>
        <!-- Topbar Navbar -->
        <p class="text-left brand-title mt-3 ml-2"><?php //echo _("WiFi Configuration Portal"); ?></p>
        <ul class="navbar-nav ml-auto">
          <div class="topbar-divider d-none d-sm-block"></div>
          <!-- Nav Item - User -->
          <li class="nav-item dropdown no-arrow">
          <a class="nav-link" href="index.php?page=auth_conf">
            <span class="mr-2 d-none d-lg-inline small"><?php echo htmlspecialchars($config['admin_user'], ENT_QUOTES); ?></span>
            <i class="fas fa-user-circle fa-3x"></i>
          </a>
          </li>
        </ul>
      </nav>
      <!-- End of Topbar -->
      <!-- Begin Page Content -->
      <div class="container-fluid">
      <?php
        $extraFooterScripts = array();
        // handle page actions
        switch ($page) {
        case "wlan0_info":
            DisplayDashboard($extraFooterScripts);
            break;
        case "dhcpd_conf":
            DisplayDHCPConfig();
            break;
        case "wpa_conf":
            DisplayWPAConfig();
            break;
        case "network_conf":
            DisplayNetworkingConfig();
            break;
        case "modem_conf":
            DisplayModemConfig();
            break;
        case "hostapd_conf":
            DisplayHostAPDConfig();
            break;
        case "adblock_conf":
            DisplayAdBlockConfig();
            break;
        case "openvpn_conf":
            DisplayOpenVPNConfig();
            break;
        case "vpnc_conf":
            DisplayVPNConfig();
            break;
        case "GPS":
            DisplayGPS();
            break;
        case "torproxy_conf":
            DisplayTorProxyConfig();
            break;
        case "auth_conf":
            DisplayAuthConfig($config['admin_user'], $config['admin_pass']);
            break;
        case "save_hostapd_conf":
            SaveTORAndVPNConfig();
            break;
        case "theme_conf":
            DisplayThemeConfig();
            break;
        case "data_use":
            DisplayDataUsage($extraFooterScripts);
            break;
        case "system_info":
            DisplaySystem();
            break;
        case "about":
            DisplayAbout();
            break;
        default:
            DisplayDashboard($extraFooterScripts);
        }
        ?>
      </div><!-- /.container-fluid -->
    </div><!-- End of Main Content -->
    <!-- Footer -->
    <footer class="sticky-footer bg-grey-100">
      <div class="container my-auto">
        <div class="copyright text-center my-auto">
          <span></span>
        </div>
      </div>
    </footer>
    <!-- End Footer -->
    </div><!-- End of Content Wrapper -->
    </div><!-- End of Page Wrapper -->
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top" style="display: inline;">
      <i class="fas fa-angle-up"></i>
    </a> 

    <!-- jQuery -->
    <script src="dist/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="dist/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript -->
    <script src="dist/jquery-easing/jquery.easing.min.js"></script>

    <!-- Chart.js JavaScript -->
    <script src="dist/chart.js/Chart.min.js"></script>

    <!-- SB-Admin-2 JavaScript -->
    <script src="dist/sb-admin-2/js/sb-admin-2.js"></script>

    <!-- Custom RaspAP JS -->
    <script src="app/js/custom.js"></script>

    <?php if ($page == "wlan0_info" || !isset($page)) { ?>
    <!-- Link Quality Chart -->
    <script src="app/js/linkquality.js"></script>
    <?php }
    
    // Load non default JS/ECMAScript in footer.
    foreach ($extraFooterScripts as $script) {
        echo '    <script type="text/javascript" src="' , $script['src'] , '"';
        if ($script['defer']) {
            echo ' defer="defer"';
        }
        echo '></script>' , PHP_EOL;
    }
    ?>
    
    <!-- Make sure you put this AFTER Leaflets CSS -->
    <script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
    integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
    crossorigin=""></script>
    
    <!-- Load plotly.js into the DOM -->
    <script src='https://cdn.plot.ly/plotly-latest.min.js'></script>

    <!-- Custom Plotly JS -->
    <!--script src="app/js/plotly.js"></script-->
    <!-- Plotly.js 3D Definitions -->
    <script type="text/javascript">
    
    // NOTE USE https://developer.mozilla.org/de/docs/Web/JavaScript/Reference/Global_Objects/Array/map
    // NOTE USE https://www.w3schools.com/colors/colors_picker.asp

    /* NOTE PROGRESS BAR ... für Messung / Aktuaisierung Ladebalken
            var elem = document.getElementById("myBar");
            var width = 0;
            var id = setInterval(frame, 10);
            function frame() {
                if (width == 100) {
                    clearInterval(id);
                } else {
                    width++;
                    elem.style.width = width + '%';
                }
            }*/        
    
    // NOTE WORKING Static Ratio and satellite count, limited to three hours
    // NOTE DATA Fields: Timestamp,X,Y,Z,UTMZ,UTMR,UTMH,ALT,R95,PDOP,SDX,SDY,SDZ,Q,NS,RATIO
    // function unpack(rows, key) { return rows.map(function(row) { return row[key]; }); }


/*        const date = Date.now();
        let currentDate = null;
        do { currentDate = Date.now(); } while (currentDate - date < 300); */

/*      Plotly.d3.csv('rtksvr.csv', function(data) { processData(data) } );
      function processData(allRows) {
            var last   = allRows.length-1;
            var time   = new Date(Date.parse(allRows[last]['Timestamp'])); 
            var update = { x: [[time],[time]], y: [[R95],[RATIO]] }
            var olderTime  = time.setMinutes(time.getMinutes() - timespan);
            var futureTime = time.setMinutes(time.getMinutes() + timespan); */


    
    Plotly.d3.csv('rtksvr.csv', function(err, rows) {
      function unpack(rows, key) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                                   return rows.map(function(row) { var RowTime = Date.parse(row['Timestamp']); 
                                                                   if ((DataTime - RowTime) < 3*3600000 ) return row[key]; 
                                                                   }).filter(Boolean);
                                   }
      var data = [{ x: unpack(rows, 'Timestamp'),
                    y: unpack(rows, 'NS'),
                    type: 'scatter',
                    line: { color: 'rgb(23, 190, 207)', width: 0.8, opacity: 0.1, },
                    name: 'Sat count', fill: 'tozeroy', fillcolor: 'rgba(230, 255, 255,0.6)' },
                  { x: unpack(rows, 'Timestamp'),
                    y: unpack(rows, 'RATIO'),
                    type: 'scatter',
                    line: { color: 'blue', width: 2 },
                    opacity: 1.0, name: 'Ratio', yaxis: 'y2' }];
          
      var layout = {autosize: true, height: 350, margin: {l: 50, t: 60, b:30 },
                    title: {text: 'Ratio factor of ambiguity validation and number of satellites used',
                    font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' },
                    xaxis: { type: 'date', tickformat: "%X", automargin: true,
                             rangeselector: {buttons: [{ count: 1,  label: '1d',  step: 'day', stepmode: 'backward' },
                                                       { count: 10, label: '10d', step: 'day', stepmode: 'backward' },
                                                       { step: 'all'} ]}, },
                    yaxis: { title: 'Sat count   ', type: 'linear', zeroline: false, side: 'right', rangemode: 'tozero', automargin: true },
                    yaxis2: { title: 'Ratio', yanchor: 'top', type: 'log', zeroline: false, side: 'left', overlaying: 'y', automargin: true } };
      Plotly.react('Chart1', data, layout); 
      // Enable Tab if data available
      $('a[href="#signall"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("RTK Quality"); ?></span>').removeClass('disabled');    
    });

    // NOTE Time series Position, stacked
    Plotly.d3.csv('rtksvr.csv', function(err, rows) {
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              var LastVal = [rows[rows.length-1]['UTMH'], rows[rows.length-1]['UTMR'], rows[rows.length-1]['ALT'] ];
                              const ts=[], utmh=[], utmr=[], alt=[];
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      if ((DataTime - RowTime) < 8*3600000 ) {
                                                         ts.push(row['Timestamp']);
                                                         utmh.push((LastVal[0]-row['UTMH'])*1000);
                                                         utmr.push((LastVal[1]-row['UTMR'])*1000);
                                                         alt.push(( LastVal[2]-row['ALT'] )*1000);
                                                      }});
                              return { ts, utmh, utmr, alt }; } // (LastVal-row[key])*1000
      const {ts, utmh, utmr, alt} = unpack(rows);
  
      var trace1 = { x: ts,
                     y: utmh, // UTMH - North
                     name: 'UTMH', 
                     line: {color: 'blue', width: 0.8 },
                     type: 'scatter' };
  
      var trace2 = { x: ts,
                     y: utmr, // UTMR - East
                     name: 'UTMR',color:'#5C7DDE',
                     line: {color: 'blue', width: 0.8 },
                     xaxis: 'x',
                     yaxis: 'y2',
                     type: 'scatter' };
  
      var trace3 = { x: ts, 
                     y: alt, // ALT - Altitude
                     name: 'ALT',color:'#5C7DDE',
                     line: {color: 'blue', width: 0.8 },
                     xaxis: 'x',
                     yaxis: 'y3',
                     type: 'scatter' };
  
      var data = [trace1, trace2, trace3];
  
      var layout = { showlegend: false, height: 500, margin: {l: 70, t: 60, b:20, r:5 },
                     grid: { rows: 3, columns: 1, shared_xaxes: true, 
                     subplots:[['xy'],['xy2'],['xy3']],
                     roworder:'top to bottom' },
                     title: {text: 'Position Time Series in ETRS89/UTM',
                     font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' },
                     xaxis: {tickformat: "%X" },
                     yaxis:  {title: {text: 'North<br>[mm]', font: { color:'#5C7DDE' } }, tickformat: '.1f', range: [-15.00, +15.00], autorange: false},
                     yaxis2: {title: {text: 'East<br>[mm]' , font: { color:'#5C7DDE' } }, tickformat: '.1f', range: [-15.00, +15.00], autorange: false},
                     yaxis3: {title: {text: 'Up<br>[mm]'   , font: { color:'#5C7DDE' } }, tickformat: '.1f', range: [-30.00, +30.00], autorange: false}
                     };

      Plotly.newPlot('Chart2', data, layout); 
      // Enable Tab if data available
      $('a[href="#positionss"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Position"); ?></span>').removeClass('disabled');
    }); // End of inline function

    // NOTE Satellite ephemeris observations
    var r=1;
    var obsts=24;
    Plotly.d3.csv('strobs.csv', function(err, rows) { 
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              const ts=[], va=[], vb=[], vc=[], vd=[], ve=[], vf=[];  // Timestamp,az,el,cno,PRN
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      if ((DataTime - RowTime) < obsts*3600000 ) {
                                                         var phi   = Math.PI/180 * parseFloat(row['el']);
                                      		         var theta = Math.PI/180 * parseFloat(row['az']); 
          		                                 var x = -r * Math.cos(theta) * Math.cos(phi);
          		                                 var y = r * Math.sin(theta) * Math.cos(phi);
                                                         var z = r * Math.sin(phi);
                                                         var s = 1 + 9 * Math.cos(phi);
                                                         ts.push(row['Timestamp']);
                                                         va.push(x);
                                                         vb.push(y);
                                                         vc.push(z);
                                                         vd.push(row['cno']);
                                                         ve.push("<b>PRN"+row['PRN'] +"</b><br>Azimuth: " + row['az'] + "°<br>Elevation: " + row['el'] + "°<br>C/NO: "+ row['cno']+"dB");
                                                         //vf.push(parseInt(s));
                                                      }});
                              // console.log({ ts, va, vb, vc, vd, ve });
                              return { ts, va, vb, vc, vd, ve }; } 
      const { ts, va, vb, vc, vd, ve } = unpack(rows); 
      // console.log({ ts, va, vb, vc, vd, ve });
      var data = [{ x: va,
                    y: vb,
                    z: vc,
                    hoverinfo: 'text', hovertext: ve,
                    type: 'scatter3d',
                    mode: 'markers',
                    marker: {size: 2, color: vd, colorscale: 'solar', showscale: true, reversescale: false, opacity: 0.3 }
                    } ]; // colorscale = c('#FFE1A1', '#683531'), Viridis

      var layout = {type: 'scatter3d', height: 600, margin: {l: 50, t: 60, b:30 },

                    scene: { aspectmode: 'manual', aspectratio: {x: 1, y:1, z: 0.5 }, // cube+data = verzerrt
                             /* camera: { up: {x:0 , y:0, z:1},
                                       center: {x: 0, y:0, z: 1},
                                       eye: {x: 0.001, y: 0, z: 1.5} }, */
                             xaxis: { title: 'EW', range: [-1,+1], showticklabels: false, gridcolor: 'white', zerolinecolor: 'white' }, 
                             yaxis: { title: 'N<br>S', range: [-1,+1], showticklabels: false, gridcolor: 'white', zerolinecolor: 'white'  }, 
                             zaxis: { title: { font: { color: 'white' }}, range: [0,+1], showticklabels: false, 
                                      backgroundcolor: 'rgb(230, 230,200)', gridcolor: 'white',
                                      showbackground: true, zerolinecolor: 'white' } },
                             title: {text: 'Satellite Visibility ('+obsts+' hours; n= ' + va.length + ')',
                             font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' }
                    }
    
      Plotly.react('Chart4', data, layout); 
      // Enable Tab if data available
      $('a[href="#skyplott"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Skyplot"); ?></span>').removeClass('disabled');    
    });

    // NOTE Satellite ephemeris observations

    var r=1;
    Plotly.d3.csv('strobs.csv', function(err, rows) { 
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              const ts=[], va=[], vb=[], vc=[], vd=[], ve=[], vf=[];  // Timestamp,az,el,cno,PRN
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      if ((DataTime - RowTime) < obsts*3600000 ) {
                                                         var phi   = row['el']; // Math.PI/180 * parseFloat(row['el']);
                                      		         var theta = row['az']; // Math.PI/180 * parseFloat(row['az']); 
                                                         ts.push(row['Timestamp']);
                                                         va.push(phi);
                                                         vb.push(theta);
                                                         vd.push(row['cno']);
                                                         vc.push("<b>PRN"+row['PRN'] +"</b><br>Azimuth: " + row['az'] + "°<br>Elevation: " + row['el'] + "°<br>C/NO: "+ row['cno']+"dB");
                                                      }});
                              return { ts, va, vb, vc, vd }; } 
      const { ts, va, vb, vc, vd } = unpack(rows); 
      var data = [{ theta: vb,
                    r: va,
                    hoverinfo: 'text', hovertext: vc,
                    type: 'scatterpolar',
                    mode: 'markers',
                    marker: {size: 4, color: vd, colorscale: 'solar', showscale: true, reversescale: false, opacity: 0.3 }
                    } ]; // colorscale = c('#FFE1A1', '#683531'), Viridis

      var layout = {height: 400, margin: {l: 50, t: 60, b:30 },
                    polar: { radialaxis: {angle: 90, orientation: 90, range: [90, 0] },
                             angularaxis: { rotation: 90, direction: 'clockwise', dtick: 30} },
                             title: {text: 'Satellite Visibility ('+obsts+' hours; n= ' + va.length + ')',
                             font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' }
                    }
    
      Plotly.react('Chart5', data, layout); 
      // Enable Tab if data available
      $('a[href="#skyplott"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Skyplot"); ?></span>').removeClass('disabled');    
    });

    // NOTE WORKING Two Parameter Live Chart, limited to 20 minutes, adjustable
    // Encounters errors, to be checked
    var timespan=30;
    Plotly.d3.csv('rtksvr.csv', function(err, rows) {
      function unpack(rows) { var DataTime = Date.parse(rows[rows.length-1]['Timestamp']);
                              const ts=[], va=[], vb=[];
                              rows.forEach((row) => { var RowTime = Date.parse(row['Timestamp']); 
                                                      function round(number, decimals) { return +(Math.round(number + "e+" + decimals)  + "e-" + decimals); }
                                                      if ((DataTime - RowTime) < timespan*3600000/60 ) {
                                                         ts.push(row['Timestamp']);
                                                         va.push(row['R95']);
                                                         vb.push(row['RATIO']);
                                                      }});
                              return { ts, va, vb }; } 

      const { ts, va, vb } = unpack(rows);
      var data = [{ x: ts,
                    y: va,
                    name: 'R95',
                    texttemplate: "Price: %{R95:$.2f}"  },
                  { x: ts,
                    y: vb,
                    name: 'A-Ratio' } ];
      var layout = {autosize: true, height: 250, margin: {l: 50, t: 60, b:30 },
                    title: {text: 'Live: Ratio factor of ambiguity validation and R95',
                    font: { size: 20, color: 'blue' }, xref: 'container', x: 0.0, xanchor: 'left', yref: 'container', y: 0.975, yanchor: 'top' },
                    xaxis: { type: 'date', tickformat: '%X' },
                    yaxis: { type: 'linear', zeroline: false, rangemode: 'tozero' },
                    yaxis: { title: 'A-Ratio', type: 'log', zeroline: true, side: 'left', rangemode: 'tozero', overlaying: 'y' }
                    };
      Plotly.react('Chart3', data, layout); 
      // Enable Tab if data available + show live view ...
      $('a[href="#metricss"]').html('<span class="" role="status" aria-hidden="false"><?php echo _("Live"); ?></span>').removeClass('disabled');    
      $('a[href="#metricss"]').tab('show');
    });

    var UpdRate = 2000;
    var cnt = 0;
    var meas = [];
    var interval = setInterval(function() {
      ++cnt;
      var elem = document.getElementById("pgb");
      elem.innerHTML = Math.min(100*cnt/20,100).toFixed(0) + '%';
      elem.style.width = Math.min(cnt/20*100,100) + '%'; 
      Plotly.d3.csv('rtksvr.csv', function(data) { processData(data) } );
      function processData(allRows) {
            var last   = allRows.length-1;
            var R95    = allRows[last]['R95'];
            var RATIO   = allRows[last]['RATIO']; 
            console.log("RATIO="+RATIO);
            if (parseFloat(R95) < 1.5 && parseFloat(RATIO) > 1 && cnt*UpdRate/1000 > 40 ) { // Mind.Anf.gem.Durchführungshinweise LGL BW, 10 Sekunden Stab+30s Messung
              $('#btn-meas').html('<span class="" role="status" aria-hidden="false"><?php echo _("Start Measurement"); ?></span>').prop('disabled', false);  					//removeClass('disabled');    
              meas = allRows.slice(allRows.length - 30, allRows.length);
            } else {
              $('#btn-meas').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span><?php echo _("Stabilizing ..."); ?>').prop('disabled', true)     //addClass('disabled');    
            }
            var time   = new Date(Date.parse(allRows[last]['Timestamp'])); 
            var update = { x: [[time],[time]], y: [[R95],[RATIO]] }
            var olderTime  = time.setMinutes(time.getMinutes() - timespan);
            var futureTime = time.setMinutes(time.getMinutes() + timespan);
            var minuteView = { xaxis: { range: [olderTime,futureTime], type: 'date', tickformat: '%X'} }
            Plotly.relayout('Chart3', minuteView);
            Plotly.extendTraces('Chart3', update, [0,1]);
            if(++cnt === 150) clearInterval(interval) // nach 1500x ist schluss
        }
    } , UpdRate);

    // NOTE Der Mess-Knopf ...
    $("#btn-meas").click(function() {
//      document.getElementById("measres").value = "hallo";
/*      var elem = document.getElementById("pgb");
      for (i = 0; i <= 100; i++) { elem.text = i + '%';
                                   elem.style.width = i + '%'; } */
        console.log(meas.length); 
        console.log(meas[0]['Timestamp']);

        var PArr = meas.map(function(ae) { return ae['PDOP']; });
        var SArr = meas.map(function(ae) { return ae['NS']; });
        var QArr = meas.map(function(ae) { return (ae['Q'] == 1) ? 100 : 0; });
        const RQ = QArr.reduce((a,b) => a + b, 0) / meas.length;
        var MR = 0;
        var MH = 0;
        var MA = 0;
        for (i=0; i<meas.length; i++) {
            MR += parseFloat(meas[i]['UTMR']);
            MH += parseFloat(meas[i]['UTMH']);
            MA += parseFloat(meas[i]['ALT']);
        }
        MR = MR / meas.length;
        MH = MH / meas.length;
        MA = MA / meas.length;

        var out = "Timestamp|PDOP|SAT|RTK|UTM-Zone|UTM-Right|UTM-Height|Altitude";
        var out = out + "\r\n" + meas[0]['Timestamp'] + '|'+ Math.min(...PArr).toFixed(4) +'-'+ Math.max(...PArr).toFixed(4) +'|';
        var out = out + Math.min(...SArr).toFixed(0) +'-'+ Math.max(...SArr).toFixed(0) +'|'+RQ.toFixed(1)+'%|';
        var out = out + meas[0]['UTMZ']+'|'+ MR.toFixed(3) +'|'+ MH.toFixed(3) +'|'+ MA.toFixed(3) +'|'; 
        document.getElementById("measres").value = out;
        $('#btn-meas').html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span><?php echo _("Stabilizing ..."); ?>').prop('disabled', true)
        cnt=0;
    });
    </script>

    <!-- Import Markers from file -->
    <script type='text/javascript' src='app/js/markers.js'></script>

    <!-- Leaflet -->
    <script type="text/javascript">
    /*    p1=51.505; p2=-0.091;*/
    p1 = <?php echo $lat; ?>;
    p2 = <?php echo $lon; ?>;
    epv = parseFloat(<?php echo $epv; ?>);
    var markers = <?php echo json_encode($loca); ?>;
    
    // Center Map on first entry
    p1 = parseFloat(markers['BASE'].lat);
    p2 = parseFloat(markers['BASE'].lng);
    var mymap = L.map('mapid').setView([p1, p2], 20);

    L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
	maxZoom: 20,
  	attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
        '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
	'Imagery ?? <a href="https://www.mapbox.com/">Mapbox</a>',
	id: 'mapbox/streets-v11',
	tileSize: 512,
	zoomOffset: -1
    }).addTo(mymap); 

    /* for ( var i=0; i < markers.length; ++i ) */
    for ( i in markers )
    { L.marker([markers[i].lat, markers[i].lng])
       .bindPopup( '<a href="' + markers[i].url + '" target="_blank">' + markers[i].name + '</a>' )
       .addTo(mymap);
    }
    
    L.circle([p1, p2], epv, {
		color: 'red',
		fillColor: '#f03',
		fillOpacity: 0.5
	}).addTo(mymap).bindPopup("Estimated vertical error" )
	.on('mouseover', function (e) {
            this.openPopup();
        }).on('mouseout', function (e) {
            this.closePopup();
        });

/*	L.polygon([
		[51.509, -0.08],
		[51.503, -0.06],
		[51.51, -0.047]
	]).addTo(mymap).bindPopup("I am a polygon."); */
    
    </script>
    
  </body>
</html>
