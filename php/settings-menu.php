<?php
/**
 * settings-menu.php
 * @package auto ajax wp plugin
 * @author michael
 * @date 04/17/2015
 */


namespace AutoAjax;

if (!current_user_can('manage_options')) {
    wp_die( __('Unfortunately you can\'t use this page due to your capabilities level', 'auto-ajax') );
}


// Check if the form was just submitted
if ( isset($_POST['auto-ajax-settings']) == '1' ) {

    // The form was just submitted, check if anything needs updating
    $auto_ajax_level = esc_attr($_POST['auto-ajax-level']);
    $update_browser_url = strtolower(esc_attr($_POST['update-browser-url'])) == 'true' ? 'true' : 'false';
    $auto_reload_scripts = strtolower(esc_attr($_POST['auto-reload-scripts'])) == 'true' ? 'true' : 'false';
    $default_div = esc_attr($_POST['default-div']);
    $adv_load_div = esc_attr($_POST['adv-load-div']);
    $adv_menu_div = esc_attr($_POST['adv-menu-div']);
    $adv_fallback_div = esc_attr($_POST['adv-fallback-div']);
    $adv_bubble_query = strtolower(esc_attr($_POST['adv-bubble-query'])) == 'true' ? 'true' : 'false';
    $adv_ignore_script_re = esc_attr($_POST['adv-ignore-script-re']);


    $options = array(
        'default-div'          => $default_div,
        'adv-load-div'         => $adv_load_div,
        'adv-menu-div'         => $adv_menu_div,
        'auto-ajax-level'      => $auto_ajax_level,
        'adv-fallback-div'     => $adv_fallback_div,
        'adv-bubble-query'     => $adv_bubble_query,
        'update-browser-url'   => $update_browser_url,
        'auto-load-scripts'    => $auto_reload_scripts,
        'adv-ignore-script-re' => $adv_ignore_script_re,
    );

    update_option('auto-ajax', $options);
    $updated = true;

} else {
    // Get the settings that are currently stored to fill in the form, or use defaults
    $options = get_option('auto-ajax');
    $updated = false;
    // This isn't an update POST, so just get options from db
    $default_div = $options['default-div'];
    $adv_load_div = $options['adv-load-div'];
    $adv_menu_div = $options['adv-menu-div'];
    $adv_fallback_div = $options['adv-fallback-div'];
    $auto_ajax_level = $options['auto-ajax-level'];
    $adv_bubble_query = $options['adv-bubble-query'];
    $update_browser_url = $options['update-browser-url'];
    $auto_reload_scripts = $options['auto-reload-scripts'];
    $adv_ignore_script_re = $options['adv-ignore-script-re'];
}

$nbsp='&nbsp;';
$nbtb="{$nbsp}{$nbsp}{$nbsp}{$nbsp}";
$page_content=array();
$page_content['explain_basic_mode'] = "Please try the <strong>Basic Mode</strong> to begin. By defualt it should"
  . " hopefully work well for general WP themes. The \"Ajax HTML Container\" is a selector that tells <strong>"
  . "Auto Ajax</strong> where to look for content in a page loaded asyncronously, and also what content on the current"
  . " page to replace with that content. If your dynamic content is always in a common element, then the plugin has a"
  . " simple job. However, where that is not the case you may need <strong>Advanced</strong> settings.";

$page_content['default_ajax_container'] = "Default container to load and extract html to/from upon successful Ajax requests."
  . " Accepts any CCS Selector. By default <code>#content</code> is the assumed parent. When a user clicks a link, that new page"
  . " would also require an element matching the <code>#content</code> selector. Feel free to change"
  . " the selector to any valid, but unique, CSS selector you want.";

