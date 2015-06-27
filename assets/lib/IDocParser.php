<?PHP
interface IDocParser{
    public function setURL( $url );
    public function setDocument( $document );
    public function parse();
}
?>
