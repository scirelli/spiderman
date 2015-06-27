<html>
    <head>
        <style type="text/css">
            iframe{
                width:100%;
                height:70%;
                background-color:black;
                color:white;
                border-color:#6C6C6C;
                border:2px solid #6C6C6C;
            } 
            fieldset{
                background-color:black;
                border-color:#6C6C6C;
                border:2px solid #6C6C6C;
            } 
            #ffParsers{
                vertical-align:text-top;
            }
            #leftContainer{
                display:table-cell;
                vertical-align:top;
            }
            #rightContainer{
                display:table-cell;
                vertical-align:top;
                padding-top:7px;
                display:none;
                visibility:hidden;
            }
            .row{
                display:table-row;
            }

            body{
                width:1024px;
                margin-left:auto;
                margin-right:auto;
                background:url(../assets/images/web3.jpg) repeat;
                color:white;
                background-color:black;
            }
        </style>

        <!-- jqQuery -->
        <script type="text/javascript" src="../../js/jquery-1.4.4.min.js"></script>
        <!-- jqForm -->
        <script type="text/javascript" src="../../js/jquery.form.js"></script>
        <!-- jqQuery Validate -->
        <script type="text/javascript" src="../../js/jquery-validation-1.9.0/jquery.validate.min.js"></script>
        <script type="text/javascript" src="../../js/jquery-validation-1.9.0/additional-methods.min.js"></script>

        <script type="text/javascript"> 
            "use strict";
            if( spider === undefined ) var spider = new Object();

            ;(function( spider, $ ){
                if( Object.defineProperties ){//Standard way new browsers 
                    //Getters and setters for the control panel of the grapher and the form
                    Object.defineProperties(spider, { 
                        "form":{ //The ID to the chart container
                            value:'fForm',//The value associated with the property. (data descriptors only). Defaults to undefined. 
                            //get : function(){ return value; },  
                            //set : function(newValue){ bValue = newValue; },  
                            writable:false,//True if and only if the value associated with the property may be changed. (data descriptors only). Defaults to false. 
                            enumerable:false,//True if and only if this property shows up during enumeration of the properties on the corresponding object. Defaults to false. 
                            configurable:false//True if and only if the type of this property descriptor may be changed and if the property may be deleted from the corresponding object. Defaults to false.
                        },
                        "ffRoot"   :{ value:'ffRoot' },
                        "ffLimits"  :{ value:'ffLimits' },
                        "ffAvoid"  :{ value:'ffAvoid' },
                        "ffParsers":{ value:'ffParsers' },
                        "frame"    :{ value:'frmOutput' }
                    });
                } else {//for IE
                    //---- Getters ----
                    spider.form     = 'fForm';
                    spider.ffRoot   = 'ffRoot';
                    spider.ffLimits = 'ffLimits';
                    spider.ffAvoid  = 'ffAvoid';
                    spider.ffParsers= 'ffParsers';
                    spider.frame    = 'frmOutput';
                }

                spider.submit = function(event){
                    var frame = document.getElementById(spider.frame),
                        form  = document.getElementById(spider.form ),
                        limit = document.getElementById(spider.ffLimits),
                        avoid = document.getElementById(spider.ffAvoid),
                        root  = document.getElementById(spider.ffRoot),
                        parser= document.getElementById(spider.ffParsers),
                        src = '';

                        src = form.action + '?ffRoot=' + escape(root.value) + '&ffLimits=' + escape(limit.value) + '&ffAvoid=' + escape(avoid.value) + '&ffParsers=' + escape(parser.value);
                    $('#' + spider.frame).attr('src',src);

                    event.preventDefault(); 
                    return false;
                    //frame.contentDocument.location = frame.src;
                    //frame.contentDocument.location.reload(true);
                    //window.frames[spider.frame].location.reload(true);
                }
            })( spider, jQuery);

            $(document).ready( function(){
                $('#' + spider.form).submit( spider.submit );
                $('#' + spider.ffRoot).focus();
            });
        </script>
    </head>

    <body>
        <div>
            <form id="fForm" name="fForm" method="POST" action="frame.php">
                <div class="row">
                    <div id="leftContainer">
                        <fieldset>
                            <legend>Params</legend>
                            <div>
                                <label for="ffRoot">Root URL: </label>
                                <input type="text" id="ffRoot" name="ffRoot" value="http://www.google.com"/>
                            </div>
                            <div>
                                <label for="ffLimits">Spider Domain Limits: </label>
                                <input type="text" id="ffLimits" name="ffLimit" value=""/>
                            </div>
                            <div>
                                <label for="ffAvoid">Avoid Domains: </label>
                                <input type="text" id="ffAvoid" name="ffAvoid" value=""/>
                            </div>

                            <div>
                                <label for="ffParsers">Attach Parsers: </label>
                                <select id="ffParsers" name="ffParsers" multiple="multiple">
                                    <?PHP
                                        require_once('../assets/lib/IDocParser.php');
                                        require_once('../assets/lib/ParserFactory.php');
                                        $aFileNames = ParserFactory::getParserList();
                                        foreach( $aFileNames as $parser ){
                                            echo '<option value="' . $parser . '">' . $parser . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <input type="submit" id="ffCrawl" name="ffCrawl" value="Crawl"/>
                            </div>
                        </fieldset>
                    </div>

                    <div id="rightContainer">
                        <textarea id="output" name="output" cols="50" rows="10">
                        </textarea>
                    </div>
                </div>
            </form>
        </div>

        <iframe id="frmOutput" name="frmOutput" src="frame.php"></iframe>
    </body>
</html>
