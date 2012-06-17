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

        <section id="side-panel">
            
            <ul id='artist-list'>
                
                

            </ul>
            
        </section>

        <section id='content-panel'>

            <div id='add-new'>


                <form>
                    
                    <label for='artist-txt'>Add an artist to your feed</label>
                    
                    <input type='text' name='artist-query' id='artist-query' />
                    
                    <input type='submit' id='artist-lookup' value="Add Artist" />

                </form>


            </div>

            <div id='confirm-artist'>


            </div>


        </section>

    </div>

<script type='text/template' id='artist-item'>
    
    <div class="thumb"><img src="<%= profile_image_url %>" /></div>
    
    <div class="content"><p><%= name %><br /><a class="action remove">X Clear</a></p></div>


</script>

<script type='text/template' id='artist-new'>


</script>

</body>

</html>
