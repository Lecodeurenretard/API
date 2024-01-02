<?php   
    define('HEADERS', getallheaders());
    
    try{
        if(
            in_array('Accept', HEADERS) &&
            !(
                str_starts_with(HEADERS['Accept'], 'application/')
                || (str_ends_with(HEADERS['Accept'], '*') || str_ends_with(HEADERS['Accept'], 'json'))  //Accept: application/json OU Accept: application/*
            )){    // don't accept JSONs
            throw new ServerError("Cannot provide other representation yet, html and mp3 responses are planned to be implemented.", 406);
        }
        header('Content-Type: application/json; charset=utf-8', true, 100);

        require_once("class.php");

        if($_SERVER["REQUEST_METHOD"] == "GET"){$req =& $_GET;}
        elseif($_SERVER["REQUEST_METHOD"] == "POST"){$req =& $_POST;}
        elseif($_SERVER["REQUEST_METHOD"] == "HEAD"){return 0;} //on n'envoie que les headers
        elseif($_SERVER["REQUEST_METHOD"] == "PUT"){
            header("Allow:  GET, POST, HEAD;", false, 403);
            throw new ServerError("Method PUT not allowed.", 403);
        }
        else{
            header("Allow: GET, POST, HEAD;", false, 405);
            throw new ServerError("Method not allowed, please try again with one specified in the header Allow.", 405);
        }

        
        if(!array_key_exists("file", $req)){
            throw new ServerError("the parameter \"file\" is missing.", 452);
            
        }elseif($req["file"] == '' || empty($req["file"])){
            throw new ServerError("No value is assigned to the parameter.", 452);
        }
        
        $internalpath = Music::STORAGE_PATH . $req["file"];   //chemin à l'interieur du server
        if(!file_exists($internalpath)){
            throw new ServerError("The specified element does not exist.", 404, 'Path: ' . $req["file"]);

        }elseif(str_contains($req["file"], "vendor/")){ //les librairies composer
            throw new ServerError("The access to this area is forbidden.", 403, "Atempt to access the 'vendor' directory.");
        }elseif(!str_ends_with($req["file"], '.mp3')){
            $req["file"] .= '.mp3';
        }

        $music = Music::getFromFile($req["file"]);
        http_response_code(200);
        echo $music->jsonEncode();
        return $music->jsonEncode();
    
    
    }catch(ServerError $err){
        http_response_code($err->getCode());
        echo $err->toJson();
        return $err->toJson();
    }catch(Throwable $err){
        $e = new ServerError($err->getMessage(), 0);
        http_response_code(500);
        echo $e->toJson();
        return $e->toJson();
    }
?>