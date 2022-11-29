<?php

/**
 * The plugin creation file.
 * 
 * Plugin Name: Post Words Filter
 * Description: Change or remove any words from your posts.
 * Version: 1.0.0
 * Author: Claudio Alcantara
 * Author URI: https://www.linkedin.com/in/claudioalcantararivas/
 * Text Domain: pwfpdomain
 * License: GPL2
 * Domain Path: /languages
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * Copyright 2022  Claudio Alcantara  (email : claudio.dev29@gmail.com)
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// Exit if accessed directly 
if( !defined( 'ABSPATH' ) ) exit;

class PostWordsFilter {

    function __construct()
    {
        // Hook or plugin in the WP Menu
        add_action( 'admin_menu', array($this, 'hookMenu') );
        add_action( 'admin_init', array($this, 'theSettings') );
        // Add the logic for the filter function
        if (get_option( 'post_words_to_filter' ) ) add_filter( 'the_content', array($this, 'filterLogic') );
    }

    function theSettings()
    {
        // TODO: Add comments
        add_settings_section( 'replecement-text-section', null, null, 'words-filter-options' );
        register_setting( 'replacementFields', 'replacementText' );
        add_settings_field( 'replacement-text', 'Filtered Text', array($this, 'replacementFieldHTML'), 'words-filter-options', 'replecement-text-section' );
    }

    function replacementFieldHTML(){ ?>
        <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '***')) ?>">
        <p class="description">Leave blank to remove the filtered words.</p>
    <?php }

    // The logic fot the words filter
    function filterLogic($content)
    {
        // Remove the comma from DB
        $badWords = explode(',', get_option( 'post_words_to_filter' ));
        // Remove the empty space
        $badWordsTrimmed = array_map('trim', $badWords);
        return str_ireplace($badWordsTrimmed, esc_html( get_option( 'replacementText', '****' )), $content);
    }

    // Add menu pages
    function hookMenu()
    {
        // Main page
        $mainPageHook = add_menu_page( 'Words To Filter', 'Post Words Filter', 'manage_options', 'postwordsfilter', array($this, 'postWordsFilterPage'), 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+Cg==', 100 );
        // Mixing the two urls, main page
        add_submenu_page( 'postwordsfilter', 'Words To Filter', 'Words Lists', 'manage_options', 'postwordsfilter', array($this, 'postWordsFilterPage') );
        // Sub page options
        add_submenu_page( 'postwordsfilter', 'Words Filter Options', 'Options', 'manage_options', 'words-filter-options', array($this, 'optionsSubPage') );
        // Load the assets in the admin page
        add_action( "load-{$mainPageHook}", array($this, 'mainPageAssets') );
    }

    /* Add assets */
    function mainPageAssets()
    {
        wp_enqueue_style( 'filterAdminCss', plugin_dir_url(__FILE__) . 'styles.css' );
    }

    // Action in the form after submited form
    function handleForm() {
        // Validating the form by using wp_nonce
        if (wp_verify_nonce( $_POST['wordsfilternonce'], 'saveFilterWords' ) AND current_user_can('manage_options')) {
            update_option( 'post_words_to_filter',  sanitize_text_field( $_POST['post_words_to_filter'] )); ?>
            <div class="updated">
                <p>Your filtered words were saved.</p>
            </div>
        <?php } else { ?>
            <div class="error">
                <p>Sorry, you don't have permission to perfom that action.</p>    
            </div>           
        <?php  }
    }

    // Functionality to the main page
    function postWordsFilterPage() { ?>
        <div class="wrap">
            <h1>Post Words Filter</h1>
            <?php if ($_POST['justsubmitted'] == "true") $this->handleForm() ?>
            <form method="POST">
                <input type="hidden" name="justsubmitted" value="true">
                <?php wp_nonce_field( 'saveFilterWords','wordsfilternonce' )?>
                <label for="post_words_to_filter">
                    <p>Enter a <strong>comma-separated</strong> list of words to filter from your site's.</p>                  
                </label>
                <div class="words-filter__flex-cotainer">
                    <!-- Hold the value from the database -->
                    <textarea name="post_words_to_filter" id="post_words_to_filter" placeholder="bad, ugly, mean">
                        <?php echo esc_textarea( get_option( 'post_words_to_filter') )?>
                    </textarea>
                </div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </form>
        </div>
    <?php }

    // Functioality to the sub page options
    function optionsSubPage() { ?>
        <div class="wrap">
            <h1>Words Filter Options</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_errors();
                    settings_fields('replacementFields');
                    do_settings_sections( 'words-filter-options' );
                    submit_button();
                ?>
            </form>
        </div>
    <?php }

}

$postWordsFilter = new PostWordsFilter();