<?php 
    define('HEADERS', getallheaders());
    define('PAGE', 'mp3');
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
            default:
                header("Allow: GET, POST, HEAD;", false, 405);
                throw new ServerError("The method \"$method\" is not allowed or unknown, please try again with one specified in the header Allow.", 405);
        }

        checkAccept($req);
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

        $e = ServerError::constructFromThrowable($err, 'Unexpected error');
        $ret = ServerError::getMaxAccept() == 'application/xml'? $e->toXML(false) : $e->toJSON();  //can return XML

        if(!$head_method){echo $ret;}
        return $ret;
    }
?>