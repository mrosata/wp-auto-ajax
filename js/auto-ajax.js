/**
 * AutoAjax WP Plugin
 *
 * @author Michael Rosata mrosata1984@gmail.com
 * @website http://onethingsimple.com
 * @version 0.1.0
 */

jQuery(function ($) {

  /**
   * Just a simple random key generator. Used to create unique labels for elements
   * @param len
   * @param pool
   * @returns {string}
   */
  function autoAjaxRandomKey(len, pool) {
    len = len || 10;
    len = parseInt(len, 10);
    pool = pool || 'abcdefghijklmnopqrstuvwxyz1234567890';
    var pLen = pool.length;
    var i = 0;
    var output = '';
    for (i;i<len;i++) {
      output += pool[Math.floor( (Math.random()*100*pool.length) % (pLen-1), 10)].toString();
    }
    return output;
  }

  /**
   * OPTIONS, localized from WP Options
   */
  var options = autoAjaxConfigObject.options;

  /**
   * autoAjax Object
   *
   * This object manages turning WP sites into One Page Applications.
   *
   * @type {{showLoading: *, advBubbleQ: *, advFallback: *, advLoadDiv: *, advMenuDiv: *, autoAjaxLvl: *, defaultDiv: *, customPrep: string, autoCache: null, debug: null, stylesheets: Array, headScripts: Array, footScripts: Array, init: Function, prepUrls: Function, setUpEvents: Function, makeAjaxRequest: Function, loadResults: Function, loadPageNormal: Function, customMeyimFunc: Function}}
   */
  var autoAjax = {

    // WP Dashboard Auto Ajax User Settings
    showLoading : options['show-loading'],
    advBubbleQ  : options['adv-bubble-query'],
    advFallback : options['adv-fallback-div'],
    advLoadDiv  : options['adv-load-div'],
    advMenuDiv  : options['adv-menu-div'],
    autoAjaxLvl : options['auto-ajax-level'],
    defaultDiv  : options['default-div'],
    // Custom callback for Meyim
    customPrep  : 'customMeyimFunc',
    // May be used to cycle back 1 page
    autoCache   : null,
    // Turn on to prevent normal page loading on auto-ajax links
    debug       : null,

    // Keep a list of all stylesheets #Currently unused
    stylesheets : [],
    // Keep a list of all head scripts #Currently unused
    headScripts : [],
    // Keep a list of all foot scripts #Currently unused
    footScripts : [],

    /**
     * Initiate the AutoAjax object, setup the page using plugin settings
     */
    init : function () {
      // figure out what selector we will use to identify links
      var selector = this.autoAjaxLvl == 'advanced' ? this.advMenuDiv : 'a';
      selector = selector == '' ? 'a' : selector;
      // Save the selector so we can
      this.globalSelector = selector;
      // put data-auto-ajax-plugin="true" on links we want to ajax
      this.prepUrls(selector);
      // Setup the event handler
      this.setUpEvents();

      // Execute Meyim Site Specific code
      if (this) {
        this[this.customPrep]();
      }
    },
    appendLoading:function($elm){
        $elm.append('<div class="loading"></div>');
    },
    removeLoading : function($elm){
        $elm.find('.loading').remove();
    },
    /**
     * Prepare urls to be used with AutoAjax. This function is called initially and then also called
     * again to work on any dynamically loaded content. We also listen for global events to make sure
     * to prep that content for use as well.
     * @param selector   css selector of links we will use with AutoAjax
     * @param context    (optional) css selector of the container, 'body' or new elm with Ajax content
     */
    prepUrls : function (selector, context) {
      var self = this;
      // Prep the arguments, selector identifies links to make ajax
      selector = selector || self.globalSelector;
      // Context localizes the area to look for links so we can more efficiently target changed areas
      context = context || '';
      context = context.length > 0 ? context + ' ' : context;
      // Get the WP website url, so we know what is local and what is not
      var blogUrl = self.locals.blogUrl;
      // Set up the links if we have the info needed
      if (blogUrl != '') {
        var links = $( context + selector );

        links.each(function (i, anchor) {
          var $anchor = $(this);
          // Get the url
          var url = $(this).attr('href');
          // Check if url is an onsite url
          if (url.indexOf(blogUrl) == 0) {
            // put the url into a data attribute to easily identify our links
            $anchor.attr('data-auto-ajax-plugin', url);
          }
        })
      }
    },

    /**
     * Sets up the link click handler for AutoAjax links using selector provided by the
     * user in dashboard settings. Also sets up Ajax global event listeners if needed.
     */
    setUpEvents : function () {
      var self = this;
      // First we need the event to listen for links being clicked
      $('body').on('click.autoajax', 'a[data-auto-ajax-plugin]', function (e) {
        e.preventDefault();
        var url = $(this).data('auto-ajax-plugin');
        // Save reference to this link in the case of bubblequery
        self.refPointKey = 'auto-ajax-'+ autoAjaxRandomKey(6);
        $(this).addClass(self.refPointKey);
        // Get the level as set by user in Dashboard settings page
        var level = self.autoAjaxLvl.toLowerCase();
        // Figure out what selector/s we are going to use
        var content = level == 'basic' ? self.defaultDiv : self.advLoadDiv;
        var fallback = level == 'advanced' ? self.advFallback : '';

        // Try to use Ajax to load a clicked link into the current page
        var attempt = self.makeAjaxRequest(url, self.loadResults, content, fallback);
        // Check if successful
        if (!attempt) {
          // The attempt was bad for some reason
          self.loadPageNormal(url);
        }
      });

      // Second, We need an event to listen for global Ajax Events
      $(document).bind("ajaxSend", function(){

      }).bind("ajaxComplete", function(){
        // Preps Urls on recently loaded content, adding data to elements
        self.prepUrls();
      });
    },

    /**
     * Make an Ajax request on AutoAjax links, check page, prep the results
     * @param url          The url to be loaded
     * @param callback     The callback to handle the response if successful
     * @param container    The container to look for inside the response, css selector
     * @param fallback     A fallback container, css selector
     * @returns {boolean}  Returns true if request is made, false if not enough args passed
     */
    makeAjaxRequest : function (url, callback, container, fallback) {
      if (arguments.length < 4) {
        // not enough args to complete request
        return false;
      }
      var self = this;
      var $oldContent;

      // Perform Bubble Query Attempt
      var refPointClass = '.' + self.refPointKey;
      if ((self.advBubbleQ == 'true') && refPointClass )  {
        $oldContent = $( container).has(refPointClass);
        if (!$oldContent.length && fallback != '') {
          // Try fallback, no need to check since nothing else we could do
          $oldContent = $(fallback).has(refPointClass);
        }
      }

      // Add empty loading div (which could be styled optionally)
      self.appendLoading($oldContent);

      // Let's make the Ajax Request
      $.ajax({
        url : url,
        type : 'GET',
        dataType : 'html',
        success : function (res) {
          // Get the new content and old content references
          var $resContent = $(res).find( container );

          if ($resContent.length && $oldContent.length) {
            // We are in good shape to load content
            callback($resContent, $oldContent);
          } else if (fallback != ''){
            // One of our items doesn't have a length
            if (!$resContent.length) {
              $resContent = $(res).find(fallback);
            }
            if (!$oldContent.length) {
              $oldContent = $(fallback);
            }
            if ($resContent.length && $oldContent.length) {
              // Ok, we are ready to load the pages with a fallback on 1 or both docs!
              callback($resContent, $oldContent);
            } else {
              self.loadPageNormal(url)
            }
          } else {
            // We have no choice but to load as normal
            self.loadPageNormal(url);
          }
        }
      });

      return true;
    },

    /**
     * Loads the Container html from Ajax response into the container html on page
     * @param htmlFrag   jQuery object of the html fragment found in Ajax response
     * @param container  jQuery object of the page container to load htmlFrag into
     */
    loadResults : function ( htmlFrag, container ) {
      // Args should both be jQuery objects no worries.
      var self = autoAjax;
      if (self.autoCache) {
        // Store the prev page into cache
        self.prevContent = {
          container : container,
          html : container.html()
        }
      }
      container.html(htmlFrag.html());
      self.removeLoading(container);
    },

    loadPageNormal : function ( url ) {
      // We will just load page normally since we weren't able to find the right content
      if (!this.debug) {
        window.location = url;
      }
    },

    /**
     * This function reloads the prev page into container when a regular nav menu link is pressed
     * It is designed for Meyim, because their nav-menu loads and annotates pages into new containers
     * so the user can flip/slide through them like cards. By loading into their containers, we break
     * their annotation for the container we loaded into, making the related nav menu button appear
     * broken. This function is called from AutoAjax.init. It uses AutoAjax.autoCache
     * autoCache I may extend to use with plugin
     */
    customMeyimFunc : function () {
      var self = this;

      if ('meyimInit' in self) {
        if (self.autoCache && ('prevContent' in self)) {
          // Attempt to reload the prev content into div
          var prev = self.prevContent;
          prev.container.html( prev.html );
          self.removeLoading(prev.container);
        }
      } else {
        self.autoCache = true;
        self.meyimInit = true;
        $('#main-nav a').on('click.autoajax', function (evt) {
          self.customMeyimFunc();
        });
      }
    }

  };

  // set up the config vars for the autoAjax object
  autoAjax.locals = $.extend(autoAjax.locals, window.autoAjaxConfigObject);
  // initialize the autoAjax object
  autoAjax.init($);

});