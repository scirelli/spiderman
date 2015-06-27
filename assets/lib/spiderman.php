<?PHP
ini_set('memory_limit', '-1');

/** ***********************************************
 * Class for spidering a webpage
 * @author: Steve Cirelli
 * @Dependences: IDocParser, WebUtils, AnchorParser, 
 *               CompositeDocParser, ITimer, SCTimerSimple
 *
 * TODO: Handle links with a hash in them
 * *************************************************/
class Spiderman{
    private $aLinkStack;
    private $docParsers;
    private $anchorParser;
    private $aDomainLimits;
    private $aDomainLimitsSz;
    private $aExcludeDomains; 
    private $aExcludeDomainsSz; 
    private $aTraversedLinks;
    private $aEventListeners;
    private $storeStateTimer;
    private $totalTime;

    const STORE_STATE_AFTER = 10;//seconds
    const STATE_FILE_NAME   = 'spiderState';
    const ERROR_MSG_NO_ROOT = 'Must provide a root link.';
    const ERROR_MSG_IDOC    = 'Spiderman.addParser can only add IDocParsers.';

    /** ***********************************************
     * Constructor
     * @param: $rootLink string; the page to begin spidering
     * @param: $domainLimits string or array of strings; a list of domains
     *         you want to stay with in. 
     *         example of domains stevecirelli.com or www.stevecirelli.com/path
     * @return: null
     * @throws: Exception
     * ************************************************/
    function __construct( $rootLink, $domainLimits = array(), $aExcludeDomains = array() ){
        if( $rootLink == false ) throw new Exception(self::ERROR_MSG_NO_ROOT);
        $this->storeStateTimer = new TimerSimple();
        $this->totalTime       = new TimerSimple();
        $this->aLinkStack      = array();
        $this->aTraversedLinks = array();
        $this->aDomainLimits   = array();
        $this->aEventListeners = array();
        $this->docParsers      = new CompositeDocParser();
        $this->anchorParser    = new AnchorParser();
        $this->push( $rootLink );
        $this->addParser( $this->anchorParser );
        if( is_string($domainLimits) ) {
            $this->setDomainLimits( array( $domainLimits ) );
        }else{
            $this->setDomainLimits( $domainLimits );
        }
        $this->aDomainLimitsSz   = count($this->aDomainLimits);

        if( is_string($aExcludeDomains) ) {
            $this->setExcludeDomainLimits( array($aExcludeDomains) );
        }else{
            $this->setExcludeDomainLimits( $aExcludeDomains );
        }
        $this->aExcludeDomainsSz = count($this->aExcludeDomains);
    }

    /** ************************************************
     * Register Listeners
     * @param: $listener IParserListener; 
     * @return: boolean; true if registered false if not.
     ***************************************************/
    public function regsiterListener( $listener ){
        if( $listener instanceof IParserListener ){
            $this->aEventListeners[] = $listener;
            return true;
        }
        return false;
    }

    /** ************************************************
     *
     * @param:
     * @return: null
     ***************************************************/
    public function crawl(){
        $this->storeStateTimer->start();
        $this->totalTime->start();

        foreach( $this->aEventListeners as $cb ){
            $cb->onBeforeStartParsing();
        }
        while( $link = array_pop( $this->aLinkStack ) ){
            $doc = file_get_contents($link);
            if( $doc === false ) $doc = '';

            echo $this->sSay( 'Removing URL from Spidey Pouch: ', $link );
            echo $this->sSay( 'Let me run some parsers...', '' );
            echo '<div class="parserBox">';
            $this->docParsers->setURL( $link );
            $this->docParsers->setDocument( $doc );
            $this->docParsers->parse();
            echo '</div>'; 

            $this->pushArray($this->anchorParser->getAnchorsArray());
            
            //Check to see if we should store Spidey's state
            if( $this->storeStateTimer->elapsed() >= self::STORE_STATE_AFTER  ){
                $this->storeState();
                $this->storeStateTimer->resetTimer();
            }
            echo $this->sSay( 'Total elapsed time is ' , $this->totalTime->elapsed() . ' sec(s)');
        }
        foreach( $this->aEventListeners as $cb ){
            $cb->onParsingComplete();
        }
        echo $this->sSay( 'Total elapsed time is ' , $this->totalTime->elapsed() . ' sec(s)');
        $this->totalTime->stop();
    }
    
    /** ************************************************
     *
     * @param:
     * @return: null
     ***************************************************/
    public function addParser( $parser ){
        if( $parser instanceof IDocParser ){
            $this->docParsers->add( $parser );
        } else {
            throw new Exception(self::ERROR_MSG_IDOC);
        }
    }

