/**
 * AutoAjax WP Plugin
 *
 * @author Michael Rosata michael.rosata@gmail.com
 * @website http://onethingsimple.com
 * @version 0.1.3
 */
jQuery(function ($) {
  /**
   * OPTIONS, localized from WP Options
   */
  var options = autoAjaxConfigObject.options;

  /**
   * autoAjax Object
   *
   * This object manages turning WP sites into One Page Applications.
   *
   * @type {{
   * showLoading: bool, 
   * advBubbleQ: bool, 
   * updateBrowserUrl: bool,
   * advFallback: bool, 
   * advLoadDiv: bool, 
   * advMenuDiv: string, 
   * autoAjaxLvl: *, 
   * defaultDiv: *, 
   * customPrep: string, 
   * autoCache: null, 
   * debug: null, 
   * stylesheets: Array, 
   * headScripts: Array, 
   * footScripts: Array, 
   * init: Function, 
   * prepUrls: Function,
   * getContentDivSelector: Function,
   * getFallbackSelector: Function,
   * setUpPopstate: Function,
   * setUpEvents: Function, 
   * makeAjaxRequest: Function, 
   * loadResults: Function, 
   * loadPageNormal: Function, 
   * customMeyimFunc: Function
   * }}
   */
  var autoAjax = {

    // WP Dashboard Auto Ajax User Settings
    showLoading : strToBool(options['show-loading']),
    advBubbleQ  : strToBool(options['adv-bubble-query']),
    updateBrowserUrl: options['update-browser-url'],
    advFallback : options['adv-fallback-div'],
    advLoadDiv  : options['adv-load-div'],
    advMenuDiv  : options['adv-menu-div'],
    autoAjaxLvl : options['auto-ajax-level'],
    defaultDiv  : options['default-div'],

    reloadFooterScripts: true,
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
     * Get all Options
     */
    getOpts: function() {
      return {
        showLoading : this.showLoading,
        advBubbleQ  : this.advBubbleQ,
        updateBrowserUrl: this.updateBrowserUrl,
        advFallback : this.advFallback,
        advLoadDiv  : this.advLoadDiv,
        advMenuDiv  : this.advMenuDiv,
        autoAjaxLvl : this.autoAjaxLvl,
        defaultDiv  : this.defaultDiv,
        reloadFooterScripts: this.reloadFooterScripts,
      };
    },
    
    /**
     * Get a single option value
     */
    getOpt: function(optionName) {
      var opts = this.getOpts();
      return opts[optionName];
    },

    /**
     * Initiate the AutoAjax object, setup the page using plugin settings
     */
    init : function () {
      // figure out what selector we will use to identify links
      var selector = this.autoAjaxLvl == 'advanced' ? this.advMenuDiv : 'a';
      selector = selector == '' ? 'a' : selector;
      this.globalSelector = selector;
      // put data-auto-ajax-plugin="true" on links we want to ajax
      this.prepUrls(selector);
      this.setUpEvents();
      this.setUpPopstate();
    },


    appendLoading:function($elm){
       if ($elm && typeof $elm === "object")
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
      var self = this,
          blogUrl = self.locals.blogUrl;

      selector = selector || self.globalSelector;
      context = context || '';
      context = context.length > 0 ? context + ' ' : context;
      
      // Set up the links if we have the info needed
      if (blogUrl != '') {
        var $links = $( context + selector );
        
        $links.each(function (i, anchor) {
          var $anchor = $(this),
              url = $(this).attr('href');

          if (typeof url === "string" && url.indexOf(blogUrl) == 0) {
            // put the url into a data attribute to easily identify our links
            $anchor.attr('data-auto-ajax-plugin', url);
          }
        });
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
        self.url = $(this).data('auto-ajax-plugin');
        // Save reference to this link in the case of bubblequery
        self.refPointKey = 'auto-ajax-'+ autoAjaxRandomKey(6);
        $(this).addClass(self.refPointKey);
        // Get the level as set by user in Dashboard settings page
        self.settingsLevel = self.autoAjaxLvl.toLowerCase();
        // Figure out what selector/s we are going to use
        var content = self.getContentDivSelector();
        var fallback = self.getFallbackSelector();

        // Try to use Ajax to load a clicked link into the current page
        var attempt = self.makeAjaxRequest(self.url, self.loadResults, content, fallback);
        // Check if successful
        if (!attempt) {
          // The attempt was bad for some reason
          self.loadPageNormal(url);
        }
      });

      // Second, We need an event to listen for global Ajax Events
      $(document)
        .bind("ajaxComplete", function(){
          // Preps Urls on recently loaded content, adding data to elements
          self.prepUrls();
      });
    },


    getContentDivSelector: function () {
      return this.settingsLevel == 'basic' ? this.defaultDiv : this.advLoadDiv;
    },

    getFallbackSelector: function () {
      return this.settingsLevel == 'advanced' ? this.advFallback : '';
    },

    setUpPopstate: function() {
      if (!this.updateBrowserUrl) {
        return void 0;
      }
      var self = this;
      var content = self.getContentDivSelector();
      var fallback = self.getFallbackSelector();

      window.onpopstate = function(event) {
        // Try to use Ajax to load a clicked link into the current page
        self.url = document.location;
        var attempt = self.makeAjaxRequest(self.url, self.loadResults, content, fallback);
        if (!attempt) {
          window.loction = document.location;
        }
      };
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
      var self = this,
          $oldContent,
          refPointClass = '.' + self.refPointKey,
          $resScripts;
      
      self.url = url;

      $oldContent = $(container);
      if (self.getOpt('advBubbleQ') && refPointClass )  {
        $oldContent = $oldContent.has(refPointClass);
        if ((!$oldContent || $oldContent.length) && fallback) {
          // Try fallback, no need to check it for length though
          $oldContent = $(fallback).has(refPointClass);
        }
      }

      // Add empty loading div (which could be styled optionally)
      self.appendLoading($oldContent);

      // Let's make the Ajax Request
      $.ajax({
        url : self.url,
        type : 'GET',
        dataType : 'html',
        
        error: function() {
          $(document).trigger('error.wp-auto-ajax', arguments);
        },
        
        complete: function() {
          $(document).trigger('complete.wp-auto-ajax', arguments);
        },
        
        success : function (res) {
          // Get the new content and old content references
          var $res = $(res);
          var $resContent = $res.find( container );

          if (self.getOpt('reloadFooterScripts') && $res.length) {
            // Just overwrite reference to footerScripts array
            self.footerScripts = [];
            // Get all script tag data from the page we are about to load from
            // the Ajax response
            $res
              .filter(function(i, x) {
              return x.nodeType === 1 && x.nodeName === 'SCRIPT'
            })
              .filter(isWpThemeScript)
              .each(function (i, tag) {
                var $tag = $(tag);
                var src = $tag.prop('src');
                var type = $tag.prop('type');
                self.footerScripts.push({
                  src: src,
                  type: type ? type : 'text/javascript',
                });
              });
          }

          if (typeof $resContent !== "undefined" && $resContent.length && ($oldContent && $oldContent.length)) {
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
              return $(document).trigger('success.wp-auto-ajax', arguments);
            } 
            else {
              self.loadPageNormal(url)
            }

          } 
          else {
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
      // Args should both be jQuery objects.
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

      if (self.updateBrowserUrl) {
        self.updateHistoryUrl();
      }

      if (self.getOpt('reloadFooterScripts')) {
        self.reloadThemeFooterScripts();
      }
    },

    updateHistoryUrl: function() {
      if (!this.updateBrowserUrl && !history || typeof history !== "object" || typeof history.pushState !== "function")
        return void 0;
      history.pushState({}, '', this.url);
    },

    loadPageNormal : function ( url ) {
      // We will just load page normally since we weren't able to find the right content
      if (!this.debug) {
        window.location = url;
      }
    },

    reloadThemeFooterScripts: function () {
      var $body = $('body');
      var footerScripts = this.footerScripts || [];
      // Get a list of scripts to remove

      // Now add all the scripts back into the body
      while (footerScripts.length) {
        var next = footerScripts.shift();
        var $newScript = $('<script></script>');
        $newScript.prop('src', next.src);
        $newScript.prop('type', next.type);
        $body.append($newScript);
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

  function isWpThemeScript (index, tag) {
    var $tag = $(tag);
    var src = $tag && $tag.prop('src');
    if (!src || !src.match(/.*wp\-content\/themes\/.+/)){
      return false;
    }
    return true;
  }

});


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
 * Convert a string representation of a true or false,
 * IE: 'true|false', 'yes|no', 'y|n', 'on|off', '1|0'
 * into an actual boolean.
 */
function strToBool(str = '') {
  if (typeof str === 'boolean')
    return str;

  switch ((''+str+'').toLowerCase()){
    case 'true':
    case 'yes':
    case 'on':
    case 'y':
    case '1':
      return true;
    default:
      return false;
  }
}
