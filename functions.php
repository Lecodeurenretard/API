<?php
include_once("class.php");
if(!defined('HEADERS')){define('HEADERS', getallheaders());}
if(!defined('PAGE')){define('PAGE', basename(__FILE__, '.php'));}
if(!defined('PAGE_TAB')){
    define('PAGE_TAB', [
        'infos' => 'music-infos',
        'html' => 'html',
        'mp3' => 'mp3',
        'index' => 'index',
        'list' => 'list'
    ]);
}

/**
 * Transforme $arr en string de la forme '$autour$arr[0]$autour$sep$autour$arr[1]$autour...'; par exemple echo arrayToString(["moi", "toi"], ' + ', '"'); //'"moi" + "toi"'
 * @param array $arr l'array à être transformé
 * @param string $sep =', ' | le séparateur
 * @param string|array $autour ='' | ce qui entoure chaque élément; si array, v.ex: arrayToString(["jojo", "lui"], ' ', ['<', '>]) = "<jojo> <lui>"; si array et le nb d'élément est 1, fait comme si ce n'était pas un tableau
 * @param bool $keys =false | Si l'on doit afficher les clefs
 * @param int $baseIndent =0| L'indentation de base, n'est valable que si $sep contient un retour à la ligne (PHP_EOL)
 * @return string tous les éléments de $arr avec entre eux $sep
 */
function arrayToString(array $arr, string $sep=', ', string|array $autour='', bool $keys = false, int $baseIndent = 0) : string{
    if($baseIndent < 0){$baseIndent=0;}
    $indent = str_contains($sep, PHP_EOL)? str_repeat("\t", $baseIndent) : '';
    
    if(gettype($autour)=='array' && count($autour)>=2){
        $ret = '';
        foreach($arr as $i => $elem){
            $ret .= $indent . $autour[0] . ($keys? $i . $autour[1] . ': ' . $autour[0] : '') . $elem . $autour[1] . $sep;
        }
        return rtrim($ret, $sep);   //enlève le dernier séparateur
    }elseif(gettype($autour)=='array' && count($autour)<2){$autour = $autour[0];}
    
    $ret = '';
    foreach($arr as $i => $elem){
        $ret .= $indent . $autour . ($keys? $i . $autour . ': ' . $autour : '') . $elem . $autour . $sep;
    }
    return rtrim($ret, $sep);   //enlève le dernier séparateur
}

/**
 * Si $expr est true, $var = $setTrue; else $var = $setFalse.
 * @param mixed $var La variable à modifier.
 * @param bool $expr L'expression à vérifier, contrôle par quelle valeur $var sera set.
 * @param mixed $setTrue La valeur par laquelle set $var si $expr == true si null ne change pas $var.
 * @param mixed $setFalse =null | La valeur par laquelle set $var si $expr == false. si null ne change pas $var.
 * @param bool $strict =false | Si true et ($setFalse === null || $setFalse ===null) alors $var = null.
 */
function ifTrueSet(mixed& $var, bool $expr, mixed $setTrue=null, mixed $setFalse=null, bool $strict=false) : void{
    if(($strict || $setTrue !== null) && $expr){$var = $setTrue;}
    elseif($strict || $setFalse !== null){$var = $setFalse;}
    //else nothing
}

/**
 * Appelle la fonction $fun avec pour arguments $args et $otherArgs
 * @param string $fun La fonction à appeler.
 * @param iterable $arg La liste des arguments, incompatible avec le passage par référence. (ex: $ret = chainFunct('count', [[1,2,3], [4,5,6,7], ['a', 'b', 'c']]); <=> $ret[0] = count([1,2,3]); $ret[1] = count([4,5,6,7]);$ret[2] = count(['a','b','c']))
 * @param bool $strict =true | Si true alors on passera dans tous les cas $otherArgs en paramètre.
 * @param mixed ...$otherArgs =[] | Les autres paramètres à  passer à fun, seront tous passés à chaque appel; si tableau vide alors on n'appelle que fun($args[])
 * @return array Retourne dans un tableau les différents résultats (ce que renvoie $fun) des appels de $fun.
 */