    /** ************************************************
     * Put a valid URL on the stack 
     * @param: $sLink string; A url
     * @return: null
     * @throws: Exception
     ***************************************************/
    public function push( $sLink ){
        if( is_string( $sLink ) && WebUtils::isValidURL($sLink) ){
            if( $this->isWithInDomain( $sLink ) && $this->hasNotBeenPushed($sLink) && !$this->isAnExcludedDomain($sLink) ){
                $this->aLinkStack[] = $sLink;
                $this->pushTraversedLinks($sLink);
                echo $this->sSay( 'I\'m going put this URL in my Spidey Pouch to crawl later', $sLink );
            }
        } else {
            $this->sSayError('Spiderman.push only accepts valid URL strings', $sLink);
            //throw new Exception('Spiderman.push only accepts valid URL strings \'' . $sLink . '\'' );
        }
    }
    
    /** ************************************************
     * Put a valid URL on the stack. Use the url as a key
     * to eliminate duplicates. It also makes sure the URL
     * is within $this->aDomainLimits.
     * @param: $sLink string; A url
     * @return: null
     * @throws: Exception
     * DEPRICATED use pushTraversed
     ***************************************************/
    public function pushToHash( $sLink ){
        if( is_string( $sLink ) &&WebUtils::isValidURL($sLink) ){
            if( $this->isWithInDomain( $sLink ) ){
                $this->aLinkStack[$sLink] = $sLink;
                echo '<span style="margin-left:20px;">Starting with: </span>' . $sLink . '<br/>';
            }
        } else {
            throw new Exception('Spiderman.pushToHash only accepts valid URL strings \'' . $sLink . '\'');
        }
    }

    public function restoreState(){
        $this->restoreStack();
        $this->restoreTraversed();
    }
    
    /** ************************************************
     * @param: $sLink string; A url
     * @return: true if domain has not been pushed. False if it has
     * @throws:
     ***************************************************/
    private function hasNotBeenPushed( $sLink ){
        $d = WebUtils::getDomain($sLink);
        //Check to see if this domain is just the top level domain and a domain.
        //If so then we want to add the default subdomain of www
        if( count(explode('.',$d)) == 2){//Not sure if this is always true. stevecirelli.com has 1 . but stevecirelli has none it maps to a ip address too
            $sLink = str_ireplace( $d, 'www.'.$d, $sLink );
        }
        return !isset($this->aTraversedLinks[$sLink]);
    }

    /** ************************************************
     * Put a valid URL on the traversed links list.
     * This list keeps track of visted urls
     * @param: $sLink string; A url
     * @return: null
     * @throws: Exception
     ***************************************************/
    private function pushTraversedLinks( $sLink ){
        $d = WebUtils::getDomain($sLink);
        //Check to see if this domain is just the top level domain and a domain.
        //If so then we want to add the default subdomain of www
        if( count(explode('.',$d)) == 2){//Not sure if this is always true. stevecirelli.com has 1 . but stevecirelli has none it maps to a ip address too
            $sLink = str_ireplace( $d, 'www.'.$d, $sLink );
        }

        $this->aTraversedLinks[$sLink] = true;
    }

    /** ************************************************
     * Put an array of valid URLs on the stack using 
     * push() 
     * @param: $aLink array; An array of urls
     * @return: null
     * @throws: Exception
     ***************************************************/
    private function pushArray( $aLinks ){
        if( !is_array($aLinks) ) return;
        foreach( $aLinks as $sLink ){
            $this->push( $sLink );
        }
    }
    
    /** ************************************************
     * Sets the limits for the crawler
     * @param: $aDomains array; a list of domains
     * @return: null
     * @throws: Exception
     ***************************************************/
    private function setDomainLimits( $aDomains ){
        if( !is_array($aDomains) ) return;
        foreach( $aDomains as $sDom ){
            $sDom = trim($sDom);
            if( WebUtils::isValidDomain( $sDom ) ){
                $this->aDomainLimits[] = $sDom;
            }
        }
    }

    private function setExcludeDomainLimits( $aExcludeDomains ){
        if( !is_array($aExcludeDomains) ) return;
        foreach( $aExcludeDomains as $sDom ){
            $sDom = trim($sDom);
            if( WebUtils::isValidDomain( $sDom ) ){
                $this->aExcludeDomains[] = $sDom;
            }
        }
    }

    /** ************************************************
     * Put an array of valid URLs on the stack using 
     * pushToHash() 
     * @param: $aLink array; An array of urls
     * @return: null
     * @throws: Exception
     * DEPRICATED use push instead.
     ***************************************************/
    private function pushArrayToHash( $aLinks ){
        if( !is_array($aLinks) ) return;
        //echo 'Hash push of: ' . implode($aLinks, '<br/>') . '<br/>';
        foreach( $aLinks as $sLink ){
            $this->pushToHash( $sLink );
        }
    }

