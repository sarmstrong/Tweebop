

<header >

     <div class="row">

          <div id='logo' class='four columns'>

               <img src="images/logo.png" alt="Tweebop" />

          </div>


          <div class='eight columns'>

               <div id="add-new-button" class='three columns action panel-button' rel="#add-new" ><a>Add New</a></div>

               <div id='add-itunes-library' class='three columns  action panel-button' rel='#library-upload'><a >Upload iTunes</a></div>

               <div id='see-tweets-button' class='three columns action panel-button' rel='#twitter-feed'><a >See Tweets</a></div>

               <div id='logout' class='three columns  action'><a href="<?php echo base_url() ?>index.php/welcome/logout">Log Out</a></div>

          </div>

     </div>

</header>

<div class='row'>

     <section id="side-panel" class='four columns '>

          <div  class=" panel " id='profile'>

               <div class="user-data">

                    <img src="<?php echo $user[0]->profile_image_url ?>" />

                    <h5>Welcome<br /><a href='https://twitter.com/#!/<?php echo $user[0]->screen_name ?>'>@<?php echo $user[0]->screen_name ?></a></h5>

               </div>



          </div>


          <div class="panel">

               <div class="full-width" id='artist-search'>

                    <input class="eight columns"placeholder="Filter Artists . . . "type="text" id="artists-search-query" val="" />

                    <input class="four columns postfix button clear" type='button'  value="Clear" />

                    <div id="artist-error" class="twelve columns">     

                         <br /><p></p>

                    </div>



               </div>



               <div class="full clearfix">

                    <ul  id='artist-list'>

                    </ul>

               </div>

               <br clear="all" />

               <div  class="panel load-more">

                    <a class="stopped">Load More</a>

                    <a class="start">Loading</a>

               </div>


          </div>



     </section>

     <section id='content-panel' class='eight columns'>

          <!-- Add New Form -->

          <div id='add-new' class="row inner-panel" >

               <form class="columns ten">

                    <h4>Add an artist to your feed</h4>

                    <div class='error alert-box alert'>

                         <p> </p>

                    </div>

                    <div class='success alert-box success'>

                         <p> </p>

                    </div>

                    <input placeholder="Artist Name"  class="columns nine" type='text' name='artist-query' id='artist-query' />

                    <input class="columns three button postfix" type='submit' id='artist-lookup' value="Add Artist" />



               </form>




          </div>

          <!-- Library Upload -->

          <div id="library-upload" class="inner-panel" >




               <div class="columns twelve">

                    <h4>Upload an iTunes Library (Beta)</h4>

                    <h6>Disclaimer</h6>

                    <ul class="standard" >

                         <li>Do the the limitations of the third party service, the app may timeout before completing your list. </li>

                         <li>Some artists may have a Twitter account but cannot be verified</li>

                    </ul>
               </div>



               <div class="columns twelve">

                    <p>&nbsp;</p>

                    <h4>Let's Get Started</h4>

                    <p>Use the button below to browse to and upload your iTunes library</p>

                    <h6>Tip: Find your iTunes library at the following locations.</h6>
                    <ul class="standard" >
                         <li><strong>Mac OS X:</strong>  /Users/username/Music/iTunes/iTunes Music Library.xml</li>
                         <li><strong>Windows XP:</strong>  C:\Documents and Settings\username\My Documents\My Music\iTunes\iTunes Music Library.xml</li>
                         <li><strong>Windows Vista:</strong>  C:\Users\username\Music\iTunes\iTunes Music Library.xml</li>
                         <li><strong>Windows 7:</strong>  C:\Users\username\My Music\iTunes\iTunes Music Library.xml</li>

                    </ul>




                    <div class='error alert-box alert'>

                         <p> </p>

                    </div>

                    <div id="file-uploader">

                         <noscript>

                         <p>Please enable JavaScript to use file uploader.</p>

                         <!-- or put a simple form for upload here -->

                         </noscript>  

                    </div>

               </div>







               <div id='results' style="display: block">


                    <div class="columns twelve">

                         <h4>Upload Results!</h4>  

                    </div>



                    <div class="found columns six">

                         <h6>Added</h6>

                         <ul class="standard">



                         </ul>



                    </div>

                    <div class="not-found columns six">

                         <h6>Not-Added</h6>

                         <ul class="standard">


                         </ul>

                    </div> 

               </div>

          </div>

          <!-- Twitter Feed -->

          <div id='twitter-feed' class="row inner-panel" >



               <div >

                    <div id='tweet-filter' class="ten columns action"  >

                         <input placeholder="Filter Tweets . . . "class="nine columns"type="text" id="tweet-filter-query" value="" />

                         <input class="three columns postfix button clear" type='button'  value="Clear" />

                    </div>

                    <div id='list-count' class="two columns action"  >

                         <p><span> </span><br />Loaded</p>

                    </div>


               </div>

               <div class='error twelve columns '>     

                    <div class="alert-box alert">

                         <p>


                         </p>

                    </div>

               </div>

               <div id='tweets'>


               </div>

               <div class="columns twelve" >

                    <div id="next">

                         <a class="stopped">Load More</a>

                         <a class="start">Loading</a>




                    </div>


               </div>



          </div>



     </section>

</div>

</div>

</div>


<script type='text/template' id='artist-item'>

     <div class="full-width user-data">

          <div class="highlight"></div>

          <a class='toggle-tweets' ><img src="<%= profile_image_url %>" /></a>

          <h6><a class='toggle-tweets' ><%= name %></a></h6><p><a class="action remove">X Clear</a></p>

     </div>

</script>

<script type='text/template' id='tweet-item'>

     <% 

     var m = Date.parse(created_at)

     var d = new Date(m) 

     var year = d.getFullYear(); 

     var year = year.toString().substr(2,2); 

     %>

     <% 

     var txt = text 

     var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig

     var tweet_msg =  txt.replace(exp,"<a target='_blank' href='$1'>$1</a>") 

     %>

     <div class="tweet-body user-data columns twelve">

          <img src="<%= user.profile_image_url %>" />

          <h5 class="user"><%= user.name %> <span><a href="https://twitter.com/<%= user.screen_name %>">@<%= user.screen_name %></a></span></h5><p class="date"><%= d.getMonth()+1 + "/" + d.getDate() + "/" + year %></p>

          <p class="content"><%= tweet_msg %></p> 


     </div>



</script>

<input name="twitter-handle" id="twitter-handle" type="hidden" value="<?php echo $user[0]->screen_name ?>" />

<?php include_once 'footer-include.php' ?>

</body>

</html>

