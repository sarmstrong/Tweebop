<!DOCTYPE html>
<html>
     <head>
          <title></title>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

          <link href="stylesheets/fileuploader.css" media="screen, projection" rel="stylesheet" type="text/css" />

          <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>

          <!-- Included CSS Files -->

          <link rel="stylesheet" href="stylesheets/app.css">

          <script type="text/javascript" src="http://use.typekit.com/zcf8etf.js"></script>

          <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
          
          <?php if (ENVIRONMENT === 'development') : ?>

          <script type='text/javascript' src="js/jquery.js" ></script>
          
          <script type='text/javascript' src='js/underscore-min.js'></script>

          <script type='text/javascript' src='js/backbone-min.js'></script>

          <script type="text/javascript" src='js/fileuploader.js'></script>

          <script type="text/javascript" src='js/app.js'></script>

          <script src="js/foundation/modernizr.foundation.js"></script>
          
          <?php elseif (ENVIRONMENT === 'production') : ?>
          
          <?php //Assets::clear_cache(); ?>
          
          <?php Assets::js(array('jquery.js', 'underscore-min.js' , 'backbone-min.js'  , 'fileuploader.js' , 'app.js' , 'foundation/modernizr.foundation.js')); ?>
          
          <?php endif; ?>

          <!-- IE Fix for HTML5 Tags -->
          <!--[if lt IE 9]>
                  <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
          <![endif]-->

     </head>
     <body>
          <div id='wrapper'>
               <div id='inner-wrapper'>