function chainFunct(string $fun, iterable $arg, bool $strict=true, ...$otherArgs) : array{
    $ret = [];
    foreach($arg as $param){
        $ret[] = (count($otherArgs) != 0 || $strict)? $fun($param, ...$otherArgs) : $fun($param);
    }
    return $ret;
}

/**
 * Appelle la fonction $fun avec pour arguments $args et $otherArgs
 * @param string $fun La fonction à appeler.
 * @param iterable $arg La liste des arguments, accepte le passage par référence. (ex: $ret = chainFunct('count', [[1,2,3], [4,5,6,7], ['a', 'b', 'c']]); <=> $ret[0] = count([1,2,3]); $ret[1] = count([4,5,6,7]);$ret[2] = count(['a','b','c']))
 * @param bool $strict =true | Si true alors on passera dans tous les cas $otherArgs en paramètre.
 * @param mixed ...$otherArgs =[] | Les autres paramètres à  passer à fun, sont passés par référence, seront tous passés à chaque appel; si tableau vide alors on n'appelle que fun($args[])
 * @return array Retourne dans un tableau les différents résultats (ce que renvoie $fun) des appels de $fun.
 */
function chainFunctRef(string $fun, iterable& $arg, bool $strict=true, mixed& ...$otherArgs) : array{
    $ret = [];
    foreach($arg as $param){
        $ret[] = (count($otherArgs) != 0 || $strict)? $fun($param, ...$otherArgs) : $fun($param);
    }
    return $ret;
}

/**
 * Appelle la fonction $fun avec pour arguments $args et $otherArgs
 * @param string $fun La fonction à appeler.
 * @param iterable $arg La liste des arguments, accepte le passage par référence. (ex: $ret = chainFunct('count', [[1,2,3], [4,5,6,7], ['a', 'b', 'c']]); <=> $ret[0] = count([1,2,3]); $ret[1] = count([4,5,6,7]);$ret[2] = count(['a','b','c']))
 * @param bool $strict =true | Si true alors on passera dans tous les cas $otherArgs en paramètre.
 * @param mixed ...$otherArgs =[] | Les autres paramètres à  passer à fun, seront tous passés à chaque appel; si tableau vide alors on n'appelle que fun($args[])
 * @return array Retourne dans un tableau les différents résultats (ce que renvoie $fun) des appels de $fun.
 */
function chainFunctDemiRef(string $fun, iterable& $arg, bool $strict=true, ...$otherArgs) : array{
    $ret = [];
    foreach($arg as $param){
        $ret[] = (count($otherArgs) != 0 || $strict)? $fun($param, ...$otherArgs) : $fun($param);
    }
    return $ret;
}

/**
 * Retourne soit $arr[$key] si existe, sinon $default
 * @param array $arr Le tableau à vérifier.
 * @param int|string $key La clef à vérifier.
 * @param $default =null La valeur à renvoyer si $arr[$key] n'existe pas.
 */
function ret_array_key_if_defined(array $arr, string|int $key, $default=null) : mixed{
    return (array_key_exists($key, $arr))? $arr[$key] : $default;
}


/**
 * redirects the user with an http response 3XX to the url: "$url?$paramName[0]=$paramValue[0]& ..."
 * @param string $url l'url de redirection
 * @param ?array $paramName =null | Le nom des paramètres à passer à l'url, si n'est pas de la même longueur que $paramValue, sera ingnoré.
 * @param ?array $paramValue =null | La valeur des paramètres à passer à l'url, si n'est pas de la même longueur que $paramName, sera ingnoré.
 * @param bool $replaceHeaders=true | Le deuxième argument de header()
 * @param int $code =308 | Le code de la réponse http à envoyer.
 * @param string ...$headers | Les headers à envoyer à la place de ceux reçus, si pas set, la fonction enverra les headers des la page
 * @throws ServerError Si !( 299 < $code < 399) lève une ServerError
 */ 
