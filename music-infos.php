<?php   
    define('HEADERS', getallheaders());
    define('PAGE', 'infos');
    header('Content-Language: en');

    try{
        require("class.php");

        $method = $_SERVER["REQUEST_METHOD"];
        $head_method = false;
        switch($method){
            case "HEAD":
                $head_method = true; //dans ce cas là, on n'envoie que les headers

            case "GET":
                $req =& $_GET;   //on met la référence au cas où on change les supervariable entre temps
                break;
            
            case "POST":
                $req =& $_POST;
                break;
            /*
            elseif($method == "PUT"){
                header("Allow:  GET, POST, HEAD;", false, 403);
                throw new ServerError("Method PUT not allowed.", 403);
            }
            */

            case 'OPTIONS':
                header('Allow: GET, POST, HEAD, OPTIONS');
                header('Access-Control-Allow-Headers: Accept, Accept-Error, Req-Body-Indent, Req-Body-Style');
                http_response_code(200); 
                return '';
    
            default:
                header("Allow: GET, POST, HEAD, OPTIONS", true, 405);
                throw new ServerError("Method $method is not allowed or unknown, please try again with one specified in the header Allow.", 422, __LINE__);
        }

        if(array_key_exists('Req-Body-Indent', HEADERS) && isset(HEADERS['Req-Body-Indent']))  { $indent = HEADERS['Req-Body-Indent']; }
        elseif(array_key_exists('indent', $req))                                            { $indent = $req['indent']; }
        else                                                                                { $indent = 0; }
        
        if(!is_numeric(trim($indent))){throw new ServerError("The 'indent' is not a number", 400, __LINE__, "indent: `$indent`");}
        $indent = (int) trim($indent);    //convert to int
        if($indent < 0){$indent=0;}

        checkAccept(
            $req,
            (array_key_exists('redirect', $req) && isset($req['redirect']))? strToBool($req['redirect']) : true,    //check if the redirect param is true
        );  
        checkParamFile($req);

        $music = Music::getFromFile($req["file"]);

        header("Body-Indent: $indent");
        if(isMaxWeightAndAvailable('application/json') || isMaxWeightAndAvailable('application/*') || isMaxWeightAndAvailable('*/*')){
            header('Content-Type: application/json; charset=utf-8', true, 200);
            header('Body-Style: false'); 
            if(!$head_method){echo $music->jsonEncode($indent);}//send ressource
            return $music->jsonEncode($indent); 
        }
        

        header('Content-Type: application/xml; charset=utf-8', true, 200);
        $style = XML_Style(true, $req);
        if($style){
            header('Body-Style: true');
            if(!$head_method){ echo "<?xml-stylesheet href='xml-style.css' rel='stylesheet'?>" . PHP_EOL; } //output only if $style
        }else{
            header('Body-Style: false');
        }

        if(!$head_method){
            echo $music->XMLEncode($indent, $style);    //always output
        }
        return $music->XMLEncode($indent, $style);
    }catch(ServerError $err){
        header('Content-Type: application/json; charset=utf-8', true, $err->getCode());
        $err->sendErrorHeaders();

        $ret = ServerError::getMaxAccept() == 'application/xml'? $err->toXML(false) : $err->toJSON();  //can return XML

        if(!$head_method){echo $ret;}
        return $ret;

    }catch(Throwable $err){
        header('Content-Type: application/json; charset=utf-8', true, 500);

        try {
            $e = ServerError::constructFromThrowable($err, 'Caught unexpected error');
            $ret = ServerError::getMaxAccept() == 'application/xml'? $e->toXML(false) : $e->toJSON();  //can return XML
        }catch(Throwable $th){
            //unable to access ServerError
            $ret = 
                '{'                                             . PHP_EOL .
                "\t". '"code": 500'                             . PHP_EOL .
                "\t". '"name": "Unknown error"'                 . PHP_EOL .
                "\t". '"message": ' . "{$th->getMessage()}"     . PHP_EOL .
                "\t". '"other-info": ""'                        . PHP_EOL .
                '}';
        }
        
        if(empty($head_method) || !$head_method){echo $ret;}
        
        return $ret;
    }
?>