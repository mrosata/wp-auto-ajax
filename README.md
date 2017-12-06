# wp-auto-ajax

WP Auto Ajax!
===================

> Update 2017-12-06
- Moved __Auto Ajax__ into the Admin "Settings" menu rather than "Tools". Plugins
which classify as "tools" perform a task and complete. __Auto Ajax__ seems more at
home in settings.
- Moved history input to top of settings page to make the __Auto Ajax__ settings easier to understand.
- Also, now settings not relative to the plugin setting level ('basic'/'advanced') will be disabled as well.
- Reloads all parent theme scripts in footer (removes old ones, then reappends
  them). This makes it so sliders and such will work again on ajax loaded pages.

> Update 2016-07-08
- Added custom events for `'complete.wp-auto-ajax'` and `'success.wp-auto-ajax'` and `'error.wp-auto-ajax'` so you may attach custom JavaScript logic to run at these times. See below for more info.

> Update 2016-07-07
- I've gone and fixed a few JavaScript errors that may or may not have been seen in some themes. A new History feature has been added which works regardless if you are in basic or advanced mode (you just need to check the checkbox on the settings page). The history feature will update the url after a successful Ajax page load and it will try to implement Ajax functionality when the user hits back/forward through their history. Just like with the normal settings, if the plugin can't successfully make and populate Ajax content into the page then it will fallback to regular page loads.



This plugin is in halted development. It worked on 2 themes that it was tested on and is in use on a production site at the moment. That being said, there are many features I would like to add before putting this on the WP Repository, but you are welcome to use the plugin however you'd like. If any issues pop up, I would love to know about them, but I can't guarantee support.

----------


Documentation
-------------

> **Install:**

> - Download the repo manually, or navigate to the plugin folder of your WP install which should be at `wp-content/plugins` and clone using:
>  > git clone https://github.com/mrosata/wp-auto-ajax auto-ajax
> 
> - Log into your WP site and **activate** the `auto-ajax` plugin. 
> - From there, you can find an options menu under the `tools` tab in the Dashboard. Basically your able to give CSS selectors to the &lt;a&gt; tags you want to become Ajax, and a CSS selector to the element where all the main content of the page is loaded (where the links will load into). There is also options for "fallback" selectors in case the plugin can't find the right selector on the page it is trying to load. 


If the plugin doesn't work out of the box then it's best to use the advanced settings. Note that the browser history should work in both "regular" and "advanced" mode, the options in the advanced section require that you check "advanced" in the settings screen.  

If you would like to attach custom JavaScript events to fire after the ajax returns then use one or more of the following. Each receives 3 arguments (Event, jQXHR, state):
```javascript
/* success: Fires when the ajax request returns successfully and 
            the plugin is able to load content into your HTML. */
    jQuery(document).on('success.wp-auto-ajax', someLogicForOnComplete);
    
/* error: Fires when the ajax request throws an error and in those
          cases you probably have something wrong on your server. */
    jQuery(document).on('error.wp-auto-ajax', someLogicForOnComplete);
    
/* complete: Fires when ajax is complete regardless of outcome. */
    jQuery(document).on('complete.wp-auto-ajax', someLogicForOnComplete);
```

One thing to note, if the Ajax request comes back fine but for some reason the plugin isn't able to handle loading the content into the page there is no event because the plugin just allows the link to behave the same way it would have if the plugin wasn't turned on (so the next page loads without Ajax and there is no need for event handling).
If you wanted to do some work when the requests are sent out to the server look into `jQuery.ajax.onSend` [http://api.jquery.com/ajaxSend/](http://api.jquery.com/ajaxSend/).


> You may contact me @ [mike@stayshine.com](mailto:mike@stayshine.com)
