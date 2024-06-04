<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Lang" content="en" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <title>REDRetarget</title>
    
    <meta content="authenticity_token" name="csrf-param"/>
    <meta content="<?php echo $token; ?>" name="csrf-token"/>    
    
    <link href="<?php echo base_url(); ?>assets/images/icon.png" rel="shortcut icon" type="image/vnd.microsoft.icon" />
    <!--<link rel='stylesheet' id='roboto-fonts-css'  href='//fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i' type='text/css' media='all' />-->
    <link href="//fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
    
    
    <link href="<?php echo base_url(); ?>assets/css/style.min.css" rel="stylesheet" />
    <link href="<?php echo base_url(); ?>assets/css/style-responsive.min.css" rel="stylesheet" />
    <link href="<?php echo base_url(); ?>assets/css/retina.css" rel="stylesheet" />
    
    <script src="<?php echo base_url(); ?>assets/js/jquery-1.12.0.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/jquery-ui.min.js"></script>
    
    <script src="<?php echo base_url(); ?>assets/plugins/jquery.blockUI/jquery.blockUI.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/core.min.js"></script>
    
    <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js"></script>
    
    <link href="<?php echo base_url(); ?>assets/plugins/qtip/jquery.qtip.min.css" rel="stylesheet" />
    <script src="<?php echo base_url(); ?>assets/plugins/qtip/jquery.qtip.min.js"></script>
    
    <link href="<?php echo base_url(); ?>assets/plugins/select2/select2.min.css" rel="stylesheet" />
    <script src="<?php echo base_url(); ?>assets/plugins/select2/select2.min.js"></script>
    
    <link href="<?php echo base_url(); ?>assets/plugins/jquery-fancybox/jquery.fancybox.css" rel="stylesheet" />
    <script src="<?php echo base_url(); ?>assets/plugins/jquery-fancybox/jquery.fancybox.js"></script>    
    
    <script src="<?php echo base_url(); ?>assets/plugins/chosen/chosen.jquery.min.js"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/chosen/chosen.min.css">
    <script src="<?php echo base_url(); ?>assets/plugins/jquery-datatables/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/jquery-datatables/jquery.dataTables.min.css">
    
    <script src="<?php echo base_url(); ?>assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css">
    
    
    <link href="<?php echo base_url(); ?>assets/css/app.css?<?php echo time(); ?>" rel="stylesheet" />
    <script src="<?php echo base_url(); ?>assets/js/app.js"></script>
    
    <!-- REDretarget Google Analytics Code -->
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-53203083-1', 'auto');
      ga('send', 'pageview');

    </script>
    <!-- Trackify Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','//connect.facebook.net/en_US/fbevents.js');

        fbq('init', '1604876259777969');
        fbq('track', "PageView");
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1604876259777969&ev=PageView&noscript=1" /></noscript>
    
    <!--<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/ueyrd4rp';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>-->
        
    <script>
        var base_url = "<?php echo base_url(); ?>";        
    </script>
    
</head>

<body class="<?php echo $current_template . " {$body_class}"; ?>">
    <div id="content_wrap">
        <?php if (!empty($_SESSION['oauth_token'])) : ?>
        <div class="navbar">
            <div class="navbar-inner">
                <div class="row-fluid">
                    <div class="pull-left">
                        <a href="<?php echo base_url(); ?>"><img class="logo" src="<?php echo base_url(); ?>assets/images/trackify.jpg" /></a>
                    </div>
                    <?php if ($current_template != "welcome") : ?>
                    <div class="pull-right">
                        <div class="nav_wrap">
                            <span class="shop_info nav_inline">
                                <label>Shop</label>
                                <span><?php echo $shop['name']; ?></span>
                            </span>
                            <div class="dropdown nav_inline">
                                <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                    <span class="user_info">
                                        <label>Admin</label>
                                        <span><?php echo $shop['shop_owner']; ?></span>
                                    </span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    <li><a href="<?php echo base_url(); ?>logout"><i class="glyphicon glyphicon-log-out"></i>Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="mainwrapper">