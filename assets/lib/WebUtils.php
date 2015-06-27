<?PHP

class WebUtils{
    const ANCHOR_REGEX = '|(*ANY)(<\s*a\s+.*?href\s*=\s*["\']{1}(.*?)["\']{1}[^>]*>)+?|i';//|(*ANY)(<\s*a\s+.*?href\s*=\s*["\']{1}(.*?)["\']{1}[^>]*>.*?</[^>]+>)+?|i
    const FILE_REGEX   = '';
    const URL_FORMAT = '/^(https?):\/\/(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?@)?(?#)((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*[a-z][a-z0-9-]*[a-z0-9]|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5]))(:\d+)?)(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?)?)?(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?$/i';
    /*
       '/^(https?):\/\/'.                                         // protocol
       '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
       '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
       '@)?(?#'.                                                  // auth requires @
       ')((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
       '[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
       '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
       '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
       ')(:\d+)?'.                                                // port
       ')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
       '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
       '?)?)?'.                                                   // path and query string optional
       '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
       '$/i';
     */
    const BASE_URL = '@(.+?//.+?)/@'; //preg_match(regex, $url, $matches); matches[1] holds base url
    /*
       '/^(https?):\/\/'.                                         // protocol
       '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
       '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
       '@)?(?#'.                                                  // auth requires @
       ')((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
       '[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
       '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
       '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
       ')(:\d+)?'.                                                // port
       ')'.
       '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
       '$/i';
     */
    const DOMAIN = '@.+?//(.+?)/@'; //forgetting a domain preg_match(regex, $url, $matches); matches[1] holds domain
    const VALID_DOMAIN = '@^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}$@';//for validating a domain

    const MATCHES_PATH_QS      = 13;
    const MATCHES_PATH_QS2     = 14;
    const MATCHES_FILE         = 15;
    const MATCHES_QUERY_STRING = 17;
    const MATCHES_HASH         = 19;

    /** ************************************************
     * Validates a URL
     * @param: $url string; A string containing a url
     * @return: boolean true if $url is a valid url false if not.
     ***************************************************/
    public static function isValidURL( $url ){
        if (self::strStartsWith(strtolower($url), 'http://localhost')) {
            return true;
        }
        return (preg_match(self::URL_FORMAT, $url) == true);
    }

    /** ************************************************
     * Validates a domain
     * @param: $sDomain string; A string containing a domain
     * @return: boolean true if $sDomain is a valid domain false if not.
     ***************************************************/
    public static function isValidDomain( $sDomain ){
        $cnt = preg_match(self::VALID_DOMAIN, $sDomain);
        if( $cnt >= 1 ) return true;
        return false;
    }

    /** ************************************************
     * Test whether a string starts with another string
     * @param: $string string; the string to search
     * @param: $needle string; the item to find in $string
     * @return: boolean; true if found false if not
     ***************************************************/
    public static function strStartsWith($string, $needle) {
          return substr($string, 0, strlen($needle)) == $needle;
    }

    /** ************************************************
     * 
     * @param: $url string; a url
     * @return: string; the base url of url...stripping the path
     ***************************************************/
    public static function getCurrentPathURL( $sUrl ){
        $matches = array();
        preg_match( self::URL_FORMAT, $sUrl, $matches );
        if( count($matches) > 0 ){//if it's not a valid URL length will be 0
            $file = $qstring = $hash = '';
            if( isset($matches[self::MATCHES_FILE]) ){
                $file = $matches[self::MATCHES_FILE];
                $file = strpos($file, '.') === false ? '' : $file;
            }
            if( isset($matches[self::MATCHES_QUERY_STRING]) ){
                $qstring = $matches[self::MATCHES_QUERY_STRING];
            }
            if( isset($matches[self::MATCHES_HASH]) ){
                $hash = $matches[self::MATCHES_HASH];
            }
            $search = array( $file, $qstring, $hash );
            $sUrl = str_replace( $search, '', $sUrl );
            return rtrim($sUrl, '/');
        }
        return '';
    }

    /** ************************************************
     * 
     * @param: $url string; a url
     * @return: string; the base url of url...stripping the path
     ***************************************************/
    public static function getPathOnly( $sUrl ){
        $matches = array();
        preg_match( self::URL_FORMAT, $sUrl, $matches );
        if( count($matches) > 0 ){//if it's not a valid URL length will be 0
            $file = $qstring = $hash = '';
            if( isset($matches[self::MATCHES_FILE]) ){
                $file = $matches[self::MATCHES_FILE];
                $file = strpos($file, '.') === false ? '' : $file;
            }
            if( isset($matches[self::MATCHES_QUERY_STRING]) ){
                $qstring = $matches[self::MATCHES_QUERY_STRING];
            }
            if( isset($matches[self::MATCHES_HASH]) ){
                $hash = $matches[self::MATCHES_HASH];
            }
            $sUrl = $matches[self::MATCHES_PATH_QS];
            if( $sUrl != '' ){
                $search = array( $file, $qstring, $hash );
                $sUrl = str_replace( $search, '', $sUrl );
                return rtrim($sUrl, '/');
            }
        }
        return '';
    }

    /** ************************************************
     * 
     * @param: $url string; a url
     * @return: string; the base url of url...stripping the path
     ***************************************************/
    public static function getBaseURL( $sUrl ){
        if( !self::isValidURL($sUrl) ) return '';
        $matches = array();
        preg_match( self::BASE_URL, $sUrl, $matches );
        if( count($matches) >= 2 ){
            return $matches[1];
        }
        return '';
    }

    /** ************************************************
     * 
     * @param: $url string; a url
     * @return: string; the domain of the url...stripping the path
     ***************************************************/
    public static function getDomain( $sUrl ){
        if( !self::isValidURL($sUrl) ) return '';
        $matches = array();
        preg_match( self::DOMAIN, $sUrl, $matches );
        if( count($matches) >= 2 ){
            return $matches[1];
        }
        return '';
    }

    /** ************************************************
     * This function tries to travers the given url's path
     * but will not work if the realtive path is not traversable
     * with the given url.
     * @param: $url string; a url
     * @param: $relPath string; a relative path 
     * @return: string; the url after it was traversed by
     * relPath. Or false if there was some error/problem
     * TODO: Do a better job of adding / in the return string
     ***************************************************/
    public static function traversRelPath( $sUrl, $relPath ){
        if( !WebUtils::isValidURL($sUrl) ) return false;
        $baseP   = WebUtils::getCurrentPathURL($sUrl);
        $baseU   = WebUtils::getBaseURL($sUrl);
        $path    = WebUtils::getPathOnly($sUrl);
        $path    = explode('/', $path);
        $relPath = explode('/', $relPath);

        //echo '<pre>Path '. var_export($path, true) . '</pre><br/>';
        //echo '<pre>rel '. var_export($relPath, true) . '</pre><br/>';
        foreach( $relPath as $r ){
            if( $r != '.' && $r != '..' ){
                array_push( $path, array_shift($relPath));
                continue;
            }else if( $r == '.' ) {
                array_shift($relPath);
                continue;
            } else if( $r == '..' ){
                array_shift($relPath);
                if( array_pop($path) == null ) return false;
            }
        }
        return rtrim($baseU . implode('/', $path) . implode('/', $relPath), '/');
    }
}
?>
