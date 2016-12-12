<?php
/**
 * Plugin Name: User Behavioral Post
 * Plugin URI: http:titusshoats.com
 * Description: A plugin that allows you to show post based on user history & behavior
 * Version: 1.0
 * Author: Titus Shoats
 * Author URI: http:/titusshoats.com
 * Text Domain: ubp_text_domain
 * License: GPLv2
 */


/****Plugin Scripts & Styles***/

function my_custom_plugin_javascript(){

    wp_enqueue_script( 'jquery-file','https://code.jquery.com/jquery-3.1.1.min.js' );

    wp_register_script('select2',plugin_dir_url(__FILE__).'js/select2.full.min.js',array('jquery'));

   

  
    wp_enqueue_script( 'select2-file',plugin_dir_url(__FILE__).'js/select2-file.js' , array('select2') );

}
add_action( 'admin_enqueue_scripts', 'my_custom_plugin_javascript');


function my_custom_plugin_css(){
   wp_register_style('custom-plugin-css0',plugin_dir_url(__FILE__).'css/select2.min.css',false);
   wp_register_style('custom-plugin-css1',plugin_dir_url(__FILE__).'css/select2_custom.css',false);

    wp_register_style('bootstrap-css','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',false);


    wp_enqueue_style('custom-plugin-css0');
    wp_enqueue_style('custom-plugin-css1');
    wp_enqueue_style('bootstrap-css');
}

add_action('admin_enqueue_scripts','my_custom_plugin_css');

if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log(write_log( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}





/****Activation***/

register_activation_hook(__FILE__,'ubp_install');

function ubp_install(){

	global $wbdp;

	$sql_vistitor_data = "CREATE TABLE `wp_ubp_visitor_data`(\n"
    . " \n"
    . " visitor_id int unsigned not null AUTO_INCREMENT PRIMARY KEY,\n"
    . " unique_id char(50) not null,\n"
    . " ip_address char(50) not null\n"
    . " \n"
    . ")";

    $sql_track_visitor = 
    "CREATE TABLE `wp_ubp_visitor_track_post_data`(\n"
    . " \n"
    . " unique_id char(50) not null PRIMARY KEY,\n"
    . " ip_address char(50) not null,\n"
    . " track_post char(250) not null\n"
    . " \n"
    . ")";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql_vistitor_data);
    dbDelta($sql_track_visitor);

    $ubp_db_version = '1.0';

    add_option('ubp_db_version',$ubp_db_version);

}

add_action('admin_enqueue_scripts','backend_enqueue_ajax');

function backend_enqueue_ajax(){

   
    global $post;
    if(!empty($post->ID)){
     wp_enqueue_script('backend-ubp-ajax-script',plugin_dir_url(__FILE__).'/js/backend-user-post-interest-ajax.js',array('jquery'));

    wp_localize_script('backend-ubp-ajax-script','ubp_ajax_object_backend',array('ajax_url'=>admin_url('admin-ajax.php'),'behavioral_post_http'=>'behavioral_post_http','post_id'=>$post->ID,'ubp_remove_data'=>'ubp_remove_data'));
    }else{
      write_log("Post object, does not exist");
    }
       


}

add_action('wp_enqueue_scripts','frontend_enqueue_ajax');

function frontend_enqueue_ajax(){

    global $post;
    if(!empty($post->ID)){
 wp_enqueue_script('ubp-ajax-script',plugin_dir_url(__FILE__).'/js/frontend-user-post-interest-ajax.js',array('jquery'));

   

wp_localize_script('ubp-ajax-script','ubp_ajax_object_frontend',array('ajax_url'=>admin_url('admin-ajax.php'),'track_current_page'=>get_permalink(),'post_id'=>$post->ID,'visitor_history_meta_data'=>get_post_meta($post->ID,'_visitor_history'),'home_url'=>home_url()));

    }
}


add_action('admin_menu','ubp_create_menu');