function redirect(string $url, ?array $paramName = null, ?array $paramValue = null, bool $replaceHeaders = true, int $code = 308, ?string ...$headers) : void{
    $noParam = explode('?', $url)[0];
    if(
        (array_key_exists(PAGE, PAGE_TAB) && $noParam == PAGE_TAB[PAGE]) 
        || (array_key_exists(PAGE, PAGE_TAB) && $noParam == PAGE_TAB[PAGE] . '.php')
        || $noParam == '\this'){//if redirects on this page
        return;
    }
    if($url == 'index.php'){$url = 'music-infos';}//index.php will redirect on music-infos.php regardless of the request (and will lose the headers)
    
    if($code > 399 || $code < 299){
        throw new ServerError("Cannot redirect with the code: '$code'", 500, 'function redirect()');
    }

    $head = isset($headers)? $headers : HEADERS;
    $params = (isset($paramName) && count($paramName) == count($paramValue) && count($paramName) != 0)? 
        '?' .  paramURL($paramName, array_values($paramValue)) 
        : '';

    foreach($head as $header){header($header, $replaceHeaders);}
    
    header("Location: $url$params", true, $code);
    header('Content-Type: text/html; charset=utf-8');
    exit("You should be redirected, if the redirection don't work, go to <a href'$url$params'>". basename($url) .'</a>');
}
/**
 * return the arguments as if they were in an URL
 */
function paramURL(array $paramName, array $paramValue) : ?string{
    if(count($paramName) != count($paramValue)){return null;}
    $ret = '';
    foreach($paramName as $i => $name){
        $ret .= urlencode($name) . '=' . urlencode($paramValue[$i]) . '&';
    }
    return rtrim($ret, '&');
}

/**
 * Verifie si le header Accept permet le type "$type/$sous_type".
 * @param string $type Le type MIME
 * @param string $sous_type le sous type MIME
 */
function verifyAcceptsType(string $type, string $sous_type) : bool{
        foreach(parseAcceptHeader() as $accept){
            if($accept->type == "$type/$sous_type" || $accept->type == "$type/*" || $accept->type == "*/*"){
                return true;
            }
            //echo "$type/$sous_type vs " . $accept->type . PHP_EOL;
    }
    return false;
}

/**
 * Prend le paramètre 'q' dans l'en-tête "Accept" et retourne 1 si l'en-tête n'est pas accessible ou le type spécifié n'est pas présent
 * @param string $MIME_type Le type et le sous-type MIME à évaluer
 * @return bool Si le type complet MIME est disponible et est le plus haut dans le poid
 */
function isMaxWeightAndAvailable(string $MIME_Type) : bool{
    $accept = (defined('HEADERS') && array_key_exists('Accept', HEADERS))? HEADERS['Accept'] : '*/*';
    $comma_exploded = explode(',', $accept);
    $this_weight = getWeightOfAccept($MIME_Type);

    $type_availble = ['*/*', 'text/html', 'text/*', 'application/json', 'application/xml', 'application/*', 'audio/mp3', 'audio/*'];
    if(!in_array($MIME_Type, $type_availble)){return false;}

    if($this_weight == 0){return false;}
    foreach($comma_exploded as $type){  //bubble sort
        if($this_weight < getWeightOfAccept(explode(';', $type)[0])  &&  array_search_include($type_availble, $type) == -1){ //if the type watched has a greater priority and is available
            return false;
        }
    }
    return true;
}

/**
 * Retourne le poid du type MIME
 * @param bool $accept_error If should search in Accept-Error
 */
function getWeightOfAccept(string $MIME_type, bool $accept_error=false) : float{
    $MIME_type = rtrim(ltrim($MIME_type));  //no whitespace before and after
    
    if(!array_key_exists('Accept-Error', HEADERS) && !array_key_exists('Accept', HEADERS)){
        return 1;
    }
    
    //also check if Accept-Header is given
    $accept = ($accept_error && array_key_exists('Accept-Error', HEADERS))? HEADERS['Accept-Error'] : HEADERS['Accept'];
    if(!str_contains($accept, $MIME_type)){return 0;}

    $comma_exploded = explode(',', $accept);
    $key = array_search_include($comma_exploded, $MIME_type);
    $interestring = $comma_exploded[($key !== -1)? $key : 0];   //if is not in the array, get the first element
    

    if(!str_contains($interestring, ';')){return 1;}
    return (float) (ltrim(explode(';', $interestring)[1], ' q='));
}

