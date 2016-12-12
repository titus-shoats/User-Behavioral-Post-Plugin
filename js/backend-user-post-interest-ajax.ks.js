
jQuery(document).ready(function($) {

var behavioral_post_http = {
    behavioral_post_http:'behavioral_post_http'
};

jQuery.post(ubp_ajax_object_backend.ajax_url, behavioral_post_http, function(response) {
   console.log(response);
});



var generate_metabox_behavior_data = {
    action: 'behavioral_meta_box_http',
    post_id:ubp_ajax_object_backend.post_id

};

jQuery.post(ubp_ajax_object_backend.ajax_url, generate_metabox_behavior_data, function(response) {
   console.log(response);
});


});