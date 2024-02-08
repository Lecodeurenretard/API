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
     */
    static function jsonDecode(string $json) : self{
        $obj = json_decode($json, false, 3);
        if(!isset($obj->title, $obj->composers, $obj->track, $obj->commentaire, $obj->path)){throw new ServerError("The object given to decode is not a Music object at line " . __LINE__ . " from: " . __FILE__, 500, "json given: " . $json);}
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
       if(empty($path)){throw new ServerError("Cannot set object with empty path", 500);}
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
        if($path === null){throw new ServerError("The path argument is null at l " . __LINE__ . " in " . __FILE__ . ' .', 500);}

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
     */
    static function glob(string $pattern, ?int $flags = 0) : array{
        if(glob($pattern, $flags) === false){throw new ServerError("The glob function encounters an error, please check $pattern (pattern) and $flags (flags).", 500, "Error occured in line " . __LINE__ ." of file " . __FILE__);}
        foreach(glob($pattern, $flags) as $file=>$i){
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
?>