<?php

use Respect\Validation\Validator as v;

require_once "vendor/autoload.php";

function outputError() {
    echo json_encode(array('success' => false, 'error' => 'Invalid URL'));
    exit;
}

error_reporting(0);

$url = $_POST['url'];

// Validate URL
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

// If there's 'www' in the URL remove it
$parse['host'] = str_replace('www.', '', $parse['host']);

// Use graph API to get data
$graphURL = 'http://graph.facebook.com';

// Graph API URL depends on URL params
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

// Collect information
$graphJSON = file_get_contents($graphURL);

// is page found?
if (!$graphJSON) {
    outputError();
}

// Parse json
$graphData = json_decode($graphJSON);

$res = array(
    'name'  => $graphData->name,
    'id'    => $graphData->id,
    'rss'   => "https://www.facebook.com/feeds/page.php?format=rss20&id={$graphData->id}",
    'page'  => $url,
);
    
echo json_encode(array('success' => true, 'data' => $res));
