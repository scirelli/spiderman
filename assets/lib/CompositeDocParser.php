<?PHP
class CompositeDocParser implements IDocParser{
    private $aParsers;

    function __construct(){
        $this->aParsers = array();
    }

    public function setURL( $url ){
        for($i=0, $l=count($this->aParsers); $i<$l; $i++){
            $this->aParsers[$i]->setURL( $url );
        }
    }

    public function setDocument( $document ){
        for($i=0, $l=count($this->aParsers); $i<$l; $i++){
            $this->aParsers[$i]->setDocument( $document );
        }
    }

    public function parse(){
        for($i=0, $l=count($this->aParsers); $i<$l; $i++){
            $this->aParsers[$i]->parse();
        }
    }

    public function add( $parser ){
        if( $parser instanceof IDocParser ){
            $this->aParsers[] = $parser;
        }
    }
}
?>
