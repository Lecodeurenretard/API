<?php
    define('HEADERS', getallheaders());
    require("class.php");
    header('Content-Language: en-US');

    try{
        $method = $_SERVER["REQUEST_METHOD"];
        $head_method = false;

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

        $internalpath = Music::STORAGE_PATH . $req["file"];   //chemin à l'interieur du server
        $externalpath = Music::STORAGE_URL . $req["file"];
        if(!array_key_exists('extension', pathinfo($internalpath, PATHINFO_EXTENSION))){ //don't have extention
            $req["file"] .= '.mp3';
        }

        if(!array_key_exists('Accept', HEADERS) && HEADERS['Accept'] != 'text/html' && HEADERS['Accept'] != 'text/*' && HEADERS['Accept'] != '*/*'){
            header('alt-request: http://api.musiques.nils.test.sc2mnrf0802.universe.wf/get-req.php');
            throw new ServerError("Can only give html representation of the file, for more information see the \"alt-request\" header.", 406);
        }
        if(array_key_exists('Accept-Charset', HEADERS) && !str_contains(HEADERS['Accept-Charset'], '*') && !str_contains(HEADERS['Accept-Charset'], 'utf-8')){
            throw new ServerError("Cannot provide another charset that utf-8", 406);
        }

        //verify path
        if(!file_exists($internalpath) || !is_file($internalpath)){
            throw new ServerError("The specified element does not exist.", 404, "Path: $externalpath");

        }elseif(str_contains($req["file"], "vendor/")){ //les librairies composer
            throw new ServerError("The access to this area is forbidden.", 403, "Attempt to access the 'vendor' directory.");
        
        }else if(!str_contains(realpath($internalpath), Music::STORAGE_PATH)){  //on vérifie que le script ne cherche pas en dehors du bon dossier
            throw new ServerError("The specified path leads outside the 'api' folder", 403, (realpath($externalpath) === false)? 'realpath() failed' : "attempt to access " . realpath($externalpath));
        }

        $music = Music::getFromFile($req['file']);

        header("Content-Type: text/html; charset=utf-8");
        if(!$head_method)
            echo "<audio src='". Music::STORAGE_URL . $req['file'] ."' type='audio/mp3' controls autoplay></audio>";
    }catch(ServerError $err){
        header('Content-Type: application/json; charset=utf-8', true, $err->getCode());

        echo $err->toJson();
        return $err->toJson();
        
    }catch(Throwable $err){
        header('Content-Type: application/json; charset=utf-8', true, 500);

        $e = new ServerError($err->getMessage(), 0);
        echo $e->toJson();
        return $e->toJson();
    }
?>