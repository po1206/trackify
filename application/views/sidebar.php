<?php if (!empty($_SESSION['oauth_token'])) : ?>
<div id="sidebar-left">
    <div class="nav-collapse sidebar-nav">
        <ul class="nav nav-tabs nav-stacked main-menu">
            <li>
                <a href="<?php echo base_url() . "settings/"; ?>" class="tour-abandon <?php if ($current_template == 'settings') echo 'active'; ?>">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Settings</span>
                </a>
            </li>
            <li>
                <a href="<?php echo base_url() . "track/"; ?>" class="tour-abandon <?php if ($current_template == 'track_facebook' || $current_template == 'track_conversion' || $current_template == 'manage_conversion' || $current_template == 'manage_facebook') echo 'active'; ?>">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Niche Tracking</span>
                </a>
            </li>
            <?php if (!empty($_GET['tag'])) { ?>
            <li id="liAbandon" style="margin-left: 10px;">
                <a <?php if (strpos($_SERVER['REQUEST_URI'],'manage.php') !== false) {echo 'style="background: #2D323D"';} ?> href="manage.php?tag=<?php echo $_GET['tag']; ?>" class="tour-abandon">
                    <i class="icon-tag"></i><span class="hidden-tablet "><?php echo $_GET['tag']; ?></span>
                </a>
            </li>
            <?php } ?>

            <li style="display: none;">
                <a href="<?php echo base_url() . "report/purchase"; ?>" class="tour-abandon <?php if ($current_template == 'report_purchase') echo 'active'; ?>">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Purchase Event Logs</span>
                </a>
            </li>

            <li>
                <a href="<?php echo base_url() . "custom_audiences"; ?>" class="tour-abandon <?php if ($current_template == 'custom_audiences' || $current_template == 'create_laas') echo 'active'; ?> <?php if ($current_template == 'create_custom_audience' || $current_template == 'create_lookalike_audience') echo 'sub-menu-active'; ?>">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Facebook Audiences</span>
                </a>
                <ul class="nav sub-menu">
                    <li>
                        <a href="<?php echo base_url() . "audiences/create/custom/" . $settings["ad_account"]; ?>" class="tour-abandon <?php if ($current_template == 'audiences/create_custom') echo 'active'; ?>">Create CA</a>
                    </li>
                    <li>
                        <a href="<?php echo base_url() . "create_audience/lookalike/" . $settings["ad_account"]; ?>" class="tour-abandon <?php if ($current_template == 'create_lookalike_audience') echo 'active'; ?>">Create LAA</a>
                    </li>
                </ul>
            </li>

            <li>
                <a href="<?php echo base_url() . "build-feed/"; ?>" class="tour-abandon <?php if ($current_template == 'build_feed' || $current_template == 'create_catalog' || $current_template == 'fb_feeds') echo 'active'; ?>">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Facebook Feed</span>
                </a>
                <!--<ul class="nav sub-menu">
                    <li>
                        <a href="<?php echo base_url() . "facebook-feeds/"; ?>" class="tour-abandon <?php if ($current_template == 'build_feed' || $current_template == 'edit_feed') echo 'active'; ?>">
                            Build Feed
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url() . "create-feed/"; ?>" class="tour-abandon <?php if ($current_template == 'create_feed') echo 'active'; ?>">
                            Create Feed
                        </a>
                    </li>
                </ul>-->
            </li>

            <li>
                <a href="//www.facebook.com/groups/trackifyusers/" target="_blank" class="tour-abandon">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Trackify Facebook Group</span>
                </a>
            </li>

            <li>
                <a href="http://redretarget.freshdesk.com/support/solutions/folders/14000005468" class="tour-abandon" target="_blank">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Support</span>
                </a>
            </li>
            
            <!--<li>
                <a href="<?php echo base_url() . "help/"; ?>" class="tour-abandon <?php if ($current_template == 'help') echo 'active'; ?>">
                    <i class="icon-bar-chart"></i><span class="hidden-tablet ">Help</span>
                </a>
            </li>-->
            
        </ul>
    </div>
</div>
<?php endif; ?>
