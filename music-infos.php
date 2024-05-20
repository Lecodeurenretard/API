<?php   
    define('HEADERS', getallheaders());
    define('PAGE', 'infos');
    header('Content-Language: en');

    try{
        require("class.php");

        $req = checkReq($_SERVER["REQUEST_METHOD"], $exit, $head_method);
        if($exit){ return ''; }
        unset($exit);   //no wasted memory !

        if(array_key_exists('Req-Body-Indent', HEADERS) && isset(HEADERS['Req-Body-Indent']))  { $indent = HEADERS['Req-Body-Indent']; }
        elseif(array_key_exists('indent', $req))                                               { $indent = $req['indent']; }
        else                                                                                   { $indent = 0; }
        
        if(!is_numeric(trim($indent))){throw new ServerError("The 'indent' is not a number", 400, __LINE__, "indent: `$indent`");}
        $indent = (int) trim($indent);    //convert to int
        if($indent < 0){$indent=0;}

        checkAccept(
            $req,
            (array_key_exists('redirect', $req) && isset($req['redirect']))? strToBool($req['redirect']) : true,    //check if the redirect param is true
        );  
        checkParamFile($req);

        $music = Music::getFromFile($req["file"]);

        header("Body-Indent: $indent");
        if(isMaxWeightAndAvailable('application/json') || isMaxWeightAndAvailable('application/*') || isMaxWeightAndAvailable('*/*')){
            header('Content-Type: application/json; charset=utf-8', true, 200);
            header('Body-Style: false'); 
            if(!$head_method){echo $music->jsonEncode($indent);}//send ressource
            return $music->jsonEncode($indent); 
        }
        

        header('Content-Type: application/xml; charset=utf-8', true, 200);
        $style = XML_Style(true, $req);
        if($style){
            header('Body-Style: true');
            if(!$head_method){ echo "<?xml-stylesheet href='xml-style.css' rel='stylesheet'?>" . PHP_EOL; } //output only if $style
        }else{
            header('Body-Style: false');
        }

        if(!$head_method){
            echo $music->XMLEncode($indent, $style);    //always output
        }
        return $music->XMLEncode($indent, $style);
    }catch(ServerError $err){
       return errSrv($err, $head_method);

    }catch(Throwable $err){
        return errThrow($err, $head_method);
    }
?>