<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
<head> 
	<title><?php echo SITE_TITLE." | ".SITE_SUBTITLE; ?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="<?=ST_PATH?>js/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?=ST_PATH?>css/bootstrap.css" />
	<!--<link rel="stylesheet" type="text/css" href="<?=ST_PATH?>css/bootstrap-datepicker.css" />
		<script type="text/javascript" src="<?=ST_PATH?>js/bootstrap-datepicker.js"></script>
	-->
	<link rel="stylesheet" type="text/css" href="<?=ST_PATH?>css/custom.css" />
	<script type="text/javascript" src="<?=ST_PATH?>js/bootstrap.js"></script>
	<script type="text/javascript" src="<?=ST_PATH?>js/custom.js"></script>
	<link rel="shortcut icon" href="<?=ST_PATH?>img/favicon.png" type="image/x-icon">

</head>
<body>
	<nav class="navbar navbar-default navbar-fixed-top">
      <div class="container-fluid">
      	<div class="col-md-12">
        <div class="col-md-offset-2 col-md-8 pull-right headerCode">
        	<?php headerLogin($_SESSION['logado']);?>
        	</div>
		    </div>
        </div><!--/.nav-collapse -->

    </nav>