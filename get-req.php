<?php   
    define('HEADERS', getallheaders());
    $head_method = $_SERVER["REQUEST_METHOD"] == "HEAD"; //dans ce cas là, on n'envoie que les headers
    try{
        require("class.php");

        $method = $_SERVER["REQUEST_METHOD"];

        switch($method){
            case "HEAD":
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

        if(array_key_exists('Accept', HEADERS) && (HEADERS['Accept'] == 'audio/mp3' || HEADERS['Accept'] == 'audio/*')){    //if exepct audio/mp3 redirect to get-music.php
            foreach(HEADERS as $header){header($header);}
            header("Location: get-music.php?file=" . urlencode($req["file"]), 301);
            header("Content-Type: text/html; charset=utf-8'");
            exit("You should be redirected, if the redirection don't work, go to <a href'get-music.php?file=" . urlencode($req["file"]) . "'>get-music</a>");
        }else if(array_key_exists('Accept', HEADERS) && str_starts_with(HEADERS['Accept'], 'audio')){
            throw new ServerError("Can only give mp3 files", 406);
        }

        if(array_key_exists('Accept', HEADERS) && HEADERS['Accept'] == 'text/html'){
            throw new ServerError("Can only give json version", 501);
        }else if(
            array_key_exists('Accept', HEADERS) &&
            !(
                (
                    str_starts_with(HEADERS['Accept'], 'application/') || str_starts_with(HEADERS['Accept'], '*/')
                )&&( 
                    str_ends_with(HEADERS['Accept'], '*') || str_ends_with(HEADERS['Accept'], 'json')//Accept: application/json OU Accept: application/* OU Accept: */*
                )  
            )
        ){    // don't accept JSONs
            throw new ServerError("Cannot provide other representation yet, html response is planned to be implemented.", 406);
        }
        header('Content-Type: application/json; charset=utf-8', true, 100);

        
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
        header('Content-Type: application/json; charset=utf-8', true, $err->getCode());
        
        if(!$head_method)
            echo $err->toJson();
            return $err->toJson();
        
    }catch(Throwable $err){
        header('Content-Type: application/json; charset=utf-8', true, 500);
        
        if(!$head_method)
            $e = new ServerError($err->getMessage(), 0);
            echo $e->toJson();
            return $e->toJson();
    }
?>