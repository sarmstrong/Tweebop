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

     </body>

</html>
