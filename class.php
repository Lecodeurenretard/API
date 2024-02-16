<?php
require("vendor/autoload.php");
use wapmorgan\Mp3Info\Mp3Info;

/**
 * Représente un fichier musique .mp3
 */
class Music {
    /** @var string Contient l'url jusqu'au répertoire des musiques*/
    public const STORAGE_URL = 'http://musiques.nils.test.sc2mnrf0802.universe.wf/api/';
    
    /** @var string Contient le chemin jusqu'au répertoire des musiques*/
    public const STORAGE_PATH = '/home/sc2mnrf0802/nils.test.musiques.wf/api/';

    /** @var string Le titre de la musique*/
    public string $title;
    
    /** @var string[] Les compositeurs ayant participés*/    
    public array $composers = array();
    
    /** @var int Le numéro de la piste de la musique*/
    public int $track;

    /** @var string Le commentaire laissé*/    
    public string $commentaire;

    /** @var string  Le chemin pour accéder au fichier à partir de /api*/
    private string $path;

    private string $fullPath = self::STORAGE_URL;
    public function __construct(string $title='', array $composers=[], int $track=-1, string $commentaire="", string $path){
        if(empty($path) && $path !==''){throw new ServerError("Cannot set object with empty path", 500,'Line: '. __LINE__ . ' of file: ' . __FILE__);}
        $this->title = (isset($title))? $title : '';
        $this->composers = (isset($composers))? $composers : [];
        $this->track = (isset($track))? $track: -1;
        $this->commentaire = isset($commentaire)? $commentaire : '';
        $this->path = $path;
        $this->fullPath = Music::STORAGE_URL.$path;

        if($this->title===$this->commentaire && $this->title===''&&$this->track===-1&&$this->composers===[] && $path!==''){$this->setFromFile($path);}
    }

    public function __serialize() : array{
        return [
            "title"=> $this->title,
            "composers"=> serialize($this->composers),
            "track"=> $this->track,
            "commentaire"=> $this->commentaire,
            "path" => $this->path
        ];
    }
 
    public function __unserialize(array $data) : void{
        $this->title = $data["title"];
        $this->composers = unserialize($data["composers"]);
        $this->track = $data["track"];
        $this->commentaire = $data["commentaire"];
        $this->path = $data["path"];
        $this->fullPath = Music::STORAGE_URL . $this->path;
    }

    public function __toString() : string{
        return $this->title . " (" . implode(", ", $this->composers) . '; ' . $this->track .")";
    }

    /**
     * Convertit l'objet courant en JSON
     */
    public function jsonEncode() : string{
        return  //on fait en sorte qu'un humain puisse lire la sortie
            '{ '                                                                                        . PHP_EOL .
            '    "title":  "'. $this->title.'",'                                                        . PHP_EOL . 
            '    "composers": ['                                                                        . PHP_EOL .
            '        ' . arrayToString($this->composers, ','                                            . PHP_EOL .
            '        ', '"')                                                                            . PHP_EOL .
            '    ],'                                                                                    . PHP_EOL .
            '    "track": '. $this->track.','                                                             . PHP_EOL . 
            '    "commentaire": "'. $this->commentaire.'",'                                             . PHP_EOL .
            '    "path": "' . $this->path .'"'                                                          . PHP_EOL .
            '}';

    }

    /**
     * Convertit la string en format JSON en objet Music
     * @param self $json Le json encodé par Music->jsonEncode()
     * @throws ServerError Lance une ServerError si $json n'est pas un objet Music
     */
    static function jsonDecode(string $json) : self{
        $obj = json_decode($json, false, 3);
        if(
            empty($obj->title) 
            || empty($obj->composers) 
            || empty($obj->track) 
            || empty($obj->commentaire) 
            || empty($obj->path)
            ){
                throw new ServerError("The object given to decode is not a Music object at line " . __LINE__ . " from: " . __FILE__, 500, "json given: " . $json);
            }
        
        return new Music($obj->title, explode('/', $obj->composers), $obj->track, $obj->commentaire, $obj->path);
    }
    
    /**
     * Convertit la string en format JSON en objet Music et set l'objet à l'objet décodé
     * @param string $json Le json encodé par Music->jsonEncode()
     * @return self L'objet décodé
     */
    public function json_decode(string $json) : self{
        $decoded = self::jsonDecode($json);
        $this->setTo($decoded);
        return $decoded;
    }

    /**
     * Set les fields de $this aux fields de $obj 
     */
    protected function setTo(self $obj){
        $this->title = $obj->title;
        $this->composers = $obj->composers;
        $this->track = $obj->track;
        $this->commentaire = $obj->commentaire;
        $this->path = $obj->path;
        $this->fullPath = $obj->fullPath;
    }

    /**
     * Set les fields de $this suivant les arguments
     */
    private function set(?string $title, ?array $composers, ?int $track, ?string $commentaire="", string $path){
        $this->title        =    isset($title)      ? $title       : '';
        $this->composers    =    isset($composers)  ? $composers   : [];
        $this->track        =    isset($track)      ? $track       : -1;
        $this->commentaire  =    isset($commentaire)? $commentaire : '';
        $this->path = $path;
        $this->fullPath = Music::STORAGE_URL.$path;
    }

