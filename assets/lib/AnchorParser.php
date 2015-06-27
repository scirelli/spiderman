<?PHP
/** ************************************************
 * Class to remove HREF links from an anchor tag.
 * @author: Steve Cirelli
 * @Dependents: IDocParser, WebUtils
 ***************************************************/
class AnchorParser implements IDocParser{
    //const ANCHOR_REGEX = '|(*ANY)(<\s*a\s+.*?href\s*=\s*["\']{1}(.*?)["\']{1}[^>]*>.*?</[^>]+>)+?|i';
    
    private $sURL;
    private $document;
    private $aAnchors;
    private $sBaseURL;
    private $sCurrentPathURL;

    function __construct(){
        $this->aAnchors = array();
    }

    /** ************************************************
     * Trims whitespace off the url, then removes the /
     * off the right side of the url if it exists.
     * Gets the base url and the url with just it's path
     * @param:
     * @return: null
     * @throws:
     ** ***********************************************/
    public function setURL( $url ){
        if( is_string($url) && WebUtils::isValidURL($url) ){
            $url = rtrim(trim($url),'/');
            $this->sURL = $url;

            $base = WebUtils::getBaseURL($url);
            if( $base == '' ) $base = $url;
            $this->sBaseURL = $base;

            $path = WebUtils::getCurrentPathURL($url);
            if( $path == '' ) $path = $url;
            $this->sCurrentPathURL = $path;
        }else{
            throw new Exception('AnchorParser.setURL accepts valid URLs only.');
        }
    }

    /** ************************************************
     *
     * @param:
     * @return: null
     * @throws:
     ** ***********************************************/
    public function setDocument( $document ){
        $this->document = $document;
    }

    /** ************************************************
     *
     * @param:
     * @return: null
     * @throws:
     ** ***********************************************/
    public function parse(){
        $this->aAnchors = null;
        $this->aAnchors = $this->getAnchorLinks( $this->document );
        $this->processURLs();
    }
    
    /** ************************************************
     * Returns an array of the href's parsed from the 
     * document. The url's are trimmed of whitespace
     * and pass the WebUtils::isValidURL() method.
     * @return: array of valid urls
     ** ***********************************************/
    public function getAnchorsArray(){
        return $this->aAnchors;
    }

    /** ************************************************
     * Parses the href out of an HTML anchor tag. 
     * @param: $htmlString string; A string formatted in 
     *      HTML containing the anchor links you want parsed
     * @return: array of anchor links. <a href="{Anchor link}"></a>
     ***************************************************/
    private function getAnchorLinks( $htmlString ){
        $matches = array();
        preg_match_all( WebUtils::ANCHOR_REGEX, $htmlString, $matches, PREG_PATTERN_ORDER );
        //echo '<pre>'.var_export($matches[count($matches)-1], true).'</pre>';
        return $matches[ count($matches) - 1 ];
    }

    /** ************************************************
     * Turns all relative URLs into absolute URLs
     * @return: null
     * Notes: 
     *   Different forms of urls
     *       /dir/
     *       /dir
     *       /dir/index.php
     *       dir/index.php
     *       dir/index.php?var=v
     *       index.php
     *       /index.php
     *       ./dir
     *       ../../dir
     *       <a href="javascript:;" class="prev"></a>
     ***************************************************/
    private function processURLs(){
        $tmpArray = array();
        foreach( $this->aAnchors as $url ){
            $url = trim($url);
            echo $this->sSay('I\'m going to process url: ', $url);
            if( WebUtils::isValidURL($url) ){
                $url = rtrim($url,'/');
                $tmpArray[] = $url;
                //echo $this->sSay('After Processing url: ', $url);
            }else{
                if( strlen($url) <= 0 ) continue;
                if( $this->containsJS($url) ) continue;
                if( $this->containsHash($url) ) continue;//TODO: Not sure about this might not need it. URLs can contain hashes but link to a differnt page?
                if( $this->containsMailTo($url) ) continue;

                if( strpos($url, '/') === 0 ){//means root.
                    $url = rtrim($url,'/');
                    $tmpArray[] = $this->sBaseURL . $url;
                }else if( strpos($url, './') === 0 || strpos($url, '../') === 0 ){//realative path try and travers
                    $url = rtrim($url,'/');
                    echo $this->sSay('I\'ve found a relative path ', $url);
                    $rtn = WebUtils::traversRelPath( $this->sURL, $url );
                    echo $this->sSay('I\'ve tried to travers it and arrived at ', $rtn);
                    if( WebUtils::isValidURL($rtn) ){
                        $tmpArray[] = $rtn;
                    }else{
                        echo $this->sSay('Relative url parse failed. url is', $rtn);
                    }
                }else{//who knows just add it;
                    $url = rtrim($url,'/');
                    echo $this->sSay('Not sure what to do with this url: ',$url);
                    $tmpArray[] = $this->sCurrentPathURL . '/' . $url;
                    echo $this->sSay('So I\'ll add on the current path: ', $this->sCurrentPathURL . '/' . $url);
                }
                //echo $this->sSay('After Processing url: ', $tmpArray[count($tmpArray)-1]);
            }
        }
        $this->aAnchors = null;
        $this->aAnchors = $tmpArray;
    }

    private function containsJS( $url ){
        $matches  = array();
        //preg_match( '@^(\.{1}/)|^(\.{2}/)@', $url, $matches, PREG_OFFSET_CAPTURE );
        preg_match( '@javascript:@i', $url, $matches, PREG_OFFSET_CAPTURE );
        return count($matches) > 0;
    }

    private function containsHash( $url ){
        $matches = array();
        preg_match( '@#@', $url, $matches, PREG_OFFSET_CAPTURE );
        return count($matches) > 0;
    }

    private function containsMailTo( $url ){
        $mail = 'mailto:';
        $t = strpos( $url , $mail );
        if( $t !== false ) return true;
        return false;
    }

    private function sSay( $sMsg, $sVar ){
        $str = '<span class="speak">AnchorParser says:</span> %s \'<i>%s</i>\'<br/>';
        return sprintf( $str, $sMsg, $sVar );
    }
}
?>
