<?PHP
    require_once('../../constants.php');
    require_once(BASEPATH . 'lib/checkSet.php');
    require_once(BASEPATH . 'lib/ITimer.php');
    require_once(BASEPATH . 'lib/SCTimerSimple.php');
    require_once('../assets/lib/WebUtils.php');
    require_once('../assets/lib/IDocParser.php');
    require_once('../assets/lib/IParserListener.php');
    require_once('../assets/lib/CompositeDocParser.php');
    require_once('../assets/lib/Parsers/LogParser.php');
    require_once('../assets/lib/AnchorParser.php');
    require_once('../assets/lib/ParserFactory.php');
    require_once('../assets/lib/spiderman.php');

    $ffRoot    = checkSet('ffRoot', '', false);
    $ffLimits  = checkSet('ffLimits', null, false);
    $ffAvoid   = checkSet('ffAvoid', null, false);
    $ffParsers = checkSet('ffParsers','', false);

    if( $ffLimits  != null ) $ffLimits  = explode(',', $ffLimits); 
    if( $ffAvoid   != null ) $ffAvoid   = explode(',', $ffAvoid); 
    if( $ffParsers != null ){
        $ffParsers = explode(',', $ffParsers);
    }else{
        $ffParsers = array();
    }

?><html>
    <head>
        <style type="text/css">
            body{
                color:white;
            }
            .speak{
                font-weight:bold;
                color:darkred;
            }
            .speakError{
                font-weight:bold;
                color:red;
            }
            .parserBox{
                margin-left:20px; 
                border:1px solid darkblue; 
                padding:3px 0px 3px 3px
            }
        </style>
    </head>
    <body>
        <?PHP
            if( $ffRoot != '' ){
                $spider = new Spiderman($ffRoot, $ffLimits, $ffAvoid);//array('octoconsulting.com','www.octoconsulting.com')
                //echo var_export( $ffParsers, true );
                foreach( $ffParsers as $value ){
                    $parser = ParserFactory::createParser( $value );
                    //echo $parser instanceof IDocParser ? 'true' : 'false';
                    if( $parser !== false ){
                        $spider->addParser( $parser );
                    }
                }
                $spider->crawl();
            }else{
                echo '<div>Nothing to display</div>';
            }
    
            //---------- Test area -----------------
            /*
            $matches = array();
            $u   = 'http://www.steve.com/path1/path2/path3/path4?var=1';
            $rel = '.././../../../path1a/./path1ab/../path';
            $v   = WebUtils::isValidURL($u);
            echo $u . '<br/>';
            echo $rel . '<br/>';
            echo 'Valid url: ' . ($v == true ? 'true' : 'false') . '<br/>';

            if( strpos($rel, './') === 0 || strpos($rel, '../') === 0 ){
                $rtn = WebUtils::traversRelPath( $u, $rel );
                echo $rtn == null? 'failed' : $rtn;
            }
             */
            /*
            $ffurl = 'http://octoconsulting.com:8080/path.php?var=1#hash';
            $matches = array();
            echo preg_match( WebUtils::URL_FORMAT, $ffurl, $matches ) + ' s';
            echo '<pre>' . var_export($matches, true) . '</pre>';
            */
            /*
            $ffurl = '';
            echo WebUtils::isValidURL( $ffurl ) ? 'Yes' : 'No';
            */

            /*
            $ffurl = 'http://cra.octoconsulting.co';
            $regEx = '@(.+?//.+?)/@';
            $regEx = '@^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}$@';

            $matches = array();
            echo preg_match( $regEx, $ffurl, $matches ) + ' s';
            var_dump($matches);
            */
            //$matches = array();
            //$url = './steve';
            //preg_match_all( '@((\.{1}/)+?|(\.{2}/)+?)@', $url, $matches, PREG_OFFSET_CAPTURE );
            //preg_match( '@^(\.{1}/)|^(\.{2}/)@', $url, $matches, PREG_OFFSET_CAPTURE );
            //var_dump($matches);
            //var_dump(count($matches));
        ?>
    </body>
</html>
