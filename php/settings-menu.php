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
    $default_div = esc_attr($_POST['default-div']);
    $adv_load_div = esc_attr($_POST['adv-load-div']);
    $adv_menu_div = esc_attr($_POST['adv-menu-div']);
    $adv_fallback_div = esc_attr($_POST['adv-fallback-div']);
    $adv_bubble_query = strtolower(esc_attr($_POST['adv-bubble-query'])) == 'true' ? 'true' : 'false';


    $options = array(
        'default-div'       => $default_div,
        'adv-load-div'      => $adv_load_div,
        'adv-menu-div'      => $adv_menu_div,
        'auto-ajax-level'   => $auto_ajax_level,
        'adv-fallback-div'  => $adv_fallback_div,
        'adv-bubble-query'  => $adv_bubble_query
    );

    update_option('rosata-auto-ajax', $options);
    $updated = true;

} else {
    // Get the settings that are currently stored to fill in the form, or use defaults
    $options = get_option('rosata-auto-ajax');
    $updated = false;
    // This isn't an update POST, so just get options from db
    $default_div = $options['default-div'];
    $adv_load_div = $options['adv-load-div'];
    $adv_menu_div = $options['adv-menu-div'];
    $adv_fallback_div = $options['adv-fallback-div'];
    $auto_ajax_level = $options['auto-ajax-level'];
    $adv_bubble_query = $options['adv-bubble-query'];
}


//todo: You should Automate this form so that you won't have to write it again in the future for other plugins
?>
<div class="wrap">
    <h2><?php _e('Auto Ajax Options', 'auto-ajax') ?></h2>

    <p>
        <?php _e('Thanks for testing out this plugin. There are a few options that you may want or need to set up below.','auto-ajax') ?>
    </p>

    <p>
        <?php _e('Please choose a method that best suites your needs. By defualt I have selected "Basic" and hopefully should work fairly well for similiarly designed themes to WP TwentyFifteen. It loads into the <code>#content</code> new pages using Ajax into the <code>#content</code> of current loaded page. Feel free to change the selector to any CSS selector you want, just make it unique to one element on the page','auto-ajax') ?>
    </p>

    <form method="POST" id="autoAjaxSettings">
        <table class="form-table">

            <!-- Basic Settings -->
            <tr valign="top">
                <th scope="row">
                    <?php // see if the basic box is checked or not
                    $checked = $auto_ajax_level == 'basic' ? 'checked' : '';
                    ?>
                    <!-- The Checkbox to use Basic Setup -->
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

                    <!-- Textbox Default Ajax Loading div -->
                    <input type="text" name="default-div" size="35" class="auto-ajax-input" value="<?php echo $default_div ?>"/>

                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e('Default container to load and extract html from Ajax requests.', 'auto-ajax') ?>
                    </p>
                    
                </td>
            </tr>

            <!-- Advanced Settings -->
            <tr>
                <th>
                    <?php // see if the advanced box is checked or not
                    $checked = $auto_ajax_level == 'advanced' ? 'checked' : '';
                    ?>
                    <!-- The Radio button to use Advanced Setup -->
                    <input type="radio" name="auto-ajax-level" class="auto-ajax-lvl" value="advanced" <?php echo $checked ?> />
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

                    <!-- Checkbox Recursive Container Search -->
                    <input type="checkbox" name="adv-bubble-query" size="35" class="auto-ajax-checkbox" value="true" <?php echo $checked ?>/>

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

                    <!-- Textbox Default Ajax Loading div -->
                    <input type="text" name="adv-menu-div" size="35" class="auto-ajax-input" value="<?php echo $adv_menu_div ?>"/>

                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e('Ajax Menu Selector: This is a css selector that DOES NOT have to be unique. Any link elements, &lt;a&gt; inside elements matching this selector will attempt to be loaded using Auto-Ajax into the Ajax Custom Container. If you want all links on the page to be used, leave it blank', 'auto-ajax') ?>
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

                    <!-- Textbox Default Ajax Loading div -->
                    <input type="text" name="adv-load-div" size="35" class="auto-ajax-input" value="<?php echo $adv_load_div ?>"/>


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

                    <!-- Textbox Default Ajax Loading div -->
                    <input type="text" name="adv-fallback-div" class="auto-ajax-input" size="35" value="<?php echo $adv_fallback_div ?>"/>


                    <!-- Tooltip -->
                    <span class="dashicons dashicons-editor-help auto-ajax-info-icon"></span>
                    <p class="auto-ajax-tooltip">
                        <?php _e('Ajax Fallback Container, a css selector that should be unique. If a Page or Post doesn\'t have the main Custom Ajax HTML Container anywhere in it\'s body, then Auto Ajax will search for this container. That means that this will also both be the section of the page that requests are loaded into and also the section of the page that is loaded into the Ajax container of other pages.', 'auto-ajax') ?>
                    </p>


                </td>
            </tr>




            <tr>
                <th>
                </th>
                <td>
                    <button type="submit" id="autoAjaxSubmit"><?php _e('Update Settings', 'auto-ajax') ?></button>

                    <?php
                    if (isset($updated) && $updated):
                        echo '<div style="font-size:1.75em">' .__('Auto Ajax Updated!', 'auto-ajax') . '</div>';
                    endif;
                    ?>
                </td>
            </tr>

            <?php settings_fields('rosata-auto-ajax') ?>
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