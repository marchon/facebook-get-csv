<?php

use Respect\Validation\Validator as v;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;

require_once "vendor/autoload.php";

/**
 * Outputs an error of invalid URL
 */
function outputError() {
    echo 'One or more URLs are invalid';
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

/**
 * Sets download headers
 * @param string $filename
 */
function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

error_reporting(E_ALL);

$urls = $_POST['facebook-url'];

// Validate URLs
foreach ($urls as $url) {
    validateUrl($url);
}

// Headers for spreadsheet
$res[] = array('Page URL', 'Page id', 'Page name', 'Rss feed URL');

// Graph API URL depends on URL params
foreach ($urls as $url) {
    $graphURL = getGraphUrl($url);
    @$graphJSON = file_get_contents($graphURL);
    
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
        $graphData->id,
        $graphData->name,
        "https://www.facebook.com/feeds/page.php?format=rss20&id={$graphData->id}",
    );
}        

$config = new ExporterConfig();
$exporter = new Exporter($config);

download_send_headers("facebook_data_" . date("Y-m-d") . ".csv");
$exporter->export('php://output', $res);
die();