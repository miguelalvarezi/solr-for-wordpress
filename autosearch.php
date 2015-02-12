<?php

function s4w_simple_query($qry, $offset, $count) {
    $response = NULL;
    
    $plugin_s4w_settings = s4w_get_option();
    $server = $plugin_s4w_settings['s4w_server']['type']['search'];
    $solr = s4w_get_solr($server);

    if ( $solr ) {
        $params = array();
        $params['fl'] = 'title,permalink';
        
        try { 
            $response = $solr->search($qry, $offset, $count, $params);
            if ( ! $response->getHttpStatus() == 200 ) { 
                $response = NULL; 
            }
        }
        catch(Exception $e) {
            syslog(LOG_ERR, "failed to query solr for " . print_r($qry, true) . print_r($params,true));
            $response = NULL;
        }
    }
    
    return $response;
}

function wdm_autosearch() {
    $search_que = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
    if (preg_match ('/^[a-zA-Z0-9 ]*$/', $search_que)) {

        $blogid = get_current_blog_id();
        $res = s4w_simple_query("blogid:$blogid AND text:$search_que*", 0, $_REQUEST['limit']);

        $result = json_encode(array_map(function ($doc) {
            return array('permalink' => $doc->permalink,
                'title' => $doc->title);
        }, $res->response->docs));

        echo $result;
    }
    die();
}

add_action('wp_ajax_wdm_autosearch', 'wdm_autosearch');
add_action('wp_ajax_nopriv_wdm_autosearch', 'wdm_autosearch');