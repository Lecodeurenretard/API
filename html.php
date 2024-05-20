<?php
    
    header('Content-Language: en');
    try{
        define('HEADERS', getallheaders());
        define('PAGE', 'html');
        require("class.php");
        
        
        $req = checkReq($_SERVER["REQUEST_METHOD"], $exit, $head_method, null, ['Accept', 'Accept-Error']);
        if($exit){ return ''; }
        unset($exit);   //no wasted memory !

        checkAccept(
            $req,
            (array_key_exists('redirect', $req) && isset($req['redirect']))? strToBool($req['redirect']) : true,    //check if the redirect param is true
        );
        checkParamFile($req);

        $music = Music::getFromFile($req['file']);
        $return = '';
        if(array_key_exists('title', $req) && strToBool($req['title'] == null)){throw new ServerError("Parameter title incorrect, it must be equal to 'true' or 'false'", 400, __LINE__);}
        if(
            array_key_exists('title', $req) 
            && strToBool($req['title'])        
        ){
            $return = "<div class='music-head'>" . Music::getFromFile($req['file'])->toHTML() . '</div>';
        }
        
        $return .= "<audio src='". Music::STORAGE_URL . $req['file'] ."' type='audio/mp3' controls autoplay></audio>";
        
        header("Content-Type: text/html; charset=utf-8"); 
        if(!$head_method)
            echo $return;
        return $return;
    }catch(ServerError $err){
       return errSrv($err, $head_method);

    }catch(Throwable $err){
        return errThrow($err, $head_method);
    }
?>