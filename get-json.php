<?php   
    define('HEADERS', getallheaders());
    define('PAGE', 'json');
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
            default:
                header("Allow: GET, POST, HEAD;", false, 405);
                throw new ServerError("Method $method is not allowed or unknown, please try again with one specified in the header Allow.", 422);
        }

        $parsed_Accept = parseAcceptHeader();
        $indent = array_key_exists('indent', $req)? $req['indent'] : 0; 
        if(!is_numeric(rtrim($indent))){throw new ServerError("The 'indent' is not a number", 400, "indent: $indent");}

        $indent = (int) rtrim($indent);    //convert to int

        checkAccept($req);  //redirects so should be before
        checkParamFile($req);

        $music = Music::getFromFile($req["file"]);

        header("Body-Indent: $indent");
        if(isMaxWeightAndAvailable('application/xml')){
            header('Content-Type: application/xml; charset=utf-8', true, 200);
            if(!$head_method){echo $music->XMLEncode($indent);}
            return $music->XMLEncode($indent);
        }

        
        header('Content-Type: application/json; charset=utf-8', true, 200);
        if(!$head_method)          
            echo $music->jsonEncode($indent);  //send ressource
        return $music->jsonEncode($indent);        
    }catch(ServerError $err){
        header('Content-Language: en');
        header("Body-Indent: 0");
        header('Content-Type: application/json; charset=utf-8', true, $err->getCode());
        
        if(!$head_method){echo $err->toJson();}
        return $err->toJson();
        
    }catch(Throwable $err){
        header('Content-Language: en');
        header("Body-Indent: 0");
        header('Content-Type: application/json; charset=utf-8', true, 500);
        
        $e = ServerError::constructFromThrowable($err, 'Unexpected error');
        if(!$head_method){echo $e->toJson();}
        return $e->toJson();
    }
?>