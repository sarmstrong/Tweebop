

          <%
          
          /*var img_url;

          if (typeof entities.media !== 'undefined') {

               img_url = entities.media[0].media_url;
               
               //console.log(img_url);

          }

          if (entities.urls.length > 0 ) {

               img_url = entities.urls[0].expanded_url;
               
               //console.log(img_url);

          }*/
          
          

          %>

          <% //if ( typeof img_url !== 'undefined' ) { %>

          

               <div id="myModal_<%= id_str %>" class="reveal-modal">
               
               <img src="<%= img_url %>" />
               
               <a class="close-reveal-modal">&#215;</a>
               
          </div>
          
          <a href="#" class="button" data-reveal-id="myModal_<%= id_str %>">Click Me For A Modal</a>

          <% //} %>