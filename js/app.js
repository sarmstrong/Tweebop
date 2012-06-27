$(document).ready(function() { 
    
    
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
        
        addItem : function(artist , collection , options) {
            
            console.log(options);
            
            console.log(artist);
            
            var view = new artistItem({
                
                model: artist
                
            }); 
            
            if (options.at === 0) {
               
               this.$el.prepend(view.render().el);
                
            } else {
                
                this.$el.append(view.render().el);
                
            }
            
            
        
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
            
            $("#add-new .success p").text(""); 
            
            if (this.input.val() == '') return; 
            
            var new_artist = new artistModel(); 
            
            new_artist.save({
                
                'screen_name' : this.input.val()
                
            } , {
                
                success: function(model , response) {
                    
                    if (response.error !== undefined){
                
                        $("#add-new .error p").text(response.error);
                        
                        model.destroy(); 
                
                    } else {
                        
                        //console.log(response); 
                        
                        artists.add(new_artist , {at : 0});
                        
                        $("#add-new .success p").text('"' + new_artist.get('name') + '"' + " is now in you TweeBop list.");
                        
                
                    }
                
                }
                
            });   
            
            
            return false; 
            
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
    
    var tweetCollection = Backbone.Collection.extend({
        
        url : 'https://api.twitter.com/1/lists/statuses.json' ,
        
        model : tweet
        
    })
    
    var tweets = new tweetCollection(); 
    
    
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
            
            //console.log(options); 
        
            var view = new tweetItem({
                
                model : tweet
                
            })
            
            
            
            this.$el.prepend(view.render().el)
        
        }
        
        
    })
    
    
    var list = new artistList(); 
    
    var add = new addArtist(); 
    
    $("#tweets").hide();   
    
    $("#add-new").hide(); 
    
    $("#add-new-button").bind("click" , function() { 
        
        //console.log("add"); 
        
        $("#tweets").hide();
    
        $("#add-new").fadeIn(); 

    })
    
    
    
    artists.fetch({
        
        add: true  , 
    
        success: function() { 
            
            if (artists.length > 0) {
                
                $("#tweets").fadeIn(); 
                
                var handle = $("twitter-handle").val(); 
                
                tweets.fetch({
                    
                    data : {slug : handle + "-tweebop" , owner_screen_name : handle}
                    
                })
                
            } else {
                
                $("#add-new").fadeIn(); 
                
            }
            
        
        }
        
    }); 
    
    
    
    
    
    

}); 

