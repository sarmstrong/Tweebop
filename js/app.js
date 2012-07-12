$(document).ready(function() { 
    
    /*
     * 
     * ARTIST OBJECTS
     * 
     */
    
    
    var artistModel = Backbone.Model.extend({
        
        defaults : function() { 
        
            return {
                
                active: false , 
                
                hidden: false
                
            }
        
        }, 
        
        urlRoot: 'index.php/store/artist' , 
        
        idAttribute : "screen_name"

        
    }); 
    
    var artistCollection = Backbone.Collection.extend({
        
        url : 'index.php/store/fetch' ,
        
       
        model :  artistModel
      
    }); 
    
    
    
    artists = new artistCollection(); 
    
    var artistItem = Backbone.View.extend({
        
        tagName : 'li' , 
        
        template : _.template($("#artist-item").html()) ,
        
        initialize : function() { 
        
            this.model.bind('destroy', this.remove, this);
            
            this.model.bind('change' , this.filter , this); 
    
        } , 
        
        events : {
            
            'click a.remove' : 'clear' , 
            
            'click a.toggle-tweets' : 'toggleTweets' 
            
            
        }, 
        
        render : function() {
            
            this.$el.html(this.template(this.model.toJSON())); 
            
            return this;
            
        } , 
        
        filter : function () {
            
            this.model.get('hidden') === true ? this.$el.hide() : this.$el.show();
        
        }, 
        
        clear : function() { 
            
            this.model.destroy();
            
        } , 
        
        toggleTweets : function() {
            
            var m = this.model; 
            
            artists.forEach(function(item) { 
                
                if (item.get('id') != m.get("id")) { 
                
                    item.save({active : false })
                    
                }
            
            });
            
            m.save({active : !this.model.get('active')}); 
            
            $("#artist-list li").removeClass('active'); 
            
            if (m.get('active') === true) { 
            
                this.$el.addClass("active");
                
                ///var screen_name = m.get('screen_name'); 
                
                tweets.active_artist = m.get('screen_name'); 
                
                tweets.getArtistTimeline();
            
            } else {
                
                this.$el.removeClass('active'); 
                
                tweets.getListTimeline();
                
            } 
            
            
        }
        
        
    }); 
    
    
    
    var artistList = Backbone.View.extend({
        
        el : $('#artist-list'), 
        
        initialize : function() { 
            
            artists.bind("add" , this.addItem , this); 
            
            
            
        }, 
        
        addItem : function(artist , collection , options) {
            
            
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
        
        defaults : function() { 
            
            return {
                
                hidden : false
                
            }
        
        }, 
        
        parse: function(response) { 
        
            return response; 
        
        }
        
    })
    
    var tweetCollection = Backbone.Collection.extend({
        
        url : 'https://api.twitter.com/1/lists/statuses.json' ,
        
        model : tweet , 
        
        initialize : function() { 
            
            _.extend({page  : 1} , {state : 'artist'} , {active_artist : null} , { search_filter : ''})
        
            this.on("reset" , function() { 
            
                this.page = 1; 
            
            }); 
            
            this.on("add" , function(item) { 
                
                if (this.search_filter !== '' && this.search_filter !== undefined) {
                    
                    filter.single(item , this.search_filter , 'text'); 
                    
                }
                
                $('#list-count span').text(this.length);

            })
           
        
        }, 
        
        
        next : function() { 
            
            this.page++; 
            
            if (this.state === 'artist') { 
            
                this.getArtistTimeline('page'); 
        
            } else if (this.state === 'tweets') {
                
                this.getListTimeline('page'); 
                
            }
        
        
        } , 
        
        getArtistTimeline : function(caller) { 
            
            this.state = 'artist';  
            
            if (caller !== 'page') {
                
                this.reset();
                
            }; 
            
            this.url = 'https://api.twitter.com/1/statuses/user_timeline.json'; 
                
            this.fetch({
                    
                add: true , 

                dataType: 'jsonp',

                data : {screen_name : this.active_artist , page : this.page} 
                    
            })
    
        } , 
        
        getListTimeline : function(caller) { 
            
            this.state = 'tweets'; 
            
            if (caller !== 'page') {
                
                this.reset();
                
            } 
            
            this.url = 'https://api.twitter.com/1/lists/statuses.json';
            
            var handle = $("#twitter-handle").val(); 
                
            this.fetch({
                    
                add: true , 
                    
                dataType: 'jsonp',
                    
                data : {slug : handle + "-tweebop" , owner_screen_name : handle , page : this.page}
                    
            })
        
    
        } 
        
        
        
    })
    
    tweets = new tweetCollection(); 
    
    
    var tweetItem = Backbone.View.extend({
        
        tagName : 'div', 
        
        template : _.template($("#tweet-item").html()), 
        
        initialize: function() { 
        
            this.model.bind('destroy' , this.remove , this) , 
            
            this.model.bind('change' , this.filter , this)
        
        }, 
        
        filter : function () {
            
            this.model.get('hidden') === true ? this.$el.hide() : this.$el.show();
        
        },
        
        
        render : function() { 
        
            this.$el.html(this.template(this.model.toJSON())); 
            
            this.filter(); 
            
            return this;
        
        }
        
    })
    
    var tweetList = Backbone.View.extend({
        
        el: $("#tweets"), 
        
        initialize: function() { 
            
            tweets.bind('add' , this.addItem , this); 
            
            tweets.bind('reset' , this.clearAll , this)
        
        }, 
        
        addItem : function(tweet) {
        
            var view = new tweetItem({
                
                model : tweet
                
            })
                
            this.$el.append(view.render().el);
            
            if (tweets.search_filter !== undefined && tweets.search_filter !== '') {
                
                if (tweet.get('hidden') === false) {
                    
                    $("#twitter-feed .error").hide(); 
                    
                }
                
            }
        
        } , 
        
        clearAll : function () { 
            
            this.$el.html(''); 

        } 
        
        
        
        
    })
    
    /*
     *
     * File Uploader
     *
     */
    
    var itunesUpload = Backbone.View.extend({
        
        el : $("#library-upload") , 
        
        
        
        initialize : function() {  
            
            this.$el.find('#results').hide(); 

        } , 
        
        /// SCOPE FOR UPLOADER PROPERTIES ARE IN THE UPLOADER SCOPE
        
        updateResults : function (json) { 
            
            console.log(json);
            
            if (json.twitter_success === true) {
                
                this.$el.find('#results').show();
                
                $(".not-found").append(json.not_found);
            
                $('.found').append(json.found);
                
                $("#artist_list").html(""); 
                
                fetchArtists();  
                
            } else {
                
                this.$el.find('.error').html(json.twitter_error);
                
            }

                      
        
        }  
        
        
    })
    
    var itunes_upload = new itunesUpload();
    
    /// Third Paty Uploader Object
    
    var file_uploader = new qq.FileUploader({
                
        element : $("#file-uploader")[0], 

        allowedExtensions : ['xml'], 

        action: 'index.php/store/library' ,

        //debug: true , 
        
        onComplete : function (id, filename , json) { 
            
            itunes_upload.updateResults(json);
            
        },  

        showMessage : function (message) { 
            
            
            itunes_upload.$el.find('.error').text(message); 
            
        
        }, 

        onSubmit : function (id , filename) { 
            
            itunes_upload.$el.find('.error').text(''); 
            
        }

    });
    
    
    /*
     * 
     * Search Components
     * 
     */
    
    
    var artistSearch = Backbone.View.extend({
        
        el : $("#artist-search") , 
        
        initialize : function() { 
        
           this.input = $("#artists-search-query"); 
        
    
        }, 
        
        events : {
            
            'keypress input' : 'searchArtists'
            
        } , 
        
        searchArtists : function(e) { 
            
            if (e.keyCode !== 13) return;
            
            filter.group(artists , this.input.val() , 'name'); 
            
        }
        
    })
    
    var tweetSearch = Backbone.View.extend({
        
        el : $("#tweet-filter") , 
        
        initialize : function() { 
        
           this.input = $("#tweet-filter-query"); 
           
           
           
        }, 
        
        events : {
            
            'keypress input' : 'filterTweets' ,
            
            'click .clear' : 'clearFilter'
            
        } , 
        
        filterTweets : function(e) {
            
            if (e.keyCode != 13) return;
            
            var count = filter.group(tweets, this.input.val() , 'text');
            
            var error = $("#twitter-feed .error"); 
            
            tweets.search_filter = this.input.val(); 
            
            console.log(count); 

            if (count === 0) {
                
                error.html("<p> Cannot find any results for <strong>'" + this.input.val() + "'</strong>.<br /><em>(Filters are left on till you clear them)!</em></p> "); 
                
                error.show();
                
            } else {
                
                error.hide();               
                
            }
            
        }, 
        
        clearFilter : function() { 
        
            this.input.val(''); 
            
            filter.group(tweets , '' , 'text')
        
        }
        
    })
   
    
    /*
     *
     * Utils 
     * 
     */
    
    var filter = { 
    
        group : function(collection , search_string , match_field) {
            
            var count = 0; 
            
            var search = this; 
            
            collection.each( function(item) {
                
                var inc = search.single(item , search_string , match_field); 
                
                count += inc;  
                
            })
            
            return count; 
        
        } , 
        
        single : function(item , search_string , match_field) {
            
            var exp = new RegExp(search_string , 'ig'); 
            
            //console.log(item); 
            
            //console.log(search_string);

            var match = item.get(match_field).match(exp); 

            if (match !== null ) {

                item.set({hidden: false});
                
                return 1;  

            } else {

                item.set({hidden: true});
                
                return 0; 
                

            }
            
        }
        
    
    }
    
    
    /*
     * 
     * Create Initial View Object
     * 
     */
    
    var list = new artistList(); 
    
    var add = new addArtist(); 
    
    var tweet_list = new tweetList(); 
    
    var artist_search = new artistSearch();
    
    var tweet_search = new tweetSearch();
    
     
    
    //var count = new loadedTweets(); 
    
    /*
     * 
     * Basic UI Functionality
     * 
     * 
     */
    
    
    $(".panel").hide();   
    
    $(".panel-button").each(function() { 
    
        $(this).bind("click" , function() { 
        
            var panel = $(this).attr('rel'); 
            
            $(".panel").hide(); 
            
            $(panel).fadeIn(); 
            
        })
    
    }) 
    
    $("#next").bind("click" , function() { 
    
        tweets.next(); 

    })
    
    /*
     * 
     * Initial call to populate artist list and
     * Prompt to add artists (if artist list is empty)
     * or load tweets from list
     * 
     */
    
    
    fetchArtists(); 
    
    function fetchArtists() { 
        
        artists.fetch({
        
        add: true  , 
    
        success: function() { 
            
                if (artists.length > 0) {

                    $("#twitter-feed").fadeIn(); 

                    tweets.getListTimeline(); 


                } else {

                    $("#add-new").fadeIn(); 



                }


                }

        }); 

    
    }
    
    
    
   
    
    
    
    
    
    
    
    
    

}); 

