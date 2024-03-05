<?php
    define('HEADERS', getallheaders());
    define('PAGE', 'html');
    require("class.php");
    header('Content-Language: en-US');

    try{
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
                throw new ServerError("The method \"$method\" is not allowed or unknown, please try again with one specified in the header Allow.", 405);
        }

        checkAccept($req);
        checkParamFile($req);

        $music = Music::getFromFile($req['file']);
        $return = '';
        if(array_key_exists('title', $req) && readBoolString($req['title'] == null)){throw new ServerError("Parameter title incorrect, it must be equal to 'true' or 'false'", 400);}
        if(
            array_key_exists('title', $req) 
            && readBoolString($req['title'])        
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

        if(!$head_method){echo $err->toJson();}
        return $err->toJson();
        
    }catch(Throwable $err){
        header('Content-Type: application/json; charset=utf-8', true, 500);

        
        $e = ServerError::constructFromThrowable($err, 'Unexpected error');
        if(!$head_method){echo $e->toJson();}
        return $e->toJson();
    }
?>