/**
 * Si $needle est présent dans un des éléments de $haystack
 * @param array $haystack Un array dont les elements seront convertie en string par strval()
 * @param string $needle Ce que l'on doit chercher
 * @return string|int if don't find $needle returns -1
 */
function array_search_include(array $haystack, string $needle) : string|int{
    foreach($haystack as $key => $elem){
        $e = strval($elem);

        if(str_contains($e, $needle)){
            return $key;
        }
    }
    return -1;
}

/**
 * Parse the header Accept from 'text/html; q=0.2, audio/wav' to [{type: 'text/html', q: 0.2}, {type: 'audio/wav', q: 1}]
 * @param bool $associative If the function returns an associative array (see return)
 * @param bool $errorHeader If true parse the Accept-Error header instead of the Accept
 * @return array Si !associative, retourne un array d'objet avec deux propriétées: type pour le type MIME et q pour le poid
 */
function parseAcceptHeader(bool $associative = false, bool $errorHeader = false) : Array{
    if(!defined('HEADERS') || !array_key_exists($errorHeader? 'Accept-Error' : 'Accept', HEADERS)){
       return []; 
    }
    
    $nonParsed = HEADERS[$errorHeader? 'Accept-Error' : 'Accept'];
    $comma = explode(',', $nonParsed);
    $ret = array();

    foreach($comma as $type){
        $cont = str_contains($type, ';')? explode(';', $type) : [$type, '1'];
        if(!$associative){
            array_push(
                $ret,
                (object) [
                    'type' => ltrim($cont[0]),
                    'q'=> (float) ltrim($cont[1], 'q= ')    //the weight
                ]
            );
        }else{
            $ret[ltrim($cont[0])] = (float) ltrim($cont[1], 'q= ');
        }
    }
    return $ret;
}

/**
 * Verify the Accept header and redirects if needed
 * @param array $req The request in an associative array
 */
function checkAccept(array $req, bool $redirect=true) : void{
    if(!array_key_exists('file', $req) || empty($req['file'])){$req['file'] = '';}

    if(array_key_exists('Accept-Language', HEADERS) && !str_contains(HEADERS['Accept-Language'], '*') && !str_contains(HEADERS['Accept-Language'], 'en')){
        throw new ServerError('Cannot provide language other that english');
    }

    if(array_key_exists('Accept-Charset', HEADERS) && !str_contains(HEADERS['Accept-Charset'], '*') && !str_contains(HEADERS['Accept-Charset'], 'utf-8')){
        throw new ServerError('Cannot provide another charset that utf-8', 406);
    }

    if(isMaxWeightAndAvailable('*/*')){//the representation by default is the one we first requested
        return;
    }

    if(verifyAcceptsType('audio', 'mp3') && (isMaxWeightAndAvailable('audio/mp3') || isMaxWeightAndAvailable('audio/*'))){    //if exepct audio/mp3 redirect to get-music.php, par défaut on envoie application/json
       if($redirect){redirect('mp3', ['file'], [$req['file']]);}
       return;
    }/*in comment because don't work well
    else if(array_key_exists('Accept', HEADERS) && str_contains(HEADERS['Accept'], 'audio/')){
        throw new ServerError("Can only give mp3 audio files", 406);
    }*/

    else if(verifyAcceptsType('text', 'html') && (isMaxWeightAndAvailable('text/html') || isMaxWeightAndAvailable('text/*'))){
        if($redirect){redirect('html', ['file'], [$req['file']]);}
        return;
    }
    else if(verifyAcceptsType('application', 'json') || verifyAcceptsType('application', 'xml')){
        if($redirect){redirect('music-infos', ['file'], [$req['file']]);}
        return;
    }
    
    $types = array();
    foreach(parseAcceptHeader() as $i => $head){
        $types[$i] = $head->type;
    }
    throw new ServerError('Cannot provide this representation: json, html, xml and mp3 responses are the only other alternatives.', 406, 'Tried to get a ' . arrayToString($types, ' or a ', "'"));
}

/**
 * Read the 'Authorization' header
 * @param array $req the request array
 * @return string|false returns the level of the user or false of failed to read 
 */
