  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col">
              <i class="fas fa-broadcast-tower fa-fw mr-2"></i><?php echo _("Modem Status and Dial-Up configuration"); ?>
            </div>
            <div class="col">
              <button class="btn btn-light btn-icon-split btn-sm service-status float-right">
                <span class="icon text-gray-600"><i class="fas fa-circle service-status-<?php echo $serviceStatus ?>"></i></span>
                <span class="text service-status">pppd <?php echo _($serviceStatus) ?></span>
              </button>
            </div>
          </div><!-- /.row -->
        </div><!-- /.card-header -->
        <div class="card-body">
        <?php $status->showMessages(); ?>
          <form role="form" action="?page=modem_conf" enctype="multipart/form-data" method="POST">
            <?php echo CSRFTokenFieldTag() ?>
            <!-- Nav tabs -->
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" id="modem" href="#modemtab" data-toggle="tab"><?php echo _("Modem status"); ?></a></li>
                <li class="nav-item"><a class="nav-link" id="messages" href="#messagetab" data-toggle="tab"><?php echo _("Messages"); ?></a></li>
                <li class="nav-item"><a class="nav-link" id="config" href="#configtab" data-toggle="tab"><?php echo _("Dial-Up configuration"); ?></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
              <div class="tab-pane active" id="modemtab">
                <?php if (!$avail) : ?>
                  <h4 class="mt-3"><?php echo _("No Modem found"); ?></h4 -->
                  <p></p><input type="submit" class="btn btn-outline btn-primary" name="RestartModemManager" value="Restart Modem Manager?" />
                <?php endif ?>
                <!-- h4 class="mt-3"><?php echo _("Modem info and settings"); ?></h4 -->
                  <div class="row">
                    <div class="col-md-11 mt-1 mb-2">
                      <div class="info-item"><?php echo _("Modem"); ?></div>
                      <div class="col"><?php echo htmlspecialchars($modem,ENT_QUOTES); ?></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mt-1 mb-2">
                      <div class="info-item"><?php echo _("IMEI"); ?></div>
                      <div><?php echo $imei; ?></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mt-1 mb-2">
                      <div class="info-item"><?php echo _("Phone number"); ?></div>
                      <div><?php echo htmlspecialchars($phonenr,ENT_QUOTES); ?></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6 mt-1 mb-2">
                      <div class="info-item"><?php echo _("Network details"); ?></div>
                      <div><?php echo htmlspecialchars($constat,ENT_QUOTES); ?></div>
                    </div>
                  </div>
                <div class="info-item"><?php echo _("Signal Quality");         ?></div>
                <!-- div><?php echo $signal;      ?></div -->
                <div class="progress mb-2" style="height: 20px;">
                  <div class="progress-bar bg-<?php echo htmlspecialchars($signal_status, ENT_QUOTES); ?>"
                                              role="progressbar" aria-valuenow="<?php echo htmlspecialchars($signal, ENT_QUOTES); ?>" aria-valuemin="0" aria-valuemax="100"
                                              style="width: <?php echo htmlspecialchars($signal, ENT_QUOTES); ?>%"><?php echo htmlspecialchars($signal, ENT_QUOTES); ?>%
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mt-1 mb-2">
                      <div class="info-item"><?php echo _("Provider"); ?></div>
                      <div><?php echo htmlspecialchars($provider,ENT_QUOTES); ?></div>
                    </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mt-2 mb-2">
                    <div class="info-item"><?php echo _("Public IPv4 Address"); ?></div>
                    <div class="info-item"><?php echo htmlspecialchars($public_ip, ENT_QUOTES); ?><a class="text-gray-500" href="https://ipapi.co/<?php echo($public_ip); ?>" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt ml-2"></i></a></div>
                  </div>
                </div>
              </div>

              <div class="tab-pane" id="configtab">
                <!-- p class="lead text-left"><?php echo _('No messages found') ?></p -->
                <!--h4 class="mt-3"><?php echo _("Modem info and settings"); ?></h4 -->
                <div class="row">
                   <div class="form-group col-md-6">
                    <label for="code"><?php echo _("Dial-In Number"); ?></label>
                      <input type="text" class="form-control" name="dialin" value="<?php echo htmlspecialchars($dialin, ENT_QUOTES); ?>" />
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
                      <input type="text" class="form-control" name="authPassword" value="<?php echo htmlspecialchars($authPassword, ENT_QUOTES); ?>" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="form-group col-md-6">
                      <label for="code"><?php echo _("APN"); ?></label>
                      <input type="text" class="form-control" name="APN" value="<?php echo htmlspecialchars($APN, ENT_QUOTES); ?>" />
                    </div>
                  </div>
                  <?php if (!RASPI_MONITOR_ENABLED) : ?>
                    <input type="submit" class="btn btn-outline btn-primary" name="SaveModemConfig" value="Save settings & restart Network Manager" />
                  <?php endif ?>
              </div>

              <div class="tab-pane" id="messagetab">
                <!-- h4 class="mt-3"><?php echo _("Client log"); ?></h4 -->
                <?php if (empty($messages)) : ?>
                <!-- p class="lead text-left"><?php echo _('No messages found') ?></p -->
                <!-- p class="text-center"><?php echo _('Click "Rescan" to search for nearby Wifi stations.') ?></p-->
                <?php endif ?>
                <?php $index = 0; ?>
                <div class="row ml-1 mr-1">
                  <?php foreach ($messages as $key => $message) : ?><!-- networks as $ssid => $network -->
                    <div class="col-sm-6 align-items-stretch mb-3">
                    <div class="card h-100">
                    <div class="card-header bg-light text-dark">
                    <div class="d-flex justify-content-between bg-light">
                    <div class="font-weight-bold align-self-center text-dark"><?php echo htmlspecialchars($message[timestamp], ENT_QUOTES); ?></div>
                    <div class="font-weight-bold align-self-center text-dark"><?php echo htmlspecialchars($message[sender], ENT_QUOTES); ?></div>
                    <!-- div class="align-self-center"><a href="#" class="btn btn-primary">Delete</a></div -->
                    <?php if (!RASPI_MONITOR_ENABLED) : ?>
                      <input type="submit" href="#messagetab" class="col-xs-1 col-md-1 btn btn-info" value="<?php echo _("X"); ?>" id="delete<?php echo $message[id] ?>" name="delete<?php echo $message[id] ?>" />
                    <?php endif ?>
                    <!-- button type="submit" href="#" class="col-xs-1 col-md-1 btn btn-info" value="<?php echo $index?>" name="delete1"><?php echo _("X"); ?></button -->
                    <!-- input type="submit" class="btn btn-success" name="delete" value="delete2" / -->
                  </div>
                  <!-- ?php echo htmlspecialchars($message[timestamp], ENT_QUOTES); ?>
                  <?php echo htmlspecialchars($message[sender], ENT_QUOTES); ?>
                  <a href="#" class="btn btn-primary">Go somewhere</a -->
              </div>
              
          <div class="card-body">
        <!-- h5 class="card-title"><?php echo htmlspecialchars($message[text], ENT_QUOTES); ?></h5 -->
        <p class="card-text"><?php echo htmlspecialchars($message[content], ENT_QUOTES); ?></p>
        <!-- a href="#" class="btn btn-primary">Go somewhere</a -->
      </div>
    </div><!-- /.card -->
  </div><!-- /.col-sm -->
  <?php $index += 1; ?>
<?php endforeach ?>
</div><!-- /.row -->

                <div class="row">
                  <!-- div class="form-group col-md-8">
                    <textarea class="logoutput" id="logoutput"><?php echo $logoutput;?></textarea>
                  </div -->
                </div>
              </div>
              <?php if (RASPI_MONITOR_ENABLED) : ?>
                  <input type="submit" class="btn btn-outline btn-primary" name="SaveVPNCSettings" value="Save settings" />
                  <?php if ($serviceStatus[0] == 0) {
					  echo '<input type="submit" class="btn btn-success" name="StartVPNC" value="Start VPN" />' , PHP_EOL;
				  } else {
                    echo '<input type="submit" class="btn btn-warning" name="StopVPNC" value="Stop VPN" />' , PHP_EOL;
                  }
                  ?>
              <?php endif ?>
              </form>
            </div>
        </div><!-- /.card-body -->
    <div class="card-footer"> Information provided by Modem Manager</div>
  </div><!-- /.card -->
</div><!-- /.col-lg-12 -->
</div><!-- /.row -->