    /**
     * Vérifie que $this et $obj sont égaux
     * @param Music $obj L'autre objet.
     */
    protected function isEqual(Music $obj) : bool{
        foreach($this as $field => $name){
            if($field != $obj->$name){return false;}
        }
        return true;
    }

    public function isDefault(Music $obj) : bool{
        return $obj->isEqual(MusicdefaultObject);
    }

    /**
     * Cherche la musique et set l'objet courant.
     * @param string $path Le chemin (à partir de /api/) du fichier, ex: ex.mp3 pour  /home/sc2mnrf0802/nils.test.musiques.wf/api/ex.mp3
     */
    public function setFromFile(string $path) : void{
        $music = new Mp3Info(Music::STORAGE_PATH . $path, true);
        
        $song = ret_array_key_if_defined($music->tags, 'song', '');
        $artist = explode('/', ret_array_key_if_defined($music->tags, 'artist', ''));        
        $track = ret_array_key_if_defined($music->tags, 'track', -1);        
        $comment = ret_array_key_if_defined($music->tags, 'comment','');    

        $this->set($song, $artist, $track, $comment, $path);
    }

    /**
     * Cherche le fichier et renvoie sa correspondance en objet Music
     * @param string $path Le chemin (à partir de /api/) du fichier 
     */
    public static function getFromFile(string $path) : Music{
        $ret = MusicdefaultObject;
        $ret->setFromFile($path);
        return $ret;
    }
}
/** @var Music Représente l'objet par défaut */
const MusicdefaultObject = new Music('', [], -1, '', '');

class ServerError extends ErrorException{
    const code_list = [
        0 => "Unknown Error",
        400 => "Bad request",
        403 => "Forbidden",
        404 => "Not found",
        405 => "Method not allowed",
        406 => "Not Acceptable",
        415 => "Unsupported Media Type",
        417 => "Expectation Failed",
        418 => "I'm a teapot",       //should not be used, just let it sit there
        422 => "Unprocessable content",
        500 => "Internal server error",
        501 => "Not Implemented",
        503 => "Service unavailable"
    ];

    public string $name = self::code_list[0];
    private string $misc; 
    public function __construct(string $message, int $code=0, string $misc=''){
        $this->message = $message;
        if(empty(self::code_list[$code])){$code = 0;}
        $this->code = $code;
        $this->name = self::code_list[$code];
        $this->misc = $misc;
    }

    public static function constructFromThrowable(Throwable $obj, string $misc='') : self{
        $e = new ServerError($obj->getMessage(), 0, $misc);
        $e->name = get_class($obj);
        return $e;
    }

    public function __toString() : string{
        return "Error " . $this->code . ': '. $this->message . "  //($this->misc)";
    }

    public function __debugInfo() : array{
        $arr = [
            'type' => $this->code . ': ' . $this->name,
            'message' => $this->message,
            'other informations' => $this->misc
        ];
        if($this->misc == null){unset($arr['other informations']);}
        return $arr;
    }

    public function toJson() : string{
        return
        '{'                                                                            . PHP_EOL .
        '   "code": ' . $this->code                                             . ','  . PHP_EOL .
        '   "name": "' . $this->name                                            . '",' . PHP_EOL .
        '   "message": "'. $this->message                                       . '",' . PHP_EOL .
        '   "stack-trace": "'. $this->getTraceAsString()                        . '",' . PHP_EOL .
        '   "other_info":"' .  $this->misc                                      . '"'  . PHP_EOL .
        '}'                                                                            . PHP_EOL ;
    }
    
    public static function fromJson(string $json) : ServerError{
        $obj = json_decode($json, false, 2);
        return new ServerError($obj->message, $obj->code, $obj->other_info);
    }
}

/**
 *  @abstract Represents the database 
 */
class DB{
    /* *
     * Cherche les musiques à l'emplacement donné.
     * @param string $album Le nom du répertoire à rechercher sans Music::STORAGE_URL; si string vide, prendra toutes les musiques de la BDD.
     * @return Music[] | null Les musiques trouvées dans un array de Music; si le répertoire n'existe pas, return null
     
    static function getMusicsFrom(string $album) : ?array{
        $base = Music::STORAGE_URL . $album;
        if(!file_exists($base)){
            return null;
        }

        return DB::glob($base .'*.mp3'); 
    }*/

    /**
     * glob but return an array of Music
     * @param string $pattern The pattern. No tilde expansion or parameter substitution is done. Accepts special characters.
     * @param ?int $flags =0 | Valid flags: GLOB_MARK, GLOB_NOSORT, GLOB_NOCHECK, GLOB_NOESCAPE, GLOB_BRACE, GLOB_ONLYDIR, GLOB_ERR; See the doc for more details.
     * @throws ServerError Lance une ServerError si glob échoue (=== false)
     */
    static function glob(string $pattern, ?int $flags = 0) : array{
        $glob = glob($pattern, $flags);

        if($glob === false){throw new ServerError("The glob function encounters an error, please check $pattern (pattern) and $flags (flags).", 500, "Error occured in line " . __LINE__ ." of file " . __FILE__);}
        
        foreach($glob as $i=>$file){
            $files[$i] = Music::getFromFile($file);
        }
        return $files;
    }
}


