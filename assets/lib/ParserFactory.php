<?PHP
class ParserFactory{
    const PARSER_PATH = '../assets/lib/Parsers';

    public static function createParser( $parserName ){
        if( is_string($parserName) === false) return false;
        $aFiles = self::getParserList();
        $key = array_search( $parserName, $aFiles, true );
        $instance = new $aFiles[$key]();
        if( $instance instanceof IDocParser === false ) return false;
        return $instance;
    }

    public static function getParserList( ){
        $aFiles = scandir(self::PARSER_PATH);
        $aTmp   = array();

        $aFiles = array_filter( $aFiles, function($item){
            if( strpos($item, '.php') !== false ) return true;
            return false;
        });

        foreach( $aFiles as $value ){
            $tmp = explode('.',$value);
            $aTmp[] = $tmp[0];
        }
        return $aTmp;
    }
}
?>
