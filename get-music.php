<?php 
    define('HEADERS', getallheaders());
    require("class.php");
    
    try{
        if($_SERVER["REQUEST_METHOD"] != "GET"){
            header("Allow: GET");
            throw new ServerError("Handle only GET method.", 405);
        }
        header("Content-Type: audio/mp3");
        readfile(Music::STORAGE_PATH . '/' . urldecode($_GET["file"]));
        echo Music::STORAGE_PATH . '/' . urldecode($_GET["file"]);
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