function ubp_create_menu(){

add_menu_page('Behavioral Post','Behavioral Options','manage_options','behavioral-post-menu','behavioral_post_page');

add_submenu_page('behavioral-post-menu','Settings','Settings','manage_options','behavioral-post-settings','behavioral_post_settings_page');

}

function behavioral_post_settings_page(){
 global $post;



     ?>
     <h3><?php  _e('Behavior Settings','ubp_text_domain');?></h3>
     <form id="behavioral_settings_form_container" method="post" action="#" enctype="application/x-www-form-urlencoded">
       <?php settings_fields('behavioral-settings-group') ?>
     
       <?php  ?>
         <table>
         <thead></thead>
         <tbody>
        <tr>
            <td>
            <label> <?php _e('Collect Data On These Built In Post Types:','ubp_text_domain'); ?></label>
           </td>
           <td>
           <?php

           
    /**
     * Fetch built in post types
     */

    ?>
    <select id="behavioral_options_builtin_post_type" class="behavioral_options_builtin_post_type" multiple="multiple">
    <?php

        /**
         * Loops through built in types page titles
         */
        $custom_post_types= get_posts('post_type=post&numberposts=-1');
        foreach( $custom_post_types as $post):
            setup_postdata($post);

            ?>

                <option value="<?php echo esc_attr(get_permalink($post->ID))?>" name="behavioral_options_post_type[behavioral_options_builtin_post_type]"><?php _e(esc_html(get_permalink($post->ID)),'ubp_text_domain');?></option>

            <?php
        endforeach;
        ?>
        </select>
       <?php
            ?>
           </td>
        </tr>

         



         </tbody>
         </table>
           <?php submit_button(); ?>
         </form>


         <script>
  jQuery(document).ready(function($){

      var  behavioral_settings_form_container_submit = document.querySelector("#submit");
      var  jsObj = {};
      jsObj['action'] ='behavioral_post_http';
       $('.behavioral_options_builtin_post_type').each(function(index){

           $(this).on("change",function(evt){

                 var that = this;
                jsObj[index] = $(this).val();

                 
               
                behavioral_settings_form_container_submit.onclick = function(){

                     jQuery.post(ajaxurl,jsObj,function(response){

                   // console.log("Response:: " + response);
                     });
                  };

           });

       });





});

</script>


<?php


}

function behavioral_post_register_settings(){
	register_setting('behavioral-settings-group','behavioral_options_builtin_post_type','behavioral_sanitize_builtin_options');
}
function behavioral_sanitize_builtin_options($input){
  $input['behavioral_options_builtin_post_type'] = update_options('behavioral_options_builtin_post_type',sanitize_text_field($input['behavioral_options_builtin_post_type']));
  return $input;
}

function behavioral_serialized_custom_options(){
	$unserialized_behavioral_custom_options[0] = unserialize(get_option('behavioral_custom_options'));
	return $unserialized_behavioral_custom_options;
}

add_action('wp_ajax_behavioral_post_http','behavioral_post_http_callback');

function behavioral_post_http_callback(){
	 
    if(isset($_POST)){

        
        update_option('behavioral_custom_options',serialize($_POST));
        
       
      }

	wp_die();
}


