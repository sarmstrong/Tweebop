$(document).ready(function() { 
    
    var echo_nest_key = 'WG7HBALBAFPSGYB2N';
    
    var echoNestModel = Backbone.Model.extend({
        
        url : 'http://developer.echonest.com/api/v4/artist/twitter' , 
        
        parse : function(response) { 
        
            return response.response; 
            
    
        }
        
    })
    
    
    var artistModel = Backbone.Model.extend({
        
        urlRoot: 'index.php/store/artist' , 
        
        idAttribute : "screen_name", 
        
        parse : function(response) {
                
            return response;
            
        }

        
        
    }); 
    
    var artistCollection = Backbone.Collection.extend({
        
        url : 'index.php/store/fetch' ,
        
       
        model :  artistModel
      
    })
    
   
    
    var artists = new artistCollection(); 
    
    var artistItem = Backbone.View.extend({
        
        tagName : 'li' , 
        
        template : _.template($("#artist-item").html()) ,
        
        initialize : function() { 
        
            this.model.bind('destroy', this.remove, this);
    
        } , 
        
        events : {
            
            'click a.remove' : 'clear' 
            
            
        }, 
        
        render : function() {
            
            this.$el.html(this.template(this.model.toJSON())); 
            
            return this;
            
        } , 
        
        clear : function() { 
            
            this.model.destroy();
            
        }
        
        
    }); 
    
    
    
    var artistList = Backbone.View.extend({
        
        el : $('#artist-list'), 
        
        initialize : function() { 
            
            artists.bind("add" , this.addItem , this)
            
        }, 
        
        addItem : function(artist) {
            
            var view = new artistItem({
                
                model: artist
                
            }); 
            
            this.$el.append(view.render().el);
        
        }

        
        
    }); 
    
    var addArtist = Backbone.View.extend({
        
        
        el : $("#add-new"), 
        
        events : {
            
            "click #artist-lookup" : 'artistLookup'
            
        }, 
        
        initialize : function() { 
        
            this.input = $("#artist-query"); 
    
        }, 
        
        artistLookup : function(e) { 
            
            $("#add-new .error p").text(""); 
            
            if (this.input.val() == '') return; 
            
            var echo_artist = new echoNestModel();
            
            echo_artist.on('change' , this.getTwitterArtist , this); 
            
            this.lookup = echo_artist; 
            
            echo_artist.fetch({
                
                dataType : 'jsonp' , 
                
                data : {
                    
                    name : this.input.val() , 
                    
                    api_key : echo_nest_key , 
                    
                    format : 'jsonp'
                }
                
            }); 
            
            
            return false; 
            
        }, 
        
        getTwitterArtist : function() { 
            
            var artist_obj = this.lookup.get('artist'); 
            
            //console.log(artist_obj);
            
            if (artist_obj === undefined) { 
            
                $("#add-new .error p").text("We couldn't find the twitter account for this artist. Sorry!");
                
                return false;
            
            } 
            
            var twitter_handle = artist_obj.twitter; 
            
            if (twitter_handle === undefined) {
                
                $("#add-new .error p").text("We couldn't find the twitter account for this artist. Sorry!");
                
                return false;
                
            }
            
            this.lookup.destroy(); 
            
            var new_artist = new artistModel(); 
            
            new_artist.save({
                
                screen_name : twitter_handle
            } , {
                
                success: function(model , response) {
                    
                    if (response.error !== undefined){
                
                        $("#add-new .error p").text(response.error);
                
                    } else {
                
                        artists.add(new_artist);
                
                    }
                
                }
                
            });  
            
        
        }
        
       


    });
    
    /*
     * 
     * TWITTER OBJECTS
     * 
     */
    
    var tweet = Backbone.Model.extend({
        
        parse: function() { 
        
            return response[0]; 
        
        }
        
    })
    
    var tweets = Backbone.Collection.extend({
        
        url : '' ,
        
        model : tweet
        
    })
    
    var tweetItem = Backbone.View.extend({
        
        tagName : 'li', 
        
        template : _.template($("#tweet-item").html()), 
        
        initialize: function() { 
        
            this.model.bind('destroy' , this.remove , this)
        
        }, 
        
        
        render : function() { 
        
            this.$el.html(this.template(this.model.toJSON())); 
        
        }
        
    })
    
    var tweetList = Backbone.View.extend({
        
        el: $("#tweet-list"), 
        
        initialize: function() { 
            
            tweets.bind('add' , this.addItem , this) 
        
        }, 
        
        addItem : function(tweet) { 
        
            var view = new tweetItem({
                
                model : tweet
                
            })
            
            this.$el.append(view.render().el)
        
        }
        
        
    })
    
    
    var list = new artistList(); 
    
    var add = new addArtist(); 
    
    $("#tweets").hide(); 
    
    $("#add-new").hide(); 
    
    artists.fetch({
        
        add: true  , 
    
        success: function() { 
        
            //console.log(artists.length)
            
            if (artists.length > 0) {
                
                $("#tweets").fadeIn(); 
                
            } else {
                
                $("#add-new").fadeIn(); 
                
            }
            
        
        }
        
    }); 
    
    
    
    
    
    

}); 