function checkAuthorization() : string|false{
    if(!array_key_exists('Authorization', HEADERS) || empty(HEADERS['Authorization']) || HEADERS['Authorization'] == ''){return 'user';}
    
    $type = explode(' ', HEADERS['Authorization'])[0];
    $value = explode(' ', HEADERS['Authorization']);
    if(!array_key_exists(1, $value)){return false;}
    $value = $value[1];
    
    if($type != 'Basic'){throw new ServerError('Unhandled authorization type', 401, "tried to authorize with '$type'", ['WWW-Authenticate: Basic']);}

    $id = explode(':', $value, 2)[0];
    $password = explode(':', $value, 2);
    if(!array_key_exists(1, $password)){return false;}
    $password = $password[1];

    foreach(['admin', 'tester'] as $user){
        if($id == $_SERVER["${user}ID"] && $password == $_SERVER["{$user}Password"]){   //check the user credentials
            return $user;
        }elseif($id == $_SERVER["{$user}ID"] && $password != $_SERVER["{$user}Password"]){//check the user credentials
            throw new ServerError('Wrong password for this account', 401, '', ['WWW-Authenticate: Basic']);
        }
    }

    return false;
}

/**
 * Check if the param file is good, if not throws a ServerError 
 * @param array|string $req the request array, if it's a string handle this parameter by $req = ["file" => $req];
 */
function checkParamFile(array | string &$req){
    if(!array_key_exists('file', $req) || empty($req['file']) || $req['file'] == ''){
        throw new ServerError("Parameter 'file' is missing or empty", 400);
    }

    if(gettype($req) == 'string'){
        $req = ['file' => $req];

    }else if(!array_key_exists('file', $req)){
        throw new ServerError("the parameter 'file' is missing.", 400);
        
    }elseif(empty($req['file'] || $req['file'] == '')){
        throw new ServerError("No value is assigned to the parameter 'file'.", 400);
    }
    
    if(!array_key_exists('extension', pathinfo($req['file']))){ //don't have extention
        $req['file'] .= '.mp3';
    }
    
    $internalpath = Music::STORAGE_PATH . $req['file'];   //chemin à l'interieur du server
    $externalpath = Music::STORAGE_URL . $req['file'];

    if(!file_exists($internalpath) || !is_file($internalpath)){
        throw new ServerError('The specified element does not exist.', 404, "Path: $externalpath");

    }elseif(str_contains($req['file'], 'vendor/')){ //les librairies composer
        throw new ServerError('The access to this area is forbidden.', 403, "Attempt to access the 'vendor' directory.");
    
    }else if(!str_contains(realpath($internalpath), Music::STORAGE_PATH)){  //on vérifie que le script ne cherche pas en dehors du bon dossier
        throw new ServerError("The specified path leads outside the 'api' folder", 403, (realpath($externalpath) === false)? 'realpath() failed' : "attempt to access " . realpath($externalpath));
    }elseif(!str_ends_with($req["file"], '.mp3')){
        throw new ServerError("Can't open this file", 400);
    }
}

/**
 * Checks if $request[$params] are booleans
 * @param array $request The request like $_GET
 * @param  string|array $params 
 * @return bool If all $params are booleans return true
 */
function checkIfParamBool(array $request, string|array $params) : bool{
    if(gettype($params) == "string"){$params = [$params];}
    
    foreach($request as $name=>$value){
        if(
            in_array($name, $params) 
            && $value != 'true' 
            && $value != 'false' 
            && $value != 1 
            && $value != 0
        ){
            return false;
        }
    }
    return !in_array($name, $params);
}

/**
 * Convert $str in PHP bool
 * @return ?bool returns null if can't read it
 */
function readBoolString(string $str): ?bool{
    if($str == '1' || $str == 'true'){return true;}
    if($str == '0' || $str == 'false'){return false;}
    
    return null;
}

function xmlentities(string $str, $flags = ENT_QUOTES | ENT_XHTML) : string{
    return htmlentities($str, $flags, 'UTF-8');
}

/**
 * xmlentities() but modified for array_walk
 */
function xmlentities_callback(string &$str) : void{
    $str = xmlentities($str);
}