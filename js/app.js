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
        
        url : 'index.php/store/fetch/echo/' ,
       
        model :  artistModel , 
        
        cursor : -1, 
        
        search_filter : '',
        
        initialize : function() {
            
            this.on("add" , function(item , collection , options) {
            
                if (this.search_filter !== '' && this.search_filter !== undefined) {
                    
                    filter.single(item , this.search_filter , 'name'); 
                    
                } 
                
                if (options.index == collection.length - 1) {
                    
                    var hide = this.where({
                        hidden : true
                    }); 
                    
                    if (hide.length === this.length) {
                        
                        $("#artist-error").fadeIn();
                    }
                    
                }
                
            });
            
        }, 
        
        parse: function(response) { 
                
            this.cursor = response.next_cursor;
            
            this.trigger("parse:success");
            
            return response.users;
        
        }
      
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
            
            this.filter(); 
            
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
                
                    item.set("active" , false);
                    
                }
            
            });
            
            m.set("active" , !m.get("active"));
            
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
            
            artists.bind('add' , this.addItem , this); 
            
            artists.bind('parse:success' , this.setUI , this);
            
            artists.bind('load:start' , this.loadStart , this);
            
            artists.bind('load:stop' , this.loadStop , this);
            
            $('#side-panel .load-more').hide(); 
            
            $('#side-panel .load-more').bind('click' , function() { 
            
                fetchArtists(); 
            
            })
            
            
            
            
            
        }, 
        
        loadStart : function() {
            
            $("#artist-error").hide(); 
            
            $('#side-panel .load-more .stopped').hide();
            
            $('#side-panel .load-more .start').fadeIn();
           
            
        },
        
        loadStop : function() {
           
            $('#side-panel .load-more .start').hide();
            
            $('#side-panel .load-more .stopped').fadeIn();
            
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
            
            
        
        } , 
        
        setUI : function() {
            
            if (artists.cursor != 0) {
                
                $('#side-panel .load-more').fadeIn(); 
                
            } else {
                
                $('#side-panel .load-more').fadeOut();
                
            }
        } , 
        
        clear: function () {
            
            this.$el.html("")
            
        }
        
        

        
        
    }); 
    
    var addArtist = Backbone.View.extend({
        
        
        el : $("#add-new"), 
        
        events : {
            
            "click #artist-lookup" : 'artistLookup'
            
        }, 
        
        initialize : function() { 
        
            this.input = $("#artist-query"); 
            
            $("#add-new .error").hide();
            
            $("#add-new .success").hide();
    
        }, 
        
        artistLookup : function(e) { 
            
            $("#add-new .error p").text("");
            
            $("#add-new .error").hide();
            
            $("#add-new .success p").text("");
            
            $("#add-new .success").hide();
            
            if (this.input.val() == '') return; 
            
            var new_artist = new artistModel(); 
            
            new_artist.save({
                
                'screen_name' : this.input.val()
                
            } , {
                
                success: function(model , response) {
                    
                    if (response.error !== undefined){
                
                        $("#add-new .error p").text(response.error);
                        
                        $("#add-new .error").fadeIn();
                        
                        model.destroy(); 
                
                    } else {
                        
                        
                        artists.add(new_artist , {
                            at : 0
                        });
                        
                        $("#add-new .success p").text('"' + new_artist.get('name') + '"' + " is now in you TweeBop list.");
                        
                        $("#add-new .success").fadeIn();
                
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
        
        url : 'index.php/store/fetch_timeline' ,
        
        model : tweet , 
        
        //page : 1 , 
        
        max_id : 0,
        
        state : 'artist', 
        
        active_artist : null , 
        
        search_filter : '', 
        
        loading : false, 
        
        initialize : function() { 
        
            this.on("reset" , function() { 
            
                this.max_id = 0; 
            
            }); 
            
            this.on("add" , function(item , collection , options) { 
                
                if (this.search_filter !== '' && this.search_filter !== undefined) {
                    
                    var f = filter.single(item , this.search_filter , 'text'); 
                    
                }
                
                $('#list-count span').text(this.length);
                
                if (options.index == collection.length - 1) {
                    
                    var hide = this.where({
                        hidden : true
                    }); 
                    
                    if (hide.length === this.length) {
                        
                        $("#twitter-feed .error").fadeIn();
                    }
                    
                }

            })
            
        
        }, 
        
        parse : function(response) {
            
            if (response.length === 0 ) {
               
                window.scrollTo(0, 0);
              
                this.trigger("error");
              
                this.trigger("load:stop"); 
               
            }
            
            
            var last = _.last(response);
               
            this.max_id = last.id_str;
           
            return response;
            
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
           
            var obj = this;
                
            this.fetch({
                    
                add: true , 

                //dataType: 'jsonp',
                
                beforeSend : function() {
                
                    obj.trigger("load:start");
                    
                } , 

                data : {
                    
                    screen_name : this.active_artist , 
                    
                    max_id : this.max_id ,
                    
                    type : this.state
                } , 
                
                complete : function() {
                    
                    obj.trigger("load:stop");
                    
                }
                    
            })
    
        } , 
        
        getListTimeline : function(caller) { 
            
            this.state = 'tweets'; 
            
            if (caller !== 'page') {
                
                this.reset();
                
            } 
            
            var handle = $("#twitter-handle").val(); 
            
            var obj = this; 
                
            this.fetch({
                    
                add: true , 
                    
                //dataType: 'jsonp',
                
                timeout : 5000,
                
                beforeSend : function() {
                
                    obj.trigger("load:start");
                    
                    obj.loading = true;
                    
                } , 
                    
                data : {
                    
                    slug : handle + "-tweebop" , 
                    
                    owner_screen_name : handle , 
                    
                    max_id : this.max_id  , 
                    
                    type : this.state
                    
                } , 
                
                complete : function(jqXHR , textStatus) {
                    
                    obj.trigger("load:stop");
                    
                    var t = setTimeout(function() {
                        obj.loading = false
                    } , 1500);
                    
                } , 
                
                error : function (jqXHR, textStatus, errorThrown) {
                    
                    obj.trigger("error"); 
                    
                } 
                    
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
            
            $("#twitter-feed .error").hide();
            
            tweets.bind('add' , this.addItem , this); 
            
            tweets.bind('reset' , this.clearAll , this); 
            
            tweets.bind("load:start", this.loadStart , this); 
            
            tweets.bind("load:stop" , this.loadStop , this); 
            
            tweets.bind("error" , this.error , this);
            
        ///Disabled to keep from going over rate limit
            
        //$(window).bind('scroll' , $.proxy(this , 'autoLoad')); 
        
        }, 
        
        /*
         * Might bring back if rate limit increases
         */
        
        //        autoLoad : function () { 
        //         
        //            if ($("#next").offset().top + 20 < $(window).height() + $(window).scrollTop() ) {
        //                
        //                //console.log("true");
        //                
        //                if (tweets.loading !== true) {
        //                    
        //                    tweets.next();
        //                    
        //                    
        //                }
        //                
        //            }
        //        
        //        }, 
        error : function() { 
        
            $("#twitter-feed .error p").text("Seems Twitter has stopped working on us. We're probably using it one to many times or it's not paging correctly. Try back in an hour. Thanks!");
            
            $("#twitter-feed .error").fadeIn();
            
        
        },
        
        loadStart : function () { 
            
            $("#twitter-feed .error").hide();
        
            $('#twitter-feed #next .stopped').hide();
            
            $('#twitter-feed #next .start').fadeIn();
            
        }, 
        
        loadStop : function () { 
        
            $('#twitter-feed #next .start').hide();
            
            $('#twitter-feed #next .stopped').fadeIn();
        
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
    
    var echoNestLookup = Backbone.Model.extend({
        
        ///urlRoot : 
        
        })
    
    var itunesUpload = Backbone.View.extend({
        
        el : $("#library-upload") , 
        
        queue : null,
        
        current_list : null, 
        
        current_item : 0,
        
        initialize : function() {  
            
            this.$el.find('#results').hide(); 
            
            this.$el.find('.error').hide();

        } , 
        
        /// SCOPE FOR UPLOADER PROPERTIES ARE IN THE UPLOADER SCOPE
        
        updateResults : function (json) {
            
            this.queue = json.artists;
            
            this.current_list = [];
            
            this.fetchArtist(this.current_item);
            
            $("#results").fadeIn(); 
            
        } , 
        
        fetchArtist : function (num) {
            
            var obj = this;
            
            $("#processing").text("Looking up " + this.queue[num]);
            
            $.ajax({
                
                type : 'POST', 
                
                url : 'index.php/store/echo_nest_lookup', 
                
                data : {
                    
                    artist : this.queue[num]
                }, 
                
                dataType: 'json',
                
                success : function (data, textStatus, jqXHR) {
                    
                    obj.processLookup(data);
                    
                } 
                
                
                
            })
            
        } , 
        
        processLookup : function(data) {
            
            if (data.success === false) {
                
                if (data.error_code === 3) {
                    
                    this.systemTimeout();
                    
                    return false;
                    
                } else if (data.error_code === 5) {
                    
                    $(".not-found ul").append("<li>We couldn't find <strong>" + data.artist_lookup + "</strong>.</li>").fadeIn(); 
                    
                } else {
                    
                    $(".not-found ul").append("<li>We couldn't find a twitter handle for <strong>" + data.artist_lookup + "</strong>!</li>").fadeIn();
                    
                }
                
                
            } else {
                
                if ($.inArray(data.screen_name , this.current_list) === -1) {
                    
                    console.log(data.screen_name); 
                    
                    this.current_list.push(data.screen_name);
                    
                }
                
                $(".found ul").append("<li><strong>" + data.artist_lookup + "</strong> @" + data.screen_name + "</li>").fadeIn();
                
            }
            
            this.current_item++; 
            
            if (this.current_item < this.queue.length) {
                
                this.fetchArtist(this.current_item);
                
            } else {
                
                $("#processing").text("Adding to list . . .");
                
                this.updateTwitterList(); 
                
            }
    
        } , 
        
        updateTwitterList : function () { 
            
            var obj = this; 
        
            $.ajax({
                
                type : "POST", 
                
                url : "index.php/store/batch_twitter_list_add", 
                
                data : {
                    
                    list : this.current_list.join(',')
                    
                } , 
                
                dataType: 'json', 
                
                success : function (data) { 
                
                    obj.fetchList(data); 
                
                } 
                
            })
        
        } , 
        
        
        fetchList : function(json) {
            
            if (json.twitter_success === true) {
                
                list.clear();
                
                $("#processing").text("Finished!!!"); 
                
                artists.cursor = -1;
                
                artists.reset(); 
                
                fetchArtists();  
                
            } else {
                
                this.$el.find('.error').html(json.twitter_error);
                
            }
    
        }, 
        
        systemTimeout : function() {
    
            var t = setInterval(timer , 1000);
    
            var inc = 61; 
            
            var obj = this;
            
            $("#processing").html("We experiencing a time-out. Wait <span class='time'><span id='seconds'>60</span> seconds</span>");
  
            function timer() {
      
                inc--; 
      
                if (inc >= 0 ) {
                    
                    $("#seconds").text(inc); 
        
        
                } else {
       
                    clearInterval(t);
                    
                    obj.fetchArtist(obj.current_item);
        
                }
      
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
            
            itunes_upload.$el.find('.error p').text(message); 
            
            itunes_upload.$el.find('.error').fadeIn();
            
        
        }, 

        onSubmit : function (id , filename) { 
            
            itunes_upload.$el.find('.error p').text(''); 
            
            itunes_upload.$el.find('.error').hide();
            
            $(".not-found ul").html('');
            
            $('.found ul').html('');
            
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
            
            this.error = $("#artist-error");
            
            this.error.hide();
        
    
        }, 
        
        events : {
            
            'keypress input' : 'filterArtists', 
            
            'click input.clear' : 'clearFilter'
            
        } , 
        
        filterArtists : function(e) { 
            
            if (e.keyCode !== 13) return;
            
            var count = filter.group(artists , this.input.val() , 'name'); 
            
            artists.search_filter = this.input.val();
            
            if (count === 0) {
                
                this.error.html("<p> Cannot find any results for <strong>'" + this.input.val() + "'</strong>. <em>(Filters are left on till you clear them)!</em></p> "); 
                
                this.error.show();
                
            } else {
                
                this.error.hide();               
                
            }
            
        }, 
        
        clearFilter : function() {
        
            this.input.val(''); 
            
            artists.search_filter = this.input.val(); 
            
            this.error.hide();
            
            filter.group(artists , '' , 'name'); 
            
        
        }
        
    })
    
    var tweetSearch = Backbone.View.extend({
        
        el : $("#tweet-filter") , 
        
        initialize : function() { 
        
            this.input = $("#tweet-filter-query"); 
           
            this.error = $("#twitter-feed .error"); 
           
           
        }, 
        
        events : {
            
            'keypress input' : 'filterTweets' ,
            
            'click input.clear' : 'clearFilter'
            
        } , 
        
        filterTweets : function(e) {
            
            if (e.keyCode != 13) return;
            
            var count = filter.group(tweets, this.input.val() , 'text');
            
            tweets.search_filter = this.input.val(); 
            
            if (count === 0) {
                
                this.error.find('p').html("Cannot find any results for <strong>'" + this.input.val() + "'</strong>.<br /><em>(Filters are left on till you clear them)!</em> "); 
                
                this.error.show();
                
            } else {
                
                this.error.hide();               
                
            }
            
        }, 
        
        clearFilter : function() {
            
        
            this.input.val(''); 
            
            this.error.hide();
            
            tweets.search_filter = this.input.val(); 
            
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

            var match = item.get(match_field).match(exp); 

            if (match !== null ) {

                item.set({
                    hidden: false
                });
                
                return 1;  

            } else {

                item.set({
                    hidden: true
                });
                
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
    
    
    $(".inner-panel").hide();   
    
    $(".panel-button").each(function() { 
    
        $(this).bind("click" , function() { 
        
            var panel = $(this).attr('rel'); 
            
            $(".inner-panel").hide(); 
            
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
    
    
    fetchArtists(true); 
    
    function fetchArtists(refresh) { 
        
        artists.fetch({
        
            add: true  ,
        
            data : {
                
                cursor : artists.cursor
            }, 
            
            beforeSend : function () {
               
                artists.trigger("load:start");
                
            }, 
    
            success: function() {
            
                if (refresh !== false && refresh !== undefined) {
                    
                    if (artists.length > 0) { 

                        $("#twitter-feed").fadeIn(); 

                        tweets.getListTimeline();  


                    } else {

                        $("#add-new").fadeIn(); 
                        
                    }
                    
                    app_init = true;
                    
                } 
                
            }, 
            
            complete : function () {
                
                artists.trigger("load:stop");
                
            }

        }); 

    
    }
    
});