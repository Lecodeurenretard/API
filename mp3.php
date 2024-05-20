<?php 
    define('HEADERS', getallheaders());
    define('PAGE', 'mp3');
    header('Content-Language: en');
     try{
         require("class.php");
    
        $head_method = false;
        $method = $_SERVER['REQUEST_METHOD'];
    
   
        switch($method){
            
            case "HEAD":
                $head_method = true;
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
                header('Access-Control-Allow-Headers: Accept');
                http_response_code(200); 
                return '';

            default:
                header("Allow: GET, POST, HEAD, OPTIONS;", false, 405);
                throw new ServerError("The method \"$method\" is not allowed or unknown, please try again with one specified in the header Allow.", 405, __LINE__);
        }

        checkAccept(
            $req,
            (array_key_exists('redirect', $req) && isset($req['redirect']))? strToBool($req['redirect']) : true,    //check if the redirect param is true
        );
        checkParamFile($req);
        
        header("Content-Type: audio/mp3");
        header("Content-Location: " . Music::STORAGE_URL . $_GET['file']);

        if(!$head_method)
            readfile(Music::STORAGE_PATH . '/' . urldecode($_GET["file"]));
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