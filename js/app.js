$(document).ready(function() { 

    
    
    
    var echo_nest_key = 'WG7HBALBAFPSGYB2N';
    
    var echoNestModel = Backbone.Model.extend({
        
        url : 'http://developer.echonest.com/api/v4/artist/twitter' , 
        
        parse : function(response) { 
        
            return response.response; 
            
    
        }
        
    })
    
    
    var artistModel = Backbone.Model.extend({
        
        url : 'https://api.twitter.com/1/users/lookup.json', 
        
        parse : function(response) {
            
            return response[0];
            
        }

        
        
    }); 
    
    var artistCollection = Backbone.Collection.extend({
        
       
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
            
            //alert("clear"); 
        
            this.model.destroy();
            
        }
        
        
        
        
    }); 
    
    
    
    var artistList = Backbone.View.extend({
        
        el : $('#artist-list'), 
        
        initialize : function() { 
            
            //console.log(artists); 
        
            artists.bind("add" , this.addItem , this)
        
        }, 
        
        addItem : function(artist) { 
            
            var view = new artistItem({model: artist}); 
            
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
            
            var twitter_handle = artist_obj.twitter; 
            
            this.lookup.destroy(); 
            
            var new_artist = new artistModel(); 
            
            new_artist.fetch({
                
                dataType: 'jsonp' ,
                
                data : {
                    
                    screen_name : twitter_handle
                    
                } , 
                
                success : function() { 
                
                    artists.add(new_artist);
                
                }
                
            }); 
            
             
            
            //console.log(artists); 
            
        
        }
        
       


    });
    
    var list = new artistList(); 
    
    
    var add = new addArtist(); 
    
    

}); 

