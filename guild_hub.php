<?php
/*
Plugin Name: Guild Hub
Plugin URI: http://fluffygamers.co.uk
Description: A guild management system for Adming and selected users of a site. 
Version: 0.1a
Author: Rob MacKay
Author URI: http://copterlabs.com
License: GPL2
*/

class guild_hub 
{

    public __construct() 
    {
    
        $this->guild_hub_tables();
    
    }

    /*Private Functions*/
    
    private function guild_hub_tables() 
    {
    
        if(get_option('gh_installed') === false) {
        
            global $wpdb;
        
            $sqls[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gh_settings (
                        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                        setting TEXT NOT NULL ,
                        data TEXT NOT NULL
                        );";
            
            $sqls[] = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gh_user_to_guild (
                        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        guild_id INT NOT NULL,
                        rank INT NOT NULL,
                        UNIQUE (
                        user_id,
                        guild_id
                        )
                    );
            ";
            
            CREATE TABLE `gh_table` (

            
            foreach($sqls as $sql) {
            
                $wpdb->query($sql);
            
            }
            
            $UserFields = array(

                "toon" => array(
                    "type" => "text",
                    "name" => "gh_toonName",
                    "label" => "Your Main Characters Name",
                    "showreg" => 1
                ),
            
                "age" => array(
                    "type" => "text",
                    "name" => "gh_age",
                    "label" => "Your Age"
                    "showreg" => 1                    
                ),
                
                "experiance" => array(
                    "type" => "textarea",
                    "name" => "gh_exp",
                    "label" => "MMO Experiance"
                    "showreg" => 0                    
                ),
                
                "pvp_pref" => array(
                    "type" => "select",
                    "name" => "gh_pvp",
                    "value" => array("Casual", "Hardcore", "For Fun", "Serious Team"),
                    "label" => "PVE Preference"
                    "showreg" => 0
                );                      
                
                "pve_pref" => array(
                    "type" => "select",
                    "name" => "gh_pve",
                    "value" => array("Casual", "Hardcore", "For Fun", "Serious Team"),
                    "label" => "PVP Preference"
                    "showreg" => 0
                );                
                
                "looking" => array(
                    "type" => "checkbox",
                    "name" => "gh_looking",
                    "value" => "1",
                    "label" => "Looking For Guild?"  
                    "showreg" => 1                    
                ),
                
                "location" => array(
                    "type" => "select",
                    "name" => "gh_location",
                    "value" => array("N.America", "S.America", "Europe", "Asia", "Australisia", "Russia"),
                    "label" => "My Location"
                    "showreg" => 1
                );
                
                "server" => array(
                    "type" => "text",
                    "name" => "gh_home",
                    "label" => "Your Home Server Name"
                    "showreg" => 0
                );
                
                "games" => array(
                    "type" => "textarea",
                    "name" => "gh_games",
                    "label" => "What games have you played?"
                    "showreg" => 0
                );
                
                "pve_experiance" => array(
                    "type" => "textarea",
                    "name" => "gh_pve_exp",
                    "label" => "A little about your PVE experiance"
                    "showreg" => 0
                );                
                
                "pvp_experiance" => array(
                    "type" => "textarea",
                    "name" => "gh_pvp_exp",
                    "label" => "A little about your PVP experiance"
                    "showreg" => 0
                );
                
                "avatar" => array(
                    "type" => "file",
                    "name" => "avatar", 
                    "label" => "Your Avatar (2mb limit)"
                    "showreg" => 0
                );
                
            );
            
            $data = array(
                "settings" => "fields",
                "data" => $UserFields
            );
            
            $format = array(
                "%s",
                "%s"
            );
            
            $wpdb->insert($wpdb->prefix . "gh_settings", $data, $format);
            
            update_option('gh_installed', true);
            
        } //end of install check
    
    }
    
    private function handle_user_reg() 
    {
    
        require_once(ABSPATH . WPINC . '/registration.php');
        global $wpdb, $user_ID;

        //We shall SQL escape all inputs
        $username = $wpdb->escape($_REQUEST['username']);
        if(empty($username)) {
            echo "User name should not be empty.";
            exit();
        }
        $email = $wpdb->escape($_REQUEST['email']);
        if(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/", $email)) {
            echo "Please enter a valid email.";
            exit();
        }

        $random_password = wp_generate_password( 12, false );
        $status = wp_create_user( $username, $random_password, $email );
        if ( is_wp_error($status) )
            echo "Username already exists. Please try another one.";
        else {
            $from = get_option('admin_email');
            $headers = 'From: '.$from . "\r\n";
            $subject = "Registration successful";
            $msg = "Registration successful.\nYour login details\nUsername: $username\nPassword: $random_password";
            wp_mail( $email, $subject, $msg, $headers );
            echo "Please check your email for login details.";
            
            foreach($_POST['extra'] as $meta_key => $meta_value) {
            
                update_user_meta( $status, $meta_key, $meta_value);
            
            }
            
        }
    
    }
    
    private function user_fields() {
    
        global $wpdb, $user_ID;
        
        $fields = $wpdb->get_results("SELECT data FROM {$wpdb->prefix}gh_settings WHERE settings = 'fields'");
        
        $e = <<<EOT
        <form method='post' encoding='file/text' action=''>
        <fieldset>
EOT;
        
        foreach($fields as $field) {
        
            if($field['type'] == "text") {
            
            $e .= <<<EOT
            <label>{$field['label']}</label>
            <input type='text' value='' name="extra[{$field['name']}]" />
EOT;
                
            } else if ($field['type'] == "select") {
 
            $e .= <<<EOT
            <label>{$field['label']}</label>
            <select name="extra[{$field['name']}]">
EOT;
                foreach($field['value'] as $value) {
                
                    $e .= "<option value='{$field['value']}'>{$field['value']}</option>";
                
                }
            
            $e .= "</select>";
            
                       
            } else if ($field['type'] == "textarea") {

            $e .= <<<EOT
            <label>{$field['label']}</label>
            <input type='text' value='' name="extra[{$field['name']}]">
EOT;
                        
            } else if ($field['type'] == "file") {

            $e .= <<<EOT
            <label>{$field['label']}</label>
            <input type='text' value='' name="extra[{$field['name']}]">
EOT;
                        
            }

        }
        
            $e .= <<<EOT
            </fieldset>
            </form>
EOT;
        
        
    }
    
    /*Public Functions*/
    
    public function user_reg()
    {

        if($_POST){
            
            $this->handle_user_reg();

            exit();

        } else {
        
            if (!$user_ID) { 
            
            $fields = get_option('gh_user_reg');
            
            $script = <<<EOT
                <script type="text/javascript">  
                //<![CDATA[ 
                 
                $("#submitbtn").click(function() { 
                 
                $('#result').html('<img src="<?php bloginfo('template_url') ?>/images/loader.gif" class="loader" />').fadeIn(); 
                var input_data = $('#wp_signup_form').serialize(); 
                $.ajax({ 
                type: "POST", 
                url:  "", 
                data: input_data, 
                success: function(msg){ 
                $('.loader').remove(); 
                $('<div>').html(msg).appendTo('div#result').hide().fadeIn('slow'); 
                } 
                }); 
                return false; 
                 
                }); 
                //]]>  
                </script>
EOT;
               
            }
            else {
               wp_redirect( home_url() ); exit;
            }

        }
    
    }
    
    /*
    
    Things we need: 
    
    User registration & Guild registration - 1 guild per user. 
    
    Guild management.
        Guild Name
        Description
        PVP Status.
        Current Recruiting?
        Application min requirements.
        User Management - rank, kick etc.
        Application management
        Home Server
        Games
        Banner Image
    
    User management.
        Age
        Experiance
        Looking for...
        Location
        Home Server
        PVP Experiance
        PVE Experiance
        Games
        Avatar.
        
    
    */
    
}