$page_content['ajax_menu_selector'] = "This optional css selector will force <strong>Auto Ajax</strong> to only attemp asyncronous"
  . " navigation on particular links. The selector can be any valid <em>jQuery</em> CSS selector as long as it selects actual links."
  . " For instance, <em>ul.menu</em> would be WRONG, because it's a container. Something like <code>ul.menu a.nav-link</code> could"
  . " work as it points to the actual link your sites users would click. By default the plugin uses <code>a</code>, which will"
  . " select all links on the page (<em>In any case Auto Ajax only ever modifies links that point to your own website, external links"
  . " will always be ignored</em>). <br/>Elements matching this selector will trigger Auto-Ajax Ajax when clicked.";

$page_content['auto_reload_scripts'] = "Normally everytime a WP page is loaded there are JavaScript files loaded as well, and they"
  . " perform important logic onload. Auto-Ajax will search the page being loaded asyncronously for &lt;script&gt; tags and load"
  . " those scripts into the current page. On subsequent navigations these scripts are removed and again replaced with scripts from"
  . " the latest page load. Uncheck this box to prevent this behaviour, or use advanced settings to fine-tune which scripts are"
  . " managed by Auto Ajax. <em>(Auto Ajax replaces/reload scripts from the plugins and theme folders by default, not core WP scripts)</em>";

$page_content['adv_ignore_script_re'] = "A regular expression to match scripts that Auto Ajax should ignore when loading scripts from the"
  . " next page into the current page. Use negitive matching techniques to use this option as a filter for all scripts except the ones"
  . " that match your regex.<br/>$nbsp$nbsp <strong>Simple Example:</strong> <em>(will ignore reloading scripts with \"bad-plugin-name\" in the file or"
  . " absolute directory path)</em><br/>$nbsp$nbsp<code>.+bad-plugin-name.+</code><br/><br/>$nbsp$nbsp <strong>Complex Example:</strong><em>"
  . " (only reload scripts with <code>src</code> values not containing \"good-plugin\", \"some-great-plugin\" or located inside themes"
  . " folder \"wp-content/themes/\")</em><br/>$nbtb "
  . " <code>^.((?!good-plugin|some-great-plugin|\/wp-content\/themes\/.*).)*$</code><br/><br/> $nbtb <strong>Won't Match (will reload): </strong> $nbtb "
  . "<pre>      wp-content/plugins/good-plugin/js/any-script.js
      wp-content/plugins/some-great-plugin/js/load-me.js
      wp-content/themes/my-theme/js/vendor/this-will-load.js</pre>"
  . "$nbtb <strong>Auto Ajax Will Match (will not reload): </strong> $nbtb "
  . "<pre>      wp-content/plugins/some-useless-plugin/js/donot-load-me.js
      wp-content/plugins/ignored-plugin/js/wont-load-me.js</pre>";
