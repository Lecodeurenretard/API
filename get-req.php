<?php   
    define('HEADERS', getallheaders());
    $head_method = $_SERVER["REQUEST_METHOD"] == "HEAD"; //dans ce cas là, on n'envoie que les headers
    try{

        if(in_array('Accept', HEADERS) && (HEADERS['Accept'] == 'text/html' ||HEADERS['Accept'] == 'appilcation/mp3')){
            throw new ServerError("Can only give json version", 501);
        }else if(
            in_array('Accept', HEADERS) &&
            !(
                (
                    str_starts_with(HEADERS['Accept'], 'application/') || str_starts_with(HEADERS['Accept'], '*/')
                )&&( 
                    str_ends_with(HEADERS['Accept'], '*') || str_ends_with(HEADERS['Accept'], 'json')//Accept: application/json OU Accept: application/* OU Accept: */*
                )  
            )
        ){    // don't accept JSONs
            throw new ServerError("Cannot provide other representation yet, html and mp3 responses are planned to be implemented.", 406);
        }
        header('Content-Type: application/json; charset=utf-8', true, 100);

        require_once("class.php");

        $method = $_SERVER["REQUEST_METHOD"];
        if($method == "GET"){$req =& $_GET;}            //on met la référence au cas où on change les supervariable entre temps
        elseif($method == "POST"){$req =& $_POST;}
        elseif($method == "PUT"){
            header("Allow:  GET, POST, HEAD;", false, 403);
            throw new ServerError("Method PUT not allowed.", 403);
        }
        else{
            header("Allow: GET, POST, HEAD;", false, 405);
            throw new ServerError("Method $method is not allowed or unknown, please try again with one specified in the header Allow.", 405);
        }

        
        if(!array_key_exists("file", $req)){
            throw new ServerError("the parameter \"file\" is missing.", 400);
            
        }elseif(empty($req["file"] || $req["file"] == '')){
            throw new ServerError("No value is assigned to the parameter.", 400);
        }
        
        $internalpath = Music::STORAGE_PATH . $req["file"];   //chemin à l'interieur du server
        if(!file_exists($internalpath)){
            throw new ServerError("The specified element does not exist.", 404, 'Path: ' . $req["file"]);

        }elseif(str_contains($req["file"], "vendor/")){ //les librairies composer
            throw new ServerError("The access to this area is forbidden.", 403, "Attempt to access the 'vendor' directory.");

        }elseif(!str_ends_with($req["file"], '.mp3')){
            $req["file"] .= '.mp3';
        }
        http_response_code(200);
        
        if(!$head_method)
            $music = Music::getFromFile($req["file"]);
            echo $music->jsonEncode();
            return $music->jsonEncode();
    }catch(ServerError $err){
        
        http_response_code($err->getCode());
        if(!$head_method)
            echo $err->toJson();
            return $err->toJson();
        
    }catch(Throwable $err){
        http_response_code(500);
        
        if(!$head_method)
            $e = new ServerError($err->getMessage(), 0);
            echo $e->toJson();
            return $e->toJson();
    }
?>