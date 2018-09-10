<?php

error_reporting(0); // Set E_ALL for debuging

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinderConnector.class.php';
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinder.class.php';
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';


/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume)
{
    return strpos(basename($path), '.') === 0 // if file/folder begins with '.' (dot)
        ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
        : null; // else elFinder decide it itself
}

session_start();

if (isset($_SESSION['auth']) && (($_SESSION['auth']['accessRight'] == '1') || ($_SESSION['auth']['accessRight'] == '2'))) {
    $opts = array(
        // 'debug' => true,
        'roots' => array(
            array(
                'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
                'path' => '../../../userfiles/', // path to files (REQUIRED)
                'URL' => '/userfiles/', // URL to files (REQUIRED)
                'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
                'mimeDetect' => 'internal',
                'imgLib' => 'gd',
                'uploadDeny' => array('all'),
                'uploadAllow' => array(
                    'image/gif',
                    'image/jpeg',
                    'image/png',
                    'application/flash-video',
                    'application/vnd.ms-word',
                    'application/vnd.ms-excel',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/rtf',
                    'application/pdf',
                    'application/x-shockwave-flash',
                    'text/plain',
                    'video/mpeg',
                    'video/quicktime',
					'application/zip',
                    'application/x-rar',
					'application/x-rar-compressed',
					'application/x-7z-compressed'
                ),
                'uploadOrder' => 'deny,allow'
            )
        )
    );

// run elFinder
    $connector = new elFinderConnector(new elFinder($opts));
    $connector->run();
}

