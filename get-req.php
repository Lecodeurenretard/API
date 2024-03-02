<?php   
    define('HEADERS', getallheaders());
    define('PAGE', 'req');
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
                throw new ServerError("Method $method is not allowed or unknown, please try again with one specified in the header Allow.", 405);
        }

        $parsed_Accept = parseAcceptHeader(); 

        checkAccept($req);
        checkParam($req);

        //send json
        header('Content-Type: application/json; charset=utf-8', true, 200);
        if(!$head_method)
            $music = Music::getFromFile($req["file"]);
            echo $music->jsonEncode();
            return $music->jsonEncode();
    }catch(ServerError $err){
        header('Content-Language: en');
        header('Content-Type: application/json; charset=utf-8', true, $err->getCode());
        
        if(!$head_method)
            echo $err->toJson();
            return $err->toJson();
    }catch(Throwable $err){
        header('Content-Language: en');
        header('Content-Type: application/json; charset=utf-8', true, 500);
        
        if(!$head_method)
            $e = new ServerError($err->getMessage(), 0);
            echo $e->toJson();
            return $e->toJson();
    }
?>