    /** ************************************************
     * Domains you want to stay within
     * @param: $sLink string; url
     * @return: boolean: true if with in domain, false otherwise
     * @throws: Exception
     ** ************************************************/
    private function isWithInDomain( $sLink ){
        if( $this->aDomainLimitsSz <= 0 ) return true;
        if( !is_string($sLink) ) throw new Exception('Spiderman->isWithInDomain() only expects a string as a parameter.' . $sLink );
        foreach( $this->aDomainLimits as $url ){
            //echo '<span style="margin-left:40;">Domain Compare: </span>' . $url . ' <b>in</b> ' . $sLink ;
            if( strpos( WebUtils::getDomain($sLink), $url ) !== false ){
                //echo ' <b>True</b> <br/>';
                return true;
            }
        }
        //echo ' <b>False</b> <br/>';
        return false;
    }

    /** ************************************************
     * Domains you want to avoid
     * @param: $sLink string; url
     * @return: boolean: true if an exluded domain, false otherwise
     * @throws: Exception
     ** ************************************************/
    private function isAnExcludedDomain( $sLink ){
        if( $this->aExcludeDomainsSz <= 0 ) return false;
        if( !is_string($sLink) ) throw new Exception('Spiderman->isAnExcludedDomain() expects a string as a parameter.' . $sLink);
        foreach( $this->aExcludeDomains as $url ){
            if( strpos( WebUtils::getDomain($sLink), $url ) !== false ){
                return true;
            }
        }
        return false;
    }

    private function storeState(){
        echo $this->sSay( $this->storeStateTimer->elapsed() . 'sec(s) has elpased. I\'m going to store my state.', '');
        $this->storeStack();
        $this->storeTraversedLinks();        
        echo $this->sSay( 'Storing of state completed at ', $this->storeStateTimer->elapsed());
    }

    private function storeStack(){
        $stackFile     = self::STATE_FILE_NAME . '_brn.txt';
        $fh = fopen($stackFile, 'w') or die('cant find file');// $fh = fopen($myFile, 'r+');
        $msg = '';
        foreach( $this->aLinkStack as $link ){
            $msg .= $link . PHP_EOL;
        }
        fwrite($fh, trim($msg));
        fclose($fh);
    }

    private function storeTraversedLinks(){
        $traversedFile = self::STATE_FILE_NAME . '_mem.txt';
        $fh = fopen($traversedFile, 'w') or die('cant find file');// $fh = fopen($myFile, 'r+');
        $msg = '';
        $aKeys = array_keys($this->aTraversedLinks);
        foreach( $aKeys as $link ){
            $msg .= $link . PHP_EOL;
        }
        fwrite($fh, trim($msg));
        fclose($fh);
    }

    private function restoreStack(){
        $stackFile     = self::STATE_FILE_NAME . '_brn.txt';
        $fh = fopen($stackFile, 'r') or die('cant find file');// $fh = fopen($myFile, 'r+');
        if ($fh) {
            while (($buffer = fgets($fh)) !== false) {
                $this->aLinkStack[] = $buffer;
                echo $this->sSay('Restoring Spidey Pouch link: ' , $buffer);
            }
            if (!feof($fh)) {
                echo $this->sSayError("restoreStack() Error: unexpected fgets() fail!", '');
            }
            fclose($handle);
        }
    }

    private function restoreTraversed(){
        $traversedFile = self::STATE_FILE_NAME . '_mem.txt';
        $fh = fopen($traversedFile, 'r') or die('cant find file');// $fh = fopen($myFile, 'r+');
        if ($fh) {
            while (($buffer = fgets($fh)) !== false) {
                $this->aTraversedLinks[$buffer] = 1;
                echo $this->sSay('Restoring traversed link: ' , $buffer);
            }
            if (!feof($fh)) {
                echo $this->sSayError("restoreTraversed() Error: unexpected fgets() fail!", '');
            }
            fclose($handle);
        }
    }

    private function sSay( $sMsg, $sVar ){
        $str = '<span class="speak">Spiderman says:</span> %s \'<i>%s</i>\'<br/>';
        return sprintf( $str, $sMsg, $sVar );
    }
    private function sSayError( $sMsg, $sVar ){
        $str = '<span class="speakError">Spiderman says:</span>%s \'<i>%s</i>\'<br/>';
        return sprintf( $str, $sMsg, $sVar );
    }
}
?>
