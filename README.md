# wp-auto-ajax

WP Auto Ajax!
===================

> Update 2016-07-7
I've gone and fixed a few JavaScript errors that may or may not have been seen in some themes. A new History feature has been added which works regardless if you are in basic or advanced mode (you just need to check the checkbox on the settings page). The history feature will update the url after a successful Ajax page load and it will try to implement Ajax functionality when the user hits back/forward through their history. Just like with the normal settings, if the plugin can't successfully make and populate Ajax content into the page then it will fallback to regular page loads.



This plugin is in halted development. It worked on 2 themes that it was tested on and is in use on a production site at the moment. That being said, there are many features I would like to add before putting this on the WP Repository, but you are welcome to use the plugin however you'd like. If any issues pop up, I would love to know about them, but would advise that you seek your own solutions as I can't guarantee support.

----------


Documents
-------------

StackEdit stores your documents in your browser, which means all your documents are automatically saved locally and are accessible **offline!**

> **Install:**

> - Download the repo manually, or navigate to the plugin folder of your WP install which should be at `wp-content/plugins` and clone using:
>  > git clone https://github.com/mrosata/wp-auto-ajax auto-ajax
> 
> - Log into your WP site and **activate** the `auto-ajax` plugin. 
> - From there, you can find an options menu under the `tools` tab in the Dashboard. Basically your able to give CSS selectors to the &lt;a&gt; tags you want to become Ajax, and a CSS selector to the element where all the main content of the page is loaded (where the links will load into). There is also options for "fallback" selectors in case the plugin can't find the right selector on the page it is trying to load. 

I will try to update this README.md during July 2015.

You may contact me @ [mike@stayshine.com](mailto:mike@stayshine.com)