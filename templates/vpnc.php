  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col">
              <i class="fas fa-key fa-fw mr-2"></i><?php echo _("IPSec Xauth PSK VPN Client"); ?>
            </div>
            <div class="col">
              <button class="btn btn-light btn-icon-split btn-sm service-status float-right">
                <span class="icon text-gray-600"><i class="fas fa-circle service-status-<?php echo $serviceStatus ?>"></i></span>
                <span class="text service-status">vpnc <?php echo _($serviceStatus) ?></span>
              </button>
            </div>
          </div><!-- /.row -->
        </div><!-- /.card-header -->
        <div class="card-body">
        <?php $status->showMessages(); ?>
          <form role="form" action="?page=vpnc_conf" enctype="multipart/form-data" method="POST">
            <?php echo CSRFTokenFieldTag() ?>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" id="clienttab" href="#vpnclient" data-toggle="tab"><?php echo _("Client settings"); ?></a></li>
                <li class="nav-item"><a class="nav-link" id="logoutputtab" href="#vpnlogoutput" data-toggle="tab"><?php echo _("Logfile output"); ?></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
              <div class="tab-pane active" id="vpnclient">
                <h4 class="mt-3"><?php echo _("Client settings"); ?></h4>
                  <!-- div class="row" -->
                    <!-- div class="col-md-6 mt-2 mb-2" -->
                      <!-- div class="info-item"><?php echo _("IPv4 Address"); ?></div -->
                      <!-- div class="info-item"><?php echo htmlspecialchars($public_ip, ENT_QUOTES); ?><a class="text-gray-500" href="https://ipapi.co/<?php echo($public_ip); ?>" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt ml-2"></i></a></div -->
                    <!-- /div>
                  </div -->
                  <div class="row">
                   <div class="form-group col-md-6">
                    <label for="code"><?php echo _("VPN Gateway"); ?></label>
                      <input type="text" class="form-control" name="VPNGateway" value="<?php echo htmlspecialchars($VPNGateway, ENT_QUOTES); ?>" />
                    </div>
                  </div>
                  <div class="row">
                   <div class="form-group col-md-6">
                    <label for="code"><?php echo _("Username"); ?></label>
                      <input type="text" class="form-control" name="authUser" value="<?php echo htmlspecialchars($authUser, ENT_QUOTES); ?>" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-md-6">
                      <label for="code"><?php echo _("Password"); ?></label>
                      <input type="password" class="form-control" name="authPassword" value="<?php echo htmlspecialchars($authPassword, ENT_QUOTES); ?>" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-md-6">
                      <label for="code"><?php echo _("IPSec ID"); ?></label>
                      <input type="text" class="form-control" name="IPSUser" value="<?php echo htmlspecialchars($IPSUser, ENT_QUOTES); ?>" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-md-6">
                      <label for="code"><?php echo _("IPSec Shared Secret"); ?></label>
                      <input type="password" class="form-control" name="IPSPSK" value="<?php echo htmlspecialchars($IPSPSK, ENT_QUOTES); ?>" />
                    </div>
                  </div>
              </div>
              <div class="tab-pane fade" id="vpnlogoutput">
                <h4 class="mt-3"><?php echo _("Client log"); ?></h4>
                <div class="row">
                  <div class="form-group col-md-8">
                    <textarea class="logoutput" id="logoutput"><?php echo $logoutput;?></textarea>
                  </div>
                </div>
              </div>
              <?php if (!RASPI_MONITOR_ENABLED) : ?>
                  <input type="submit" class="btn btn-outline btn-primary" name="SaveVPNCSettings" value="Save settings" />
                  <?php if ($vpncstatus[0] == 0) {
					  echo '<input type="submit" class="btn btn-success" name="StartVPNC" value="Start VPN" />' , PHP_EOL;
				  } else {
                    echo '<input type="submit" class="btn btn-warning" name="StopVPNC" value="Stop VPN" />' , PHP_EOL;
                  }
                  ?>
              <?php endif ?>
              </form>
            </div>
        </div><!-- /.card-body -->
    <div class="card-footer"> Information provided by vpnc</div>
  </div><!-- /.card -->
</div><!-- /.col-lg-12 -->
</div><!-- /.row -->

