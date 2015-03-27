<?php

use Respect\Validation\Validator as v;

require_once "vendor/autoload.php";

/**
 * Outputs an error of invalid URL
 */
function outputError() {
    echo json_encode(array('success' => false, 'error' => 'One or more URLs are invalid'));
    exit;
}

/**
 * Makes sure all of the URLs are valid
 * @param string $url URL to check
 */
function validateUrl($url) {
    $parse = parse_url($url);

    // Query may be missed, use default
    $parse['query'] = (!empty($parse['query'])) ? $parse['query'] : '';

    $check = v::arr()
        ->key('scheme', v::startsWith('http'))
        ->key('host',   v::domain()->oneOf(v::equals('facebook.com'), v::equals('www.facebook.com')))
        ->key('path',   v::string())
        ->key('query',  v::oneOf(v::startsWith('id='), v::string()))
        ->validate($parse);

    if (!$check) {
        outputError();
    }
}

/**
 * Determines Graph API URL for each Facebook page
 * 
 * @param string $url Page URL
 * @return string Graph API URL
 */
function getGraphUrl($url) {

    // Use graph API to get data
    $graphURL = 'http://graph.facebook.com';

    $parse = parse_url($url);
    
    if (!empty($parse['query'])) {
        // If there's profile id, use it. It MUST be the only parameter
        preg_match('/^id=(\d+)$/', $parse['query'], $matches);

        // Something gone bad?
        if (empty($matches)) {
            outputError();
        }

        // OK
        $graphURL .= '/'.$matches[1];
    } else {
        $graphURL .= $parse['path'];
    }
    
    return $graphURL;
}

error_reporting(0);

//$urls = $_POST['urls'];
$urls = array('http://facebook.com/VasyaTheCat', 'http://facebook.com/johncena');

// Validate URLs
foreach ($urls as $url) {
    validateUrl($url);
}

// Graph API URL depends on URL params
foreach ($urls as $url) {
    $graphURL = getGraphUrl($url);
    $graphJSON = file_get_contents($graphURL);
    
    // is data found?
    if (!$graphJSON) {
        // leave it blank
        $res[] = array($url, '', '', '');
        continue;
    }
    
    // Parse json
    $graphData = json_decode($graphJSON);
    
    $res[] = array(
        $url,
        $graphData->name,
        $graphData->id,
        "https://www.facebook.com/feeds/page.php?format=rss20&id={$graphData->id}",
    );
}        

echo json_encode(array('success' => true, 'data' => $res));