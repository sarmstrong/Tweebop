<!DOCTYPE html>
<html>
     <head>
          <title></title>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

          <link href="stylesheets/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
          
          <link href="stylesheets/fileuploader.css" media="screen, projection" rel="stylesheet" type="text/css" />

          <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>

          <script type='text/javascript' src="js/jquery" /></script>

     <script type='text/javascript' src='js/underscore-min.js'></script>

     <script type='text/javascript' src='js/backbone-min.js'></script>

     <script type='text/javascript' src='js/backbone.localStorage-min.js'></script>

     <script type="text/javascript" src='js/fileuploader'></script>

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

               <div id="add-new-button" class='action panel-button' rel="#add-new" ><a>Add New</a></div>

               <div id='add-itunes-library' class='action panel-button' rel='#library-upload'><a >Upload iTunes Library</a></div>

               <div id='see-tweets-button' class='action panel-button' rel='#twitter-feed'><a >See Some Tweets</a></div>

               <div id='logout' class='action'><a href="<?php echo base_url() ?>index.php/welcome/logout">Log Out</a></div>

               <div id='tweet-filter' class="action"  >

                    <input type="text" id="tweet-filter-query" value="" />

                    <input type='button' class="clear" value="Clear" />

               </div>

               <div id='list-count' class="action"  >

                    <p>Loaded: <span> </span></p>

               </div>


          </header>

          <section id="side-panel">

               <div id='artist-search'>

                    <input type="text" id="artists-search-query" val="" />


               </div>

               <ul id='artist-list'>

               </ul>

          </section>

          <section id='content-panel'>

               <!-- Add New Form -->

               <div id='add-new' class="panel" >


                    <div class='error'>

                         <p> </p>

                    </div>

                    <div class='success'>

                         <p> </p>

                    </div>

                    <form>

                         <label for='artist-txt'>Add an artist to your feed</label>

                         <input type='text' name='artist-query' id='artist-query' />

                         <input type='submit' id='artist-lookup' value="Add Artist" />



                    </form>

                    


               </div>

               <!-- Library Upload -->

               <div id="library-upload" class="panel" >
                    
                    <div class='error'>

                         <p> </p>

                    </div>
                    
                    <div id="file-uploader">
                          
                         <noscript>
                         
                              <p>Please enable JavaScript to use file uploader.</p>
                              
                              <!-- or put a simple form for upload here -->
                              
                         </noscript>  
                         
                    </div>
                    
                    <div id='results'>
                         
                         <h3>Library Progress</h3>
                         
                         <div class="found">
                              
                              <h4>Added</h4>
                              
                              <ul>
                                   
                              </ul>
                              
                         </div>
                         
                         <div class="not-found">
                              
                              <h5>Not-Added</h5>
                              
                              <ul>
                                   
                                   
                              </ul>
                              
                         </div> 

                    </div>

               </div>
               
               <!-- Twitter Feed -->

               <div id='twitter-feed' class="panel" >

                    <div class="error">


                    </div>

                    <div id='tweets'>


                    </div>

                    <div id="next">

                         <h4><a>Load More</a</h4>

                    </div>



               </div>



          </section>

     </div>

     <script type='text/template' id='artist-item'>

          <div class="thumb"><a class='toggle-tweets' ><img src="<%= profile_image_url %>" /></a></div>

          <div class="content"><a class='toggle-tweets' ><%= name %></a><br /><a class="action remove">X Clear</a></div>

     </script>

     <script type='text/template' id='tweet-item'>

          <% 

          var m = Date.parse(created_at)

          var d = new Date(m) 

          %>

          <% 

          var txt = text 

          var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig

          var tweet_msg =  txt.replace(exp,"<a target='_blank' href='$1'>$1</a>") 

          %>


          <div class="profile"><img src="<%= user.profile_image_url %>" /><p><%= user.name %></p></div>

          <div class='tweet-body'><p><%= tweet_msg %></p><span> <%= d.getMonth()+1 + "/" + d.getDate() + "/" + d.getFullYear() %></div>

     </script>

     <input name="twitter-handle" id="twitter-handle" type="hidden" value="<?php echo $user[0]->screen_name ?>" />

</body>

</html>

