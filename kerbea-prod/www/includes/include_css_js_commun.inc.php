<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0" />

<link rel="shortcut icon" href="<?php echo RESSOURCE_URL; ?>/icon/favicon.png">
<!--[if IE]><link rel="shortcut icon" href="<?php echo RESSOURCE_URL; ?>/icon/favicon.ico"><![endif]-->

<link rel="apple-touch-icon" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo RESSOURCE_URL; ?>/icon/apple-touch-icon-180x180.png">
<link rel="apple-touch-startup-image" sizes="320x480" href="<?php echo RESSOURCE_URL; ?>/icon/icon/startup.png"/>

<link rel="icon" sizes="192x192" href="<?php echo RESSOURCE_URL; ?>/icon/touch-icon-192x192.png">
<link rel="shortcut icon" href="<?php echo RESSOURCE_URL; ?>/icon/favicon.png">

<!-- CSS -->
<!--<link href="--><?php //echo RESSOURCE_URL; ?><!--/css/styles-reset.css" rel="stylesheet" type="text/css" />-->
<!--<link href="--><?php //echo RESSOURCE_URL; ?><!--/css/styles-general.css" rel="stylesheet" type="text/css" />-->
<!--<link href="--><?php //echo RESSOURCE_URL; ?><!--/css/styles.css" rel="stylesheet" type="text/css" />-->
<!--<link href="--><?php //echo RESSOURCE_URL; ?><!--/css/styles-dyna.css" rel="stylesheet" type="text/css" />-->
<!--<link href="--><?php //echo RESSOURCE_URL; ?><!--/css/jquery.lightbox.css" rel="stylesheet" type="text/css" />-->
<!--<link href="--><?php //echo RESSOURCE_URL; ?><!--/css/jquery-ui.css" rel="stylesheet" type="text/css" />-->
<!--<link media="screen and (min-width: 1000px)" href="css/styles-ecran.css" rel="stylesheet"  type="text/css" />-->
<!--<link media="screen and (max-width: 999px) and (min-width: 768px)" href="css/styles-tablette.css" rel="stylesheet"  type="text/css" />-->
<!--<link media="screen and (max-width: 767px)" href="css/styles-mobile.css" rel="stylesheet"  type="text/css" />-->
<link href="https://fonts.googleapis.com/css?family=Dosis:300,400,600|Raleway:300,400,600|Lora:300,400,600" rel="stylesheet">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css">
<link rel="stylesheet" href="css/normalize.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/old.css">
<!-- JS -->
<script type="text/javascript">var RESSOURCE_URL = '<?php echo RESSOURCE_URL; ?>//';</script>
<?php include( BASE_URL.WORK_DIR.'/includes/analytics.php'); ?>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php if(!empty($_SESSION['agence']['ga'])){ echo $_SESSION['agence']['ga'];} else echo 'UA-88662418-1'; ?>', 'auto');
    ga('send', 'pageview');

</script>
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/jquery.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/modernizr.min.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/jquery.lightbox.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/respond.min.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/jquery.hammer.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/jquery.carouselYnd.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/jquery.cookiebar.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/js-commun.js"></script>-->
<!--<script type="text/javascript" src="--><?php //echo RESSOURCE_URL; ?><!--/js/functions.js"></script>-->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script type="text/javascript" src="js/jquery.hammer.js"></script>
<script type="text/javascript" src="js/jquery.carouselYnd.js"></script>
<script type="text/javascript" src="js/jquery.cookiebar.js"></script>
<script src="js/main.js"></script>