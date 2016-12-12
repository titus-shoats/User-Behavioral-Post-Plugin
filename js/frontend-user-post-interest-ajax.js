jQuery(document).ready(function($) {

    /****Create Ajax calls for wordpress, and creates global vars for wordpress in head of pages**/
    var visitor_data = {
        action: 'ubp_track_visitor',
        track_current_page:ubp_ajax_object_frontend.track_current_page

    };

    jQuery.post(ubp_ajax_object_frontend.ajax_url, visitor_data, function(response) {
      console.log(response);
    });


var visitor_history_meta_data = {
        action: 'visitor_history',
        visitor_history_meta_data:ubp_ajax_object_frontend.visitor_history_meta_data,
        track_current_page:ubp_ajax_object_frontend.track_current_page,
        home_url:ubp_ajax_object_frontend.home_url

    };

    jQuery.post(ubp_ajax_object_frontend.ajax_url, visitor_history_meta_data, function(response) {
        if(response === "Behavior Tracked"){
              window.location.replace("http://google.com");
        }else{
            console.log(response);
        }
       
    });

   

});