/**
 * Transforme $arr en string de la forme '$autour$arr[0]$autour$sep$autour$arr[1]$autour...'; par exemple echo arrayToString(["moi", "toi"], ' + ', '"'); //"moi" + "toi"
 * @param array $arr l'array à être transformé
 * @param string $sep =', ' | le séparateur
 * @param string $autour ='' | ce qui entoure chaque élément
 * @param bool $keys =false | Si l'on doit afficher les clefs
 * @return string tous les éléments de $arr avec entre eux $sep
 */
function arrayToString(array $arr, string $sep=', ', string $autour='', $keys = false) : string{
    $ret = '';
    foreach($arr as $i => $elem){
        $ret .= $autour . ($keys? $i . $autour . ': ' . $autour : '') . $elem . $autour . $sep;
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
 * @param bool $replaceHeaders =true | Le deuxième argument de header()
 * @param int $code =308 | Le code de la réponse http à envoyer.
 * @param string ...$headers | Les headers à envoyer à la place de ceux reçus, si pas set, la fonction enverra les headers des la page
 * @throws ServerError Si !( 299 < $code < 399) lève une ServerError
 */ 
function redirect(string $url, ?array $paramName = null, ?array $paramValue = null, bool $replaceHeaders = true, int $code = 308, ?string ...$headers) : void{
    if($code > 399 || $code < 299){
        throw new ServerError("Cannot redirect with the code: '$code'", 500, "function redirect()");
    }

    $head = isset($headers)? $headers : HEADERS;
    $params = (isset($paramName) && count($paramName) == count($paramValue) && count($paramName) != 0)? 
        '?' .  paramURL($paramName, array_values($paramValue)) 
        : '';

    foreach($head as $header){header($header, $replaceHeaders);}
    
    header("Location: $url$params", false, $code);
    header("Content-Type: text/html; charset=utf-8'", false);
    exit("You should be redirected, if the redirection don't work, go to <a href'$url$params'>". basename($url) ."</a>");
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
    $exists = false;
    foreach(parseAcceptHeader() as $accept){
        if($accept->type == "$type/$sous_type" || $accept->type == "$type/*" || $accept->type == "*/*"){
            return true;
        }
    }
    return false;
}

/**
 * Prend le paramètre 'q' dans l'en-tête "Accept" et retourne 1 si l'en-tête n'est pas accessible ou le type spécifié n'est pas présent
 * @param string $MIME_type Le type et le sous-type MIME à évaluer
 * @return bool Si le type complet MIME est disponible et est le plus haut dans le poid
 */
function isMaxWeightAndAvailble(string $MIME_Type) : bool{
    $comma_exploded = explode(',', HEADERS['Accept']);
    $this_weight = getWeightOfAccept($MIME_Type);

    $type_availble = ['*/*', 'text/html', 'text/json', 'application/json', 'audio/mp3'];
    if(!in_array($MIME_Type, $type_availble)){return false;}

    if($this_weight == 0){return false;}    //always true
    foreach($comma_exploded as $type){
        if($this_weight < getWeightOfAccept(explode(';', $type)[0])  &&  array_search_include($type_availble, $type) == -1){ //if the type watched has a greater priority and is availble
            return false;
        }
    }
    return true;
}

/**
 * Retourne le poid du type MIME
 */
function getWeightOfAccept(string $MIME_type) : float{
    $MIME_type = rtrim(ltrim($MIME_type));  //no whitespace before and after
    if(!array_key_exists('Accept', HEADERS)){return 1;}
    if(!str_contains(HEADERS['Accept'], $MIME_type)){return 0;}

    $comma_exploded = explode(',', HEADERS['Accept']);
    $key = array_search_include($comma_exploded, $MIME_type);
    $interestring = $comma_exploded[($key !== -1)? $key : 0];   //if is not in the array, get the first element
    

    if(!str_contains($interestring, ';')){return 1;}
    return (float) (ltrim(explode(';', $interestring)[1], ' q='));
}

/**
 * Si $needle est présent dans un des éléments de $haystack
 * @param array $haystack Un array dont les elements seront convertie en string par strval()
 * @param string $needle Ce que l'on doit chercher
 */
function array_search_include(array $haystack, string $needle) : mixed{
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
 * @return array Retourne un array d'objet avec deux propriétées: type pour le type MIME et q pour le poid
 */
function parseAcceptHeader() : Array{
    $nonParsed = HEADERS['Accept'];
    $comma = explode(',', $nonParsed);
    $ret = array();

    foreach($comma as $type){
        $cont = str_contains($type, ';')? explode(';', $type) : [$type, '1'];
        array_push(
            $ret,
            (object) [
                'type' => ltrim($cont[0]),
                'q'=> (float) ltrim($cont[1], 'q= ')    //the weight
            ]
        );
    }
    return $ret;
}
?>