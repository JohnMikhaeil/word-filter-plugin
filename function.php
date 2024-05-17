<?php
/*
Plugin name: Word Filter Plugin
Description: A Plugin that filters words based on your choise
Author: John Mikhaeil
Version: 1.0.0
text domain: wfpdomain
domain path:/languages
*/
if( ! defined( 'ABSPATH' ) ) exit;
class wordFilterPlugin{
    function __construct(){
        add_action("admin_menu" , array($this, "addMenuPage"));
        add_action("admin_init" , array($this , "registerSettings") , 1);
        add_filter('the_content', array($this , 'contentAfterFilter'));
        add_action('init' , array($this  , 'languages'));
    }
    function languages(){
        load_plugin_textdomain('wfpdomain' , false , dirname(plugin_basename(__FILE__)) . '/languages');
    }
    function addMenuPage(){
        add_menu_page("Word Filter" , "Word Filter" , "administrator" , "word-filter-settings" , array($this , "menuPageContent") , "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IS0tIFVwbG9hZGVkIHRvOiBTVkcgUmVwbywgd3d3LnN2Z3JlcG8uY29tLCBHZW5lcmF0b3I6IFNWRyBSZXBvIE1peGVyIFRvb2xzIC0tPgo8c3ZnIHdpZHRoPSI4MDBweCIgaGVpZ2h0PSI4MDBweCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8cGF0aCBkPSJNMyA0LjZDMyA0LjAzOTk1IDMgMy43NTk5MiAzLjEwODk5IDMuNTQ2MDFDMy4yMDQ4NyAzLjM1Nzg1IDMuMzU3ODUgMy4yMDQ4NyAzLjU0NjAxIDMuMTA4OTlDMy43NTk5MiAzIDQuMDM5OTUgMyA0LjYgM0gxOS40QzE5Ljk2MDEgMyAyMC4yNDAxIDMgMjAuNDU0IDMuMTA4OTlDMjAuNjQyMiAzLjIwNDg3IDIwLjc5NTEgMy4zNTc4NSAyMC44OTEgMy41NDYwMUMyMSAzLjc1OTkyIDIxIDQuMDM5OTUgMjEgNC42VjYuMzM3MjZDMjEgNi41ODE4NSAyMSA2LjcwNDE0IDIwLjk3MjQgNi44MTkyM0MyMC45NDc5IDYuOTIxMjcgMjAuOTA3NSA3LjAxODgxIDIwLjg1MjYgNy4xMDgyOEMyMC43OTA4IDcuMjA5MiAyMC43MDQzIDcuMjk1NjggMjAuNTMxNCA3LjQ2ODYzTDE0LjQ2ODYgMTMuNTMxNEMxNC4yOTU3IDEzLjcwNDMgMTQuMjA5MiAxMy43OTA4IDE0LjE0NzQgMTMuODkxN0MxNC4wOTI1IDEzLjk4MTIgMTQuMDUyMSAxNC4wNzg3IDE0LjAyNzYgMTQuMTgwOEMxNCAxNC4yOTU5IDE0IDE0LjQxODIgMTQgMTQuNjYyN1YxN0wxMCAyMVYxNC42NjI3QzEwIDE0LjQxODIgMTAgMTQuMjk1OSA5Ljk3MjM3IDE0LjE4MDhDOS45NDc4NyAxNC4wNzg3IDkuOTA3NDcgMTMuOTgxMiA5Ljg1MjY0IDEzLjg5MTdDOS43OTA4IDEzLjc5MDggOS43MDQzMiAxMy43MDQzIDkuNTMxMzcgMTMuNTMxNEwzLjQ2ODYzIDcuNDY4NjNDMy4yOTU2OCA3LjI5NTY4IDMuMjA5MiA3LjIwOTIgMy4xNDczNiA3LjEwODI4QzMuMDkyNTMgNy4wMTg4MSAzLjA1MjEzIDYuOTIxMjcgMy4wMjc2MyA2LjgxOTIzQzMgNi43MDQxNCAzIDYuNTgxODUgMyA2LjMzNzI2VjQuNloiIHN0cm9rZT0iIzAwMDAwMCIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+" , 100);
        add_submenu_page("word-filter-settings" , "Settings" , "Settings" , "administrator" , "word-filter-settings" , array($this , "menuPageContent") );
        $mainPage = add_submenu_page("word-filter-settings" , "Replacing Words" , "Replacing Words" , "administrator" , "replacing-words" , array($this , "replacingWordsContent"));
        add_action("load-{$mainPage}" , array($this , 'cssStyles'));
    }
    function cssStyles(){
        wp_enqueue_style('mainPageAssets' , plugin_dir_url(__FILE__) . 'style.css');
    }
    function menuPageContent(){
        ?>
        <div class="wrap">
            <h1>Word Filter Settings</h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('wfp_group');
                do_settings_sections('word-filter-settings');
                submit_button();
                ?>
                
            </form>
        </div>
        <?php
    }
    function replacingWordsContent(){
        ?>
        <div class="wrap">
            <h1>The word to replace with if leaved blank will remove the words</h1>
            <?php
            if(isset($_POST["justSubmitted"])) $this->handleForm();
            ?>
            <form class="replacing-form" method="POST">
                <input type="hidden" name="justSubmitted" value="true"/>
                <?php wp_nonce_field('savingReplacing' , 'replacingNonce') ?>
                <input value="<?php echo esc_html(get_option('replacedWord')) ?>" name="replacedWord" type="text"/>
                <button type="submit" value="Save Changes">Save Changes</button>
            </form>
        </div>
    <?php
    }
    function handleForm(){
        if(wp_verify_nonce($_POST['replacingNonce'], 'savingReplacing') && current_user_can('manage_options')){
            update_option('replacedWord' , $_POST['replacedWord']);
            ?>
            <div class="updated">Your Changes have been updated!</div> 
            <?php
        }else{
            ?>
            <div class="error">
                sorry! you don't have permission to submit the form.
            </div>
            <?php
        }
        
    }
    function registerSettings(){
        add_settings_section('wfp_section' , null , null , 'word-filter-settings');
        
        add_settings_field('wfp_onoroff' , 'Filter Words' , array($this , 'filterWordsField') , 'word-filter-settings' , 'wfp_section');
        register_setting('wfp_group' , 'wfp_onoroff' , array( 'sanitize_callback' => 'sanitize_text_field' ,'default'=>'1'));
        
        add_settings_field('wfp_chosenPost' , 'Post To Filter' , array($this , 'chosenPostField') , 'word-filter-settings' , 'wfp_section');
        register_setting('wfp_group' , 'wfp_chosenPost' , array('sanitize_callback' => array($this , "sanitizePosts") , 'default' => '0'));
    
        add_settings_field('wfp_wordsToFilter' , 'Words To Filter' , array($this , 'wordsToFilter'), 'word-filter-settings' , 'wfp_section');
        register_setting('wfp_group' , 'wfp_wordsToFilter' , array('sanitize_callback' => 'sanitize_text_field' , 'default' => ''));
    }
    function sanitizePosts($input){
        $args = array(
            'post_type' => 'post',
            'fields' => 'ids',
            'post_status' => 'published',
            'posts_per_page' => -1
        );
        $posts = get_posts($args);
        if(in_array($input,$posts)){
            return $input;
        }else{
            add_settings_error('wfp_chosenPost' , 'chosenPost-error' , 'The post you choose is invalid');
            return get_option('wfp_chosenPost');
        }
    }
    function filterWordsField(){
        ?>
        <input type='checkbox' name="wfp_onoroff" <?php checked(get_option('wfp_onoroff') , 'on') ?> />
        <?php
    }
    function chosenPostField(){
        ?>
        <select name="wfp_chosenPost" id="wfp_chosenPost">
            <?php
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'post_status' => 'published',
            );
            $posts = get_posts($args);
            foreach($posts as $post){
                ?>
                <option <?php selected(get_option('wfp_chosenPost') , $post->ID) ?> value="<?php echo $post->ID ?>"> <?php echo $post->post_title ?> </option>
                <?php
            }
            ?>
        </select>
    <?php
    }
    function wordsToFilter(){
        ?>
        <p>Enter the words you want to filter separated with commas.</p>
        <textarea cols="60" rows="5" name="wfp_wordsToFilter"><?php echo esc_textarea(get_option("wfp_wordsToFilter")) ?></textarea>
        <?php
    }
    function contentAfterFilter($content){
        if(get_the_ID() == get_option('wfp_chosenPost')){
            $words = get_option('wfp_wordsToFilter');
            $wordsArray = explode(',' , $words);
            $FilteredContent = str_replace( $wordsArray , esc_html(get_option('replacedWord')) , $content);
            return $FilteredContent;
        }else{
            return $content;
        }
    }
}

$wordFilterPLugin = new wordFilterPLugin();