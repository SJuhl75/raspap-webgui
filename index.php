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
          
    <!-- OpenLayers CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.3.1/css/ol.css" type="text/css">
    <style>
      .map {
        height: 400px;
        width: 100%;
      }
    </style>
    
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
    
    <!-- OpenLayers JS -->
    <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.3.1/build/ol.js"></script>

    <script type="text/javascript">
    var LonLat = ol.proj.fromLonLat([{$GLOBALS['lon']}, {$GLOBALS['lat']}])
    var stroke = new ol.style.Stroke({color: 'red', width: 2});

  var feature = new ol.Feature(new ol.geom.Point(LonLat))
  var x = new ol.style.Style({
    image: new ol.style.RegularShape({
      stroke: stroke,
      points: 4,
      radius: 10,
      radius2: 0,
      angle: 0.785397   // Pi / 4
      })
  })
  feature.setStyle(x)
  var source = new ol.source.Vector({
      features: [feature]
  });

  var vectorLayer = new ol.layer.Vector({
    source: source
  });

  var map = new ol.Map({
    target: 'mapol',
    layers: [
      new ol.layer.Tile({
        source: new ol.source.OSM()
      }),
      vectorLayer
    ],
    view: new ol.View({
      center: LonLat,
      zoom: 6
    })
  });
</script>


    <script type="text/javascript">
      var LonLat = ol.proj.fromLonLat([parseFloat(<?php echo $lon; ?>),parseFloat(<?php echo $lat; ?>)]);
      var map = new ol.Map({
        target: 'mapol',
        layers: [
          new ol.layer.Tile({
            source: new ol.source.OSM()
          })
        ],
        view: new ol.View({
//          center: ol.proj.fromLonLat([37.41, 8.82]),
          center: LonLat,
          zoom: 4
        })
      });
    </script>

    <script type='text/javascript' src='app/js/markers.js'></script>

<script type="text/javascript">
/*    p1=51.505; p2=-0.091;*/
    p1 = <?php echo $lat; ?>;
    p2 = <?php echo $lon; ?>;
    epv = parseFloat(<?php echo $epv; ?>);
    var markers = <?php echo json_encode($loca); ?>;
    
    // Center Map on first entry
    p1 = parseFloat(markers[0].lat);
    p2 = parseFloat(markers[0].lng);
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

    for ( var i=0; i < markers.length; ++i )
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
