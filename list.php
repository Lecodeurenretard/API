<?php
    define('PAGE', 'list');
    define('HEADERS', getallheaders());
    require("class.php");

    $method = $_SERVER["REQUEST_METHOD"];
    $head_method = false;
    try{
        switch($method){
            case "HEAD":
                $head_method = true; //dans ce cas là, on n'envoie que les headers
            case "GET":
                $req =& $_GET;   //on met la référence au cas où on change les supervariable entre temps
                break;
                
            case "POST":
                $req =& $_POST;
                break;
                
            default:
                header("Allow: GET, POST, HEAD;", false, 405);
                throw new ServerError("Method $method is not allowed or unknown, please try again with one specified in the header Allow.", 422, __LINE__);
        }

        checkAccept($req, false);

        if(isMaxWeightAndAvailable('application/json') || isMaxWeightAndAvailable('application/*') || isMaxWeightAndAvailable('*/*')){
            header('Content-Type: application/json; charset=utf-8', true, 200);
            
            $ret = '[' . PHP_EOL; 
                foreach(glob(MUSIC::STORAGE_PATH . '*.mp3') as $file){
                    $request = curl_init("http://api.musiques.nils.test.sc2mnrf0802.universe.wf/music-infos?indent=1&file=" . urlencode(basename($file, '.mp3')));
                    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($request, CURLOPT_HTTPHEADER, ['Accept: application/json']);
                    $res = curl_exec($request);
                    $ret .= $res . ',' . PHP_EOL;    //the result of the request

                    if(curl_getinfo($request, CURLINFO_HTTP_CODE) >= 400){throw ServerError::fromJson($res);}//throws the ServerError emitted
                }
                $ret = rtrim($ret, ','.PHP_EOL);//remove trailing comma
                $ret .= PHP_EOL. ']';
            if(!$head_method){echo $ret;}
            return $ret;
        }elseif(verifyAcceptsType('application', 'xml')){
            header('Content-Type: application/xml; charset=utf-8', true, 200);

            $ret = (XML_Style(true, $req)? "\t<?xml-stylesheet href='xml-style.css' rel='stylesheet'?>" . PHP_EOL : '')
            . '<musics>' . PHP_EOL ; 
                foreach(glob(MUSIC::STORAGE_PATH . '/*.mp3') as $file){
                    $request = curl_init("http://api.musiques.nils.test.sc2mnrf0802.universe.wf/music-infos?indent=1&file=" . urlencode(basename($file, '.mp3')));
                    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($request, CURLOPT_HTTPHEADER, ['Accept: application/xml', 'Accept-Error: application/json']);

                    $res = curl_exec($request);
                    $ret .= $res . PHP_EOL;    //the result of the request

                    if(curl_getinfo($request, CURLINFO_HTTP_CODE) >= 400){throw ServerError::fromJson($res);}//throws the ServerError emitted
                }
                $ret .= '</musics>';
            
            if(!$head_method){echo $ret;}
            return $ret;
        }
        
        throw new ServerError('Can only give representation in JSON or XML', 406, __LINE__, 'Accept header: ' . HEADERS['Accept']);
    }catch(ServerError $err){
        header('Content-Language: en');
        header('Content-Type: application/json; charset=utf-8', true, $err->getCode());
        
        if(!$head_method){echo $err->toJson();}
        return $err->toJson();
        
    }catch(Throwable $err){
        header('Content-Language: en');
        header('Content-Type: application/json; charset=utf-8', true, 500);

        try {
            $e = ServerError::constructFromThrowable($err, 'Caught unexpected error');
            $ret = ServerError::getMaxAccept() == 'application/xml'? $e->toXML(false) : $e->toJSON();  //can return XML
        }catch(Throwable $th){
            //unable to access ServerError
            $ret = 
                '{'                                             . PHP_EOL .
                "\t". '"code": 500'                             . PHP_EOL .
                "\t". '"name": "Unknown error"'                 . PHP_EOL .
                "\t". '"message": ' . "{$th->getMessage()}"     . PHP_EOL .
                "\t". '"other-info": ""'                        . PHP_EOL .
                '}';
        }
        
        if(empty($head_method) || !$head_method){echo $ret;}
        
        return $ret;
    }

    