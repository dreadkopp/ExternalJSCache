<?php
/**
 * Created by PhpStorm.
 * User: arne
 * Date: 24.03.19
 * Time: 16:20
 */

namespace includes\classes;

class externalJSUpdater
{

    const CACHETIME = 86400; //one day

    const externalJS = [
        'bat.js' => 'http://bat.bing.com/bat.js',
        'ga.js' => 'https://ssl.google-analytics.com/ga.js',
        'analytics.js' => 'https://www.google-analytics.com/analytics.js',
        'googletagmanager.js' => 'https://www.googletagmanager.com/gtag/js?id=UA-57624184-1',
        'fbevents.js' => 'https://connect.facebook.net/en_US/fbevents.js',
        'fb_signals.js' => 'https://connect.facebook.net/signals/config/625777857560549?v=2.8.42&r=stable',
    ];

    const specialNeeds = [
        'googletagmanager.js' => ['replace' => ['https://www.google-analytics.com/analytics.js','/localized_js/analytics.js']],
        'fbevents.js' => ['replace' => ['d.CONFIG.CDN_BASE_URL+"signals/config/"+a+"?v="+b+"&r="+c','"/localized_js/fb_signals.js"']],
    ];

    public static function checkAndUpdate() {

        foreach (self::externalJS as $localname => $externalURL) {

            $path = DIR_FS_DOCUMENT_ROOT .'/localized_js/' . $localname;

            if (file_exists($path) && (time()-filemtime($path) > self::CACHETIME - 10)) {
                unlink($path);
                self::redownload($externalURL,$path, $localname);
            } else {
                if (!file_exists($path)) {
                    self::redownload($externalURL,$path, $localname);
                }
            }
        }
    }

    private static function redownload($url , $storepath, $localname) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $script = curl_exec($ch);
        curl_close($ch);

        if (array_key_exists($localname,self::specialNeeds)) {
            $action = key(self::specialNeeds[$localname]);
            switch ($action) {
                case 'replace' : $script = self::replaceInFile($script, self::specialNeeds[$localname][$action]);break;
                default: break;
            }

        }

        file_put_contents($storepath,$script);
    }

    private static function replaceInFile($script, $arrayFindReplace) {
        $find = $arrayFindReplace[0];
        $replace = $arrayFindReplace[1];
        $str = str_replace($find,$replace,$script);
        return $str;
    }
}