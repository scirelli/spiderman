<?PHP
/** ************************************************
 * 
 * @author: Steve Cirelli
 * @Dependents: IDocParser, WebUtils
 ***************************************************/
class LogParser implements IDocParser{
    private $sURL;
    private $document;

    function __construct(){
    }

    /** ************************************************
     * Trims whitespace off the url, then removes the /
     * off the right side of the url if it exists
     * @param:
     * @return: null
     * @throws:
     ** ***********************************************/
    public function setURL( $url ){
        if( is_string($url) && WebUtils::isValidURL($url) ){
            $url = rtrim(trim($url),'/');
            $this->sURL = $url;
        }else{
            throw new Exception('LogParser.setURL accepts valid URLs only.');
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
        if( $this->document == false ){
            $this->logToFile( $this->sURL . PHP_EOL . ' Could not retrieve document.');
        }else{
            $this->logToFile( $this->sURL );
        }
    }
    
    private function logToFile( $msg, $openFor = 'a', $fileName = 'log.txt'){
        $myFile = $fileName;
        
        $beginDate = new DateTime();
        $beginDate = $beginDate->format('m/d/Y h:m:s');
        $msg = '**********************************' . $beginDate . '***********************************************************************************************************************************************************************************************************' . PHP_EOL . $msg;
        $fh = fopen($myFile, $openFor) or die('cant find file');// $fh = fopen($myFile, 'r+');
        $msg .= PHP_EOL . '**********************************************************************************************************************************************************************************************************';
        fwrite($fh, $msg);

        fclose($fh);
    }
}
?>
