<?php 
    define('HEADERS', getallheaders());
    require("class.php");
    
    try{
        if($_SERVER["REQUEST_METHOD"] != "GET"){
            header("Allow: GET");
            throw new ServerError("Handle only GET method.", 405);
        }

        if(!array_key_exists('file', $_GET) || empty($_GET["file"]) || $_GET["file"] == ''){
            throw new ServerError('The parameter "file" is not set', 400, 'The request: ' .arrayToString($_GET, ', ', "'", true));
        }

        $internalpath = Music::STORAGE_PATH . $_GET["file"];   //chemin à l'interieur du server
        $externalpath = Music::STORAGE_URL . $_GET["file"];
        if(!array_key_exists('extension', pathinfo($internalpath))){ //don't have extention
            $_GET["file"] .= '.mp3';
        }

        if(!file_exists($internalpath) || !is_file($internalpath)){
            throw new ServerError("The specified element does not exist.", 404, "Path: $externalpath");

        }elseif(str_contains($_GET["file"], "vendor/")){ //les librairies composer
            throw new ServerError("The access to this area is forbidden.", 403, "Attempt to access the 'vendor' directory.");
        
        }else if(!str_contains(realpath($internalpath), Music::STORAGE_PATH)){  //on vérifie que le script ne cherche pas en dehors du bon dossier
            throw new ServerError("The specified path leads outside the 'api' folder", 403, (realpath($externalpath) === false)? 'realpath() failed' : "attempt to access " . realpath($externalpath));
        }
        header("Content-Type: audio/mp3");
        header("Content-Location: " . Music::STORAGE_URL . $_GET['file']);

        readfile(Music::STORAGE_PATH . '/' . urldecode($_GET["file"]));
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