add_action('wp_ajax_nopriv_ubp_track_visitor','ubp_track_visitor_callback');

    /***Collect data on pre selected post***/
    function ubp_track_visitor_callback(){
      

     global $wpdb;
       if(isset($_POST['track_current_page'])){
       $track_current_page = $_POST['track_current_page'];


        $ip_address = $_SERVER['REMOTE_ADDR'];
        if(isset($_SESSION['visitor'])){

            
        $unique_id = $_SESSION['visitor'];

              foreach (behavioral_serialized_custom_options()as $selected_posts){
                          if(is_array($selected_posts)){
                             foreach ($selected_posts[0] as $selected_post ){
                                                   
                                                  

                                                 /**
                                                  *Matches selected post (admin has selected within plugin) with current url visitor is currently on,
                                                    * If visitor is on selected posts, add visitor and post to database
                                                 **/

                                                if($track_current_page ==$selected_post){

                                                      $result =$wpdb->get_results("SELECT unique_id, ip_address FROM wp_ubp_visitor_data WHERE unique_id = '$unique_id' AND ip_address = 'xxx'",OBJECT);



                                                           /*******Inserts new visitor in database******/
                                                    if(!count($result)){

                                                                if($result = $wpdb->prepare($wpdb->insert(
                                                                       'wp_ubp_visitor_data',
                                                                       array(
                                                                       'unique_id'=>$unique_id,
                                                                       'ip_address'=>'xxx'
                                                                       ),
                                                                       array(
                                                                          '%s',
                                                                          '%s'
                                                                       )
                                                                    ),ARRAY_A)){
                                                                            echo "NEW USER";

                                                                    }else{
                                                                       /****Error in inserting new visitor***/
                                                                      print_r($result);
                                                                    }

                                                     }



                                                         if($result = $wpdb->query($wpdb->prepare("
                                                            INSERT IGNORE INTO wp_ubp_visitor_track_post_data(unique_id,ip_address,track_post)
                                                            VALUES (%s,%s,%s)",$unique_id,'xxx',$track_current_page))){
                                                                echo "Track Visitor Successfully";

                                                           }else{
                                                               print_r($result);
                                                           }




                                              }else{
                                               //  echo "No Matches on current post ";
                                              }
                                     
                             }
                          }
              }



        }else{
           $_SESSION['visitor'] =  uniqid() . time();
        }

      
      wp_die();
         
         

   }

}




/*** Meta Box**/
add_action('add_meta_boxes','ubp_meta_box_init');

function ubp_meta_box_init(){

  /**Creates metabox**/
 add_meta_box('ubp_meta_box','Behavioral Post','behavioral_meta_box','','normal','default');

}


/**
 * Callback that outputs meta box
 * @param $post object
 * @param $box object
 */
function behavioral_meta_box($post,$box){



  ?>

       <form method="post" action="#" enctype="application/x-www-form-urlencoded">

             <!-------Nonoce for security------>
               <table>
                     <?php wp_nonce_field(plugin_basename(__FILE__),'ubp_save_meta_box'); ?>
                      <label>Select post user has visited to only show this post to:</label>


                      <select name="behavioral_custom_options_metabox" class="behavioral_custom_options_metabox" multiple="multiple" >
                      <?php
                       $unserialize_behavioral_custom_options = unserialize(get_option('behavioral_custom_options'));



                      foreach ($unserialize_behavioral_custom_options[0] as $behavioral_post){
                          ?>
                           <option value="<?php echo  esc_attr($behavioral_post) ?>"><?php echo esc_html($behavioral_post) ?></option>
                          <?php
                      }

                       ?>

                       </select>
               </table>
           <input id="behavioral_custom_options_metabox_submit"   class="button button-primary button-large" type="button" value="Submit" name="behavioral_custom_options_metabox_submit"/>

       </form>



 <script>
        jQuery(document).ready(function($){

            var  behavioral_custom_options_metabox_submit = document.getElementById("behavioral_custom_options_metabox_submit");

            var  jsObj = {};



            $('.behavioral_custom_options_metabox').each(function(index){

                $(this).on("change",function(evt){

                    var that = this;
                    jsObj[index] = $(this).val();
                   


                    behavioral_custom_options_metabox_submit.onclick = function(){

                        jsObj['action'] ='behavioral_meta_box_http';
                        jsObj['post_id'] =  ubp_ajax_object_backend.post_id;


                        jQuery.post(ajaxurl,jsObj,function(response){

                            //  console.log(response);
                           
                        });
                    };

                });

            });

       });
    </script>
   

  <?php

}




add_action('wp_ajax_behavioral_meta_box_http','behavioral_meta_box_http_callback');




/**
 * Save Behavioral History HTTP Post in Database
* @param $post_id
 */


function behavioral_meta_box_http_callback(){


    if(isset($_POST['post_id']) && isset($_POST['action']) && isset($_POST[0])){


       

        
         $_SESSION['behavioral_meta_box_post_id'] = $_POST['post_id'];
         $_SESSION['behavioral_meta_box_visitor_history'] = $_POST[0];



       // print_r($_POST[0]);


        /*****If auto saving skip saving our meta box data*****/
        if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
           return;
        }

        /***Check for security**/
        wp_verify_nonce(plugin_basename(__FILE__),'ubp_save_meta_box');

       

       delete_post_meta($_SESSION['behavioral_meta_box_post_id'],'_visitor_history');

       if(update_post_meta($_SESSION['behavioral_meta_box_post_id'],'_visitor_history', $_SESSION['behavioral_meta_box_visitor_history'] )){

               echo "Tracking visitor successfully" . "<br/>";


         }else{
            echo "Error tracking visitor";
         }

     wp_die();
                  


    }
}



add_action('wp_ajax_nopriv_visitor_history','visitor_history_callback');


   /*****
    Visitor History Callback

     Check if post has visitor history metadata
    ***/
    function visitor_history_callback(){
    global $wpdb;
    $unique_id= $_SESSION['visitor'];




       if(isset($_POST['visitor_history_meta_data']) && isset($_POST['track_current_page']) && isset($_POST['home_url'])){

        

      
          /// echo $_POST['visitor_history_meta_data'];;
          // print_r($_POST['visitor_history_meta_data']);

           //track_current_page

          for($i =0; $i< count($_POST['visitor_history_meta_data'][0]); $i++){
                       $visitor_history_meta_data = $_POST['visitor_history_meta_data'][0][$i];
                       


                                  if($result =$wpdb->get_results("SELECT DISTINCT unique_id, track_post  FROM wp_ubp_visitor_track_post_data
                                                             WHERE unique_id = '$unique_id'",OBJECT)){
                                                                    /*******Fetch pot user has visit from database******/
                                                
                                                 /**
                                                    Activity will only happen if current post 
                                                    visitor is has visitor metadata attached to it.
                                                   And If visitor has visited pages in visitor metada, only show them current page
                                                     ***/


                                                   
                                             foreach($result as $results){

                                                    if($results->track_post === $visitor_history_meta_data){
                                                      
                                                       echo "Behavior Tracked";
                                                     
                                                    }
                                             }
 
                                                   
                                                    
                                   }else{
                                    echo $result;
                                   }

                                   
                
          

           }
          
             wp_die();
       }
        //wp_die();
    }























function behavioral_post_page(){

    ?>

          
       <section>
       <label id="ubp_remove_data_label">
       <h2> Remove User Behavioral Post Data</h2>
       </label>
         <br/>
         <input type="checkbox" name="ubp_remove_data" id="ubp_remove_data"/>

         <button>Submit</button>
       </section>

            <script>
  jQuery(document).ready(function($){

      var  ubp_remove_data = document.querySelector("#ubp_remove_data");
      var  jsObj = {};
      jsObj['action'] ='ubp_remove_data';
       

      ubp_remove_data.onclick = function(){


             if(ubp_remove_data.checked){
                           
                 jQuery.post(ajaxurl,jsObj,function(response){

                         if(response){
                                   document.getElementById("ubp_remove_data_label").innerHTML = "Successfully removed user behavioral post data";
                         }

                           
               });
                         

             }
        };

});

</script>

    <?php

}


add_action('wp_ajax_ubp_remove_data','ubp_remove_data_callback');

function ubp_remove_data_callback(){

  global $wpdb;
          
 if($_POST['action']){
    $tables = array('wp_ubp_visitor_data','wp_ubp_visitor_track_post_data');
  foreach($tables as $table) {
      if ($result = $wpdb->query("DELETE  FROM {$table}")) {
         echo "Success removing data";

      } else {
          write_log($result);
          write_log("Error");
      }
  }
 }

     wp_die();

}




