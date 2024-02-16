<?php   
    define('HEADERS', getallheaders());
    $head_method = false;
    try{
        require("class.php");

        $method = $_SERVER["REQUEST_METHOD"];

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

        if(!array_key_exists('file', $req) || empty($req['file']) || $req['file'] == ''){
            throw new ServerError('Parameter "file" is missing', 400);
        }

        if(array_key_exists('Accept-Language', HEADERS) && !str_contains(HEADERS['Accept-Language'], '*') && !str_contains(HEADERS['Accept-Language'], 'en')){
            throw new ServerError("Cannot provide another language that english");
        }

        if(array_key_exists('Accept-Charset', HEADERS) && !str_contains(HEADERS['Accept-Charset'], '*') && !str_contains(HEADERS['Accept-Charset'], 'utf-8')){
            throw new ServerError("Cannot provide another charset that utf-8", 406);
        }

        if(verifyAcceptsType('audio', 'mp3') && isMaxWeightAndAvailble('audio/mp3') && !isMaxWeightAndAvailble('application/json')){    //if exepct audio/mp3 redirect to get-music.php, par défaut on envoie application/json
           redirect('get-music.php', ['file'], [$req['file']]);
        }/*in comment because don't work well
        else if(array_key_exists('Accept', HEADERS) && str_contains(HEADERS['Accept'], 'audio/')){
            throw new ServerError("Can only give mp3 audio files", 406);
        }*/

        if(verifyAcceptsType('text', 'html') && isMaxWeightAndAvailble('text/html') && !isMaxWeightAndAvailble('application/json')){
            redirect('get-html.php', ['file'], [$req['file']]);
        }
        else if(!verifyAcceptsType('application', 'json')){    // don't accept JSONs
            throw new ServerError("Cannot provide this representation, html and mp3 responses are the only other alternatives.", 406);
        }
        

        //send json
        if(!array_key_exists("file", $req)){
            throw new ServerError("the parameter \"file\" is missing.", 400);
            
        }elseif(empty($req["file"] || $req["file"] == '')){
            throw new ServerError("No value is assigned to the parameter \"file\".", 400);
        }
        
        $internalpath = Music::STORAGE_PATH . $req["file"];   //chemin à l'interieur du server
        $externalpath = Music::STORAGE_URL . $req["file"];
        if(!array_key_exists('extension', pathinfo($internalpath, PATHINFO_EXTENSION))){ //don't have extention
            $req["file"] .= '.mp3';
        }

        if(!file_exists($internalpath) || !is_file($internalpath)){
            throw new ServerError("The specified element does not exist.", 404, "Path: $externalpath");

        }elseif(str_contains($req["file"], "vendor/")){ //les librairies composer
            throw new ServerError("The access to this area is forbidden.", 403, "Attempt to access the 'vendor' directory.");
        
        }else if(!str_contains(realpath($internalpath), Music::STORAGE_PATH)){  //on vérifie que le script ne cherche pas en dehors du bon dossier
            throw new ServerError("The specified path leads outside the 'api' folder", 403, (realpath($externalpath) === false)? 'realpath() failed' : "attempt to access " . realpath($externalpath));
        }

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