<?php 
    define('HEADERS', getallheaders());
    define('PAGE', 'mp3');
    header('Content-Language: en');
    require("class.php");
    
    try{    
        $req = checkReq($_SERVER["REQUEST_METHOD"], $exit, $head_method, null, ['Accept', 'Accept-Error']);
        if($exit){ return ''; }
        unset($exit);   //no wasted memory !

        checkAccept(
            $req,
            (array_key_exists('redirect', $req) && isset($req['redirect']))? strToBool($req['redirect']) : true,    //check if the redirect param is true
        );
        checkParamFile($req);
        
        header("Content-Type: audio/mp3");
        header("Content-Location: " . Music::STORAGE_URL . $_GET['file']);

        if(!$head_method)
            readfile(Music::STORAGE_PATH . '/' . urldecode($_GET["file"]));
    }catch(ServerError $err){
       return errSrv($err, $head_method);

    }catch(Throwable $err){
        return errThrow($err, $head_method);
    }
?>