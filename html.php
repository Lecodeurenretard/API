<?php
    
    header('Content-Language: en-US');
    try{
        define('HEADERS', getallheaders());
        define('PAGE', 'html');
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
            default:
                header("Allow: GET, POST, HEAD;", false, 405);
                throw new ServerError("The method \"$method\" is not allowed or unknown, please try again with one specified in the header Allow.", 405, __LINE__);
        }

        checkAccept(
            $req,
            (array_key_exists('redirect', $req) && isset($req['redirect']))? strToBool($req['redirect']) : true,    //check if the redirect param is true
        );
        checkParamFile($req);

        $music = Music::getFromFile($req['file']);
        $return = '';
        if(array_key_exists('title', $req) && strToBool($req['title'] == null)){throw new ServerError("Parameter title incorrect, it must be equal to 'true' or 'false'", 400, __LINE__);}
        if(
            array_key_exists('title', $req) 
            && strToBool($req['title'])        
        ){
            $return = "<div class='music-head'>" . Music::getFromFile($req['file'])->toHTML() . '</div>';
        }
        
        $return .= "<audio src='". Music::STORAGE_URL . $req['file'] ."' type='audio/mp3' controls autoplay></audio>";
        
        header("Content-Type: text/html; charset=utf-8"); 
        if(!$head_method)
            echo $return;
        return $return;
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