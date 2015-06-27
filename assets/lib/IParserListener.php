<?PHP
    interface IParserListener{
        public function onBeforeStartParsing();
        public function onParsingComplete();
    }
?>