//todo: You should Automate this form so that you won't have to write it again in the future for other plugins
?>
<div class="wrap">
    <h2><?php _e('Auto Ajax Options', 'auto-ajax') ?></h2>

    <p>
        <?php _e('Thanks for testing out this plugin. There are a few options that you may want or need to set up below.','auto-ajax') ?>
    </p>

    <p>
        <?php _e($page_content['explain_basic_mode'],'auto-ajax') ?>
    </p>

    

    <?php
    if (isset($updated) && $updated):
        echo '<div id="auto-ajax-updated" style="font-size:1.75em;color:#43a047;font-weight:bold;width:100%;text-align:center;">' .__('Auto Ajax Updated!', 'auto-ajax') . '</div>';
        unset($_POST['auto-ajax-settings']);
        ?>
        <script>
          setTimeout(function() {
            var autoAjaxMessageElem = document.getElementById('auto-ajax-updated');
            if (autoAjaxMessageElem && autoAjaxMessageElem.classList)
              autoAjaxMessageElem.classList.add('hidden');
          }, 5000);
        </script>
        <?php
    endif;
    ?>
    <form method="POST" id="autoAjaxSettings">
        <table class="form-table">

            <!-- Update Browser URL ( pushState History ) -->
            <tr>
                <th></th>
                <td>
                    <?php
                    $checked = strtolower($update_browser_url) == 'true' ? 'checked' : '';
                    ?>
                    <label for="adv-menu-div">
                        <?php _e('Update Browser URL (History):', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Update Browser URL (History) GLOBAL SETTING -->
                    <input 
                        type="checkbox" name="update-browser-url"
                        size="35" class="auto-ajax-checkbox" value="true" <?php echo $checked ?>/>

                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e('After loading Ajax content on your site the plugin will try to update the browsers URL using the History API.', 'auto-ajax') ?>
                    </p>

                </td>
            </tr>


            <!-- Automatically Load/Reload Script tags on pages loaded by Ajax -->
            <tr>
                <th></th>
                <td>
                    <?php
                    $checked = strtolower($auto_reload_scripts) == 'true' ? 'checked' : '';
                    ?>
                    <label for="auto-reload-scripts">
                        <?php _e('Automatically Reload Theme & Plugin Scripts:', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Update Browser URL (History) GLOBAL SETTING -->
                    <input 
                        type="checkbox" name="auto-reload-scripts"
                        size="35" class="auto-ajax-checkbox" value="true" <?php echo $checked ?>/>

                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e($page_content['auto_reload_scripts'], 'auto-ajax') ?>
                    </p>

                </td>
            </tr>





            <!-- Basic Settings -->
            <tr valign="top">
                <th scope="row">
                    <?php // see if the basic box is checked or not
                    $checked = $auto_ajax_level == 'basic' ? 'checked' : '';
                    $disabled = $auto_ajax_level == 'basic' ? '' : 'disabled';
                    ?>
                    <!-- The Checkbox to use Basic Setup GLOBAL SETTING -->
                    <input type="radio" name="auto-ajax-level" class="auto-ajax-lvl" value="basic" <?php echo $checked ?> />
                    <h4 style="display:inline-block"><?php _e('Basic','auto-ajax') ?></h4>
                    
                </th>
            </tr>

            <tr>
                <th></th>
                <td>
                    <label for="default-div">
                        <?php _e('Default Ajax HTML Container (css selector):', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Textbox Default Ajax Loading Container BASIC SETTING -->
                    <input 
                        type="text" name="default-div"  data-setting-lvl="basic"
                        size="35" class="auto-ajax-input"
                        value="<?php echo $default_div ?>" <?php echo $disabled ?> />

                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e($page_content['default_ajax_container'], 'auto-ajax') ?>
                    </p>
                    
                </td>
            </tr>



            <!-- Advanced Settings -->
            <tr>
                <th>
                    <?php // see if the advanced box is checked or not
                    $checked = $auto_ajax_level == 'advanced' ? 'checked' : '';
                    $disabled = $auto_ajax_level == 'advanced' ? '' : 'disabled';
                    ?>
                    <!-- The Radio button to use Advanced Setup GLOBAL SETTING -->
                    <input 
                        type="radio" name="auto-ajax-level"
                        class="auto-ajax-lvl" value="advanced" <?php echo $checked ?> />
                    <h4 style="display:inline-block"><?php _e('Advanced','auto-ajax') ?></h4>

                </th>
                <td>

                </td>
            </tr>


            <!-- Option Recursive Container Search -->
            <tr>
                <th></th>
                <td>
                    <?php // see if Ajax Bubbling container search is checked
                    $checked = strtolower($adv_bubble_query) == 'true' ? 'checked' : '';
                    ?>
                    <label for="adv-menu-div">
                        <?php _e('Ajax Bubbling Container Search:', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Checkbox Recursive Container Search ADVANCED SETTING -->
                    <input 
                        type="checkbox" name="adv-bubble-query" data-setting-lvl="advanced"
                        size="35" class="auto-ajax-checkbox" value="true"
                        <?php echo $checked ?> <?php echo $disabled ?> />

                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e('Bubbling container search will search backwards from the clicked link to try to find a matching Ajax container that also contains the clicked link. This is good for sites that have a lot of other Ajax events occuring besides Auto Ajax and may have additional instances of a container spawn due to those events.', 'auto-ajax') ?>
                    </p>

                </td>
            </tr>



            <!-- Option Custom Ajax Menu Selector -->
            <tr>
                <th></th>
                <td>
                    <label for="adv-menu-div">
                        <?php _e('Ajax Menu Selector:', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Textbox Default Ajax Loading div ADVANCED SETTING -->
                    <input
                        type="text" name="adv-menu-div" data-setting-lvl="advanced"
                        size="35" class="auto-ajax-input"
                        value="<?php echo $adv_menu_div ?>" <?php echo $disabled ?> />

                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e($page_content['ajax_menu_selector'], 'auto-ajax') ?>
                    </p>

                </td>
            </tr>


            <!-- Option Custom Ajax Container Selector -->
            <tr>
                <th></th>
                <td>
                    <label for="adv-load-div">
                        <?php _e('Ajax Custom Container:', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Textbox Default Ajax Loading div ADVANCED SETTING -->
                    <input
                        type="text" name="adv-load-div" data-setting-lvl="advanced"
                        size="35" class="auto-ajax-input" 
                        value="<?php echo $adv_load_div ?>" <?php echo $disabled ?> />


                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e('Ajax Custom Container, where content will load into. This should be a valid css selector and should be a container that is common on all pages. There is also an option below for a fallback container in case a page doesn\'t have this HTML Element in the body, then Auto Ajax will try to find a backup container before just forcing a regular page load on the entire document.', 'auto-ajax') ?>
                    </p>


                </td>
            </tr>



            <!-- Option Fallback Ajax Container Selector -->
            <tr>
                <th></th>
                <td>
                    <label for="adv-fallback-div">
                        <?php _e('Ajax Fallback Container: (optional)', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Textbox Default Ajax Loading div ADVANCED SETTING -->
                    <input
                        type="text" name="adv-fallback-div" data-setting-lvl="advanced"
                        class="auto-ajax-input" size="35"
                        value="<?php echo $adv_fallback_div ?>" <?php echo $disabled ?> />


                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e('Ajax Fallback Container, a css selector that should be unique. If a Page or Post doesn\'t have the main Custom Ajax HTML Container anywhere in it\'s body, then Auto Ajax will search for this container. That means that this will also both be the section of the page that requests are loaded into and also the section of the page that is loaded into the Ajax container of other pages.', 'auto-ajax') ?>
                    </p>


                </td>
            </tr>




            <!-- Ignore Scripts Using Regular Expression -->
            <tr>
                <th></th>
                <td>
                    <label for="adv-ignore-script-re">
                        <?php _e('Ignore Reloading Scripts Matching: (optional)', 'auto-ajax') ?>
                    </label>
                    <br />

                    <!-- Textbox Ignore Reloading Scripts Matching a RegExp ADVANCED SETTING -->
                    <input
                        type="text" name="adv-ignore-script-re" data-setting-lvl="advanced"
                        class="auto-ajax-input" size="35"
                        value="<?php echo $adv_ignore_script_re ?>" <?php echo $disabled ?> />


                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e($page_content['adv_ignore_script_re'], 'auto-ajax') ?>
                    </p>


                </td>
            </tr>



            <tr>
                <th>
                </th>
                <td>
                    <button type="submit" id="autoAjaxSubmit"><?php _e('Update Settings', 'auto-ajax') ?></button>
                </td>
            </tr>

            <?php settings_fields('auto-ajax') ?>
            <input type="hidden" class="widefat" name="auto-ajax-settings" value="1" />

        </table>
    </form>


</div>

<style>
    td label {
        font-weight: bold;
        text-decoration: underline;
        display: block;
        height: 6px;
    }
    .auto-ajax-checkbox {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #666;
        margin: 0;
        padding: 0;
    }
    .auto-ajax-tooltip {
        cursor: pointer;
    }
    .auto-ajax-info-icon:hover {
        -webkit-transform: scale(1.05);
        -moz-transform: scale(1.05);
        transform: scale(1.05);
        cursor: pointer;
    }
    .auto-ajax-info-icon:hover ~ p.auto-ajax-tooltip {
        color: red;
    }
</style>
<script src="<?php echo plugins_url('/../js/settings-menu.js',__FILE__) ?>"></script>
