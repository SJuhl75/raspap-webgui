  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col">
              <i class="fas fa-map-marked-alt fa-fw mr-2"></i><?php echo _("Continuously Operating Mobile GNSS Reference Station"); ?>
              <?php if ($STRStat=='up') : echo "@" . $qth; ?><?php endif ?>
            </div>
            <div class="col">
              <button class="btn btn-light btn-icon-split btn-sm service-status float-right">
                <span class="icon text-gray-600"><i class="fas fa-circle service-status-<?php echo $STRStat ?>"></i></span>
                <span class="text service-status">Stream Server <?php echo _($STRStat) ?></span>
              </button>
            </div><div class="col">
              <button class="btn btn-light btn-icon-split btn-sm service-status float-right">
                <span class="icon text-gray-600"><i class="fas fa-circle service-status-<?php echo $RTKStat ?>"></i></span>
                <span class="text service-status">Real-Time Kinematics <?php echo _($RTKStat) ?></span>
              </button>
            </div>
          </div><!-- /.row -->
        </div><!-- /.card-header -->
        <div class="card-body">
        <?php $status->showMessages(); ?>
          <form role="form" action="?page=GPS" enctype="multipart/form-data" method="POST">
            <?php echo CSRFTokenFieldTag() ?>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" id="gpsinfo" href="#gpsinfoo" data-toggle="tab"><?php echo _("GNSS Info"); ?></a></li>
                <li class="nav-item"><a class="nav-link" id="rtktab" href="#rtktabb" data-toggle="tab"><?php echo _("RTK"); ?></a></li>
                <li class="nav-item"><a class="nav-link" id="metrics" href="#metricss" data-toggle="tab"><?php echo _("Metrics"); ?></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
              <div class="tab-pane active" id="gpsinfoo">
                <?php if ($STRStat=='down') : ?><h4 class="mt-3"><?php echo _("No GPS data available"); ?></h4>
                <?php else : ?>
                  <div><?php echo $type . ": North " . number_format($lat,6,',','.') . "°  East " . number_format($lon,6,',','.') . "°  Altitude " . number_format($alt,3,',','.') . "m"; ?></div>
                  <div><?php echo "R95 = " . number_format($epv,2,',','.') . "m (" . $svcnt . " sats used)"; ?></div>
                  <div id="mapid" style="height: 450px; position: relative; outline: none;" 
                    class="leaflet-container leaflet-fade-anim leaflet-grab leaflet-touch-drag" 
                    tabindex="0"></div>
                <?php endif ?>
              </div>
              <div class="tab-pane" id="rtktabb">
                  <?php if ($rtkfix=='down') : ?><h4 class="mt-3"><?php echo _("No RTK data available"); ?></h4>
                    <input type="submit" class="btn btn-success" name="StartRTK" value="Start RTK Service" />
                  <?php else : ?>
                    <div><?php echo $rtkfix . ": ECEF X = " . number_format($xecef,3,',','') . "±" . number_format($sdx,3,',','') . "m " ?>
                         <?php echo             "Y = " . number_format($yecef,3,',','') . "±" . number_format($sdy,3,',','') . "m " ?>
                         <?php echo		    "Z = " . number_format($zecef,3,',','') . "±" . number_format($sdz,3,',','') . "m " ?></div>
                    <div><?php echo		    "ETRS89/UTM  = " . number_format($utme,3,',','') . "m / " . number_format($utmn,3,',','') . "m / " . number_format($utma,3,',','') . "m"?></div>
                    <div><?php echo		    "ITRF = " . number_format($ITRFE,3,',','') . "m / " . number_format($ITRFN,3,',','') . "m / " . number_format($ITRFA,3,',','') . "m"?></div>
                    <div><?php echo "R95% = " . number_format($R95,4,',','.') . "cm (" . $rsvcnt . " sats used)"; ?></div>
                    <div><?php echo "Baseline = " . number_format($baseline,3,',','.') . "m"; ?></div>
                    <input type="submit" class="btn btn-outline btn-primary" name="StartMeas" value="Start measurement" />
                    <!-- ?php echo '<input type="submit" class="btn btn-success" name="StartVPNC" value="Start VPN" />' , PHP_EOL;?-->
                  <?php endif ?>
              </div>
              <div class="tab-pane" id="metricss">
                <div id='myDiv'><!-- Plotly chart will be drawn inside this DIV --></div>
                <div id='Chart2'><!-- Plotly chart will be drawn inside this DIV --></div>
                <div id='Plot2'><!-- Plotly chart will be drawn inside this DIV --></div>
                <div id='Plot3'><!-- Plotly chart will be drawn inside this DIV --></div>
              </div>
              
            </div>
          </form>
            </div>
        </div><!-- /.card-body -->
    <div class="card-footer"> Information provided by gpsd</div>
  </div><!-- /.card -->
</div><!-- /.col-lg-12 -->
</div><!-- /.row -->

