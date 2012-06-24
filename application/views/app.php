<!DOCTYPE html>
<html>
     <head>
          <title></title>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

          <link href="stylesheets/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />

          <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>

          <script type='text/javascript' src="js/jquery" /></script>

     <script type='text/javascript' src='js/underscore-min.js'></script>

     <script type='text/javascript' src='js/backbone-min.js'></script>


     <script type='text/javascript' src='js/backbone.localStorage-min.js'></script>


     <script type="text/javascript" src='js/app.js'></script>

</head>
<body>
     <div id='app-view'>

          <header>

               <?php //var_dump($user); ?>

               <div id='profile'>

                    <img src="<?php echo $user[0]->profile_image_url ?>" />

                    <p>Welcome<br /><a href='https://twitter.com/#!/<?php echo $user[0]->screen_name ?>'>@<?php echo $user[0]->screen_name ?></a></p>


               </div>

               <div id="add-new" class='action'><a >Add New</a></div>

               <div id='see-tweets' class='action'><a >See Some Tweets</a></div>


          </header>

          <section id="side-panel">

               <ul id='artist-list'>

               </ul>

          </section>

          <section id='content-panel'>

               <div id='add-new'>

                    <div class='error'>

                         <p></p>

                    </div>


                    <form>

                         <label for='artist-txt'>Add an artist to your feed</label>

                         <input type='text' name='artist-query' id='artist-query' />

                         <input type='submit' id='artist-lookup' value="Add Artist" />



                    </form>


               </div>


               <div id='tweets'>

                    <ul id='tweet-list'>

                    </ul>

               </div>


          </section>

     </div>

     <script type='text/template' id='artist-item'>

          <div class="thumb"><img src="<%= profile_image_url %>" /></div>

          <div class="content"><p><%= name %><br /><a class="action remove">X Clear</a></p></div>
          
     </script>

     <script type='text/template' id='tweet-item'>
          
          <div class="profile"><img src="<%= profile_image_url %>" /><p><%= name %></p></div>

     </script>

</body>

</html>
