<?php
include("vendor/autoload.php");
use wapmorgan\Mp3Info\Mp3Info;

DotenvVault\DotenvVault::createImmutable(__DIR__)->safeload();  //init dotev

define('FORBIDDEN_CHARS', ["\t", "\r", "\n", "\x00", "\x01", "\x02", "\x03", "\x04", "\x04", "\x05", "\x1b"]);

include_once("functions.php");
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

    /** @var string L'album duquel la musique est issue*/    
    public string $album;

    /** @var string  Le chemin pour accéder au fichier à partir de /api*/
    private string $path;

    private string $fullPath = self::STORAGE_URL;
    public function __construct(string $title='', array $composers=[], int $track=-1, string $commentaire="", string $album="", string $path){        if(empty($path) && $path !==''){throw new ServerError("Cannot set object with empty path", 500,'Line: '. __LINE__ . ' of file: ' . __FILE__);}
    if(empty($path) && $path !==''){throw new ServerError("Cannot set a Music object  with empty path", 500);}
        $this->title =          isset($title)?          str_replace(FORBIDDEN_CHARS, '', $title)          : '';//remove null bytes
        $this->composers =      isset($composers)?      str_replace(FORBIDDEN_CHARS, '', $composers)      : [];
        $this->track =          isset($track)?          str_replace(FORBIDDEN_CHARS, '', $track)          : -1;
        $this->commentaire =    isset($commentaire)?    str_replace(FORBIDDEN_CHARS, '', $commentaire)    : '';
        $this->album =          isset($album)?          str_replace(FORBIDDEN_CHARS, '', $album)          : '';
        $this->path = $path;
        $this->fullPath = Music::STORAGE_URL.$path;

        if($this->title===$this->commentaire && $this->title===''&& $this->album==''&&$this->track===-1&&$this->composers===[] && $path!==''){$this->setFromFile($path);}
    }

    public function __serialize() : array{
        return [
            "title"         => $this->title,
            "composers"     => serialize($this->composers),
            "track"         => $this->track,
            "album"         => $this->album,
            "commentaire"   => $this->commentaire,
            "path"          => $this->path
        ];
    }
 
    public function __unserialize(array $data) : void{
        $this->title = $data["title"];
        $this->composers = unserialize($data["composers"]);
        $this->track = $data["track"];
        $this->album = $data["album"];
        $this->commentaire = $data["commentaire"];
        $this->path = $data["path"];
        $this->fullPath = Music::STORAGE_URL . $this->path;
    }

    public function __toString() : string{
        return $this->title . " (" . implode(", ", $this->composers) . '; ' . $this->track . "; in " . $this->album .")";
        //title (composer1, composer2, ...; track; in album)
    }   
    public function toHTML() : string{
        return "<p><span class='music-title'>$this->title</span> (<span class='music-artists'>" . arrayToString($this->composers) . "</span> | <span class='music-track'> $this->track </span> | music in album <span class='music-album'>$this->album</span>)";
        //title (composer1, composer2, ...; track; in album)
    }   

    
    /**
     * Convertit l'objet courant en JSON
     * @param int $baseIndent =0| L'indentation de base
     */
    public function jsonEncode(int $baseIndent=0) : string{
        $indent = str_repeat("\t", $baseIndent);
        return  //on fait en sorte qu'un humain puisse lire la sortie
        $indent .'{'                                                                                . PHP_EOL .
            $indent . "\t" . '"title":  "'. $this->title.'",'                                       . PHP_EOL . 
            $indent . "\t" . '"composers": ['                                                       . PHP_EOL .
            /*indent */      arrayToString($this->composers, ',' . PHP_EOL , '"', false, $baseIndent + 2)  . PHP_EOL .
            $indent . "\t" . '],'                                                                   . PHP_EOL .
            $indent . "\t" . '"track": '. $this->track.','                                          . PHP_EOL . 
            $indent . "\t" . '"album": "'. $this->album.'",'                                        . PHP_EOL . 
            $indent . "\t" . '"commentaire": "'. $this->commentaire.'",'                            . PHP_EOL .
            $indent . "\t" . '"path": "' . $this->path .'"'                                         . PHP_EOL .   
        $indent .'}';

    }

    /**
     * Convertit la string en format JSON en objet Music
     * @param self $json Le json encodé par Music->jsonEncode()
     * @throws ServerError Lance une ServerError si $json n'est pas un objet Music
     */
    public static function jsonDecode(string $json) : self{
        $obj = json_decode($json, false, 3);
        if(
            empty($obj->title) 
            || empty($obj->composers) 
            || empty($obj->track) 
            || empty($obj->album)
            || empty($obj->commentaire) 
            || empty($obj->path)
            ){
                throw new ServerError("The object given to decode is not a Music object at line " . __LINE__ . " from: " . __FILE__, 500, "json given: " . $json);
            }
        
        return new Music($obj->title, explode('/', $obj->composers), $obj->track, $obj->commentaire, $obj->album, $obj->path);
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
     * Convertit l'objet courant en XML
     */
    public function XMLEncode(int $baseIndent=0, bool $linkStyle = false) : string{
        $indent = str_repeat("\t", $baseIndent);
        $composers = $this->composers;  //I don't modify the object's field 
        array_walk($composers, 'xmlentities_callback');
        
        $this_arr = [
            xmlentities($this->title),
            arrayToString($composers, PHP_EOL, ["<composer>", "</composer>"], false, $baseIndent + 1),
            xmlentities($this->album),
            xmlentities($this->commentaire),
            xmlentities($this->path)
        ];

        return  //on fait en sorte qu'un humain puisse lire la sortie
            $indent . "<music>"                                                . PHP_EOL . 
            $indent . "\t<title>". $this_arr[0] ."</title>"                    . PHP_EOL . 
            $indent . "\t<composers>"                                          . PHP_EOL .
            $indent . "\t" .  $this_arr[1]                                     . PHP_EOL .
            $indent . "\t</composers>"                                         . PHP_EOL .
            $indent . "\t<track>". $this->track."</track>"                     . PHP_EOL . 
            $indent . "\t<album>". $this_arr[2]."</album>"                     . PHP_EOL . 
            $indent . "\t<commentaire>". $this_arr[3] ."</commentaire>"        . PHP_EOL .
            $indent . "\t<path>" . $this_arr[4] ."</path>"                     . PHP_EOL .
            $indent . "</music>";

    }

    

    /**
     * Set les fields de $this aux fields de $obj 
     */
    protected function setTo(self $obj){
        $this->title        = $obj->title;
        $this->composers    = $obj->composers;
        $this->track        = $obj->track;
        $this->commentaire  = $obj->commentaire;
        $this->album        = $obj->album;
        $this->path         = $obj->path;
        $this->fullPath     = $obj->fullPath;
    }

    /**
     * Set les fields de $this suivant les arguments
     */
    private function set(?string $title, ?array $composers, ?int $track, ?string $album, ?string $commentaire="", string $path){
        $this->title        =    isset($title)      ? str_replace(FORBIDDEN_CHARS, '', $title)       : '';
        $this->composers    =    isset($composers)  ? str_replace(FORBIDDEN_CHARS, '', $composers)   : [];
        $this->track        =    isset($track)      ? str_replace(FORBIDDEN_CHARS, '', $track)       : -1;
        $this->album        =    isset($album)      ? str_replace(FORBIDDEN_CHARS, '', $album)       : '';
        $this->commentaire  =    isset($commentaire)? str_replace(FORBIDDEN_CHARS, '', $commentaire) : '';
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
    static private function getDefault() : Music{
        return MusicdefaultObject;
    }

    /**
     * Cherche la musique et set l'objet courant.
     * @param string $path Le chemin (à partir de /api/) du fichier, ex: ex.mp3 pour  /home/sc2mnrf0802/nils.test.musiques.wf/api/ex.mp3
     */
    public function setFromFile(string $path) : self{
        $music = new Mp3Info(Music::STORAGE_PATH . $path, true);
        
        $song = ret_array_key_if_defined($music->tags, 'song', '');
        $artist = explode('/', ret_array_key_if_defined($music->tags, 'artist', ''));        
        $track = ret_array_key_if_defined($music->tags, 'track', -1);        
        $album = ret_array_key_if_defined($music->tags, 'album','');
        $comment = ret_array_key_if_defined($music->tags, 'comment','');
        
        if(!str_ends_with($path, '.mp3')){throw new ServerError("File $path is not mp3 audio!");}

        $this->set($song, $artist, $track, $album, $comment, $path);
        return $this;
    }

    /**
     * Cherche le fichier et renvoie sa correspondance en objet Music
     * @param string $path Le chemin (à partir de /api/) du fichier 
     */
    public static function getFromFile(string $path) : Music{
        return self::getDefault()->setFromFile($path);
    }
}
/** @var Music Représente l'objet par défaut */
const MusicdefaultObject = new Music('', [], -1, '', '', '');

class ServerError extends ErrorException{
    const code_list = [
        0 => "Unknown Error",
        400 => "Bad request",
        401 => 'Unauthorized',
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
    public ?array $head;
    public function __construct(string $message, int $code=0, string $misc='', ?array $headers = null){
        $this->message = $message;
        if(!array_key_exists($code, self::code_list)){$code = 0;}
        $this->code = $code;
        $this->name = self::code_list[$code];
        $this->misc = $misc;
        $this->head = $headers;
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

    /**
     * returns the error in JSON
     */
    public function toJson() : string{
        $noAuth = '';
        $getStack='';
        try{$getStack = (checkAuthorization()=='admin' || checkAuthorization()=='tester');}                     //check if request has correct auth
        catch(ServerError $e){$e->sendErrorHeaders(); $noAuth = '   |   '. $e->getMessage();}
        $stack =  str_replace( PHP_EOL, '', $this->getTraceAsString()) . '",'. PHP_EOL ;  //remove the new line
        $stack_line = $getStack? '   "stack-trace": "'. $stack . PHP_EOL  : '';         //the line containing the stack depending on the auth
 
        return
        '{'                                                                     . PHP_EOL .
        '   "code": ' . $this->code               . ','                         . PHP_EOL .
        '   "name": "' . str_replace('"', "'", $this->name)              . '",' . PHP_EOL .// the " will be replaced by a '
        '   "message": "'. str_replace('"', "'", $this->message)         . '",' . PHP_EOL .
        $stack_line .
        '   "other_info":"' .  str_replace('"', "'", $this->misc) . $noAuth        . '"'  . PHP_EOL .
        '}'                                              . PHP_EOL ;
    }

    /**
     * returns the error in XML
     * @param bool $raw If false, the return value should be served in XHTML
     */
    public function toXML(bool $raw = true) : string{
        $noAuth = '';
        try{$getStack = (checkAuthorization()=='admin' || checkAuthorization()=='tester');}                     //check if request has correct auth
        catch(ServerError $e){$e->sendErrorHeaders(); $noAuth = '   |   '. $e->getMessage();}
        $stack_line = $getStack? "\t<stack-trace>{$this->getTraceAsString()}</stack-trace>" . PHP_EOL  : '';    //the line containing the stack depending on the auth

        $this_tab = [
            $this->code,
            $raw? $this->name : htmlentities($this->name, ENT_QUOTES | ENT_XHTML, 'UTF-8'),
            $raw? $this->message : htmlentities($this->message, ENT_QUOTES | ENT_XHTML, 'UTF-8'),
            $raw? $this->misc : htmlentities($this->misc, ENT_QUOTES | ENT_XHTML, 'UTF-8')  . $noAuth
        ];
        return 
        '<error>'                                          . PHP_EOL .
            "\t<code>{$this_tab[0]}</code>"                . PHP_EOL .
            "\t<name>{$this_tab[1]}</name>"                . PHP_EOL .
            "\t<message>{$this_tab[2]}</message>"          . PHP_EOL .
            $stack_line .
            "\t<other-info>{$this_tab[3]}</other-info>"    . PHP_EOL .
        '<error>'                                          . PHP_EOL ;
    }
    
    public static function fromJson(string $json) : ServerError{
        $obj = json_decode($json, false, 2);
        return new ServerError($obj->message, $obj->code, $obj->other_info);
    }

    /**
     * Get the max accept in Accept-Error header. If encounters an error return '?/?'.
     * If two attributes have the max weight follow return the first of this list: application/json, application/xml, application/* or * / *
     */
    public static function getMaxAccept() : string{
        if(!array_key_exists('Accept-Error', HEADERS) && !array_key_exists('Accept', HEADERS)){return '*/*';}

        $is_err = array_key_exists('Accept-Error', HEADERS);
        $accept = $is_err? HEADERS['Accept-Error'] : HEADERS['Accept'];   //if Accept-Error is not given, take the Accept header

        if(!str_contains('application/json', $accept) && !str_contains('application/xml', $accept) && !str_contains('application/*', $accept) && !str_contains('*/*', $accept)){
            return '?/?';
        }

        $listWeights = [
            'application/json' => getWeightOfAccept('application/json', true),
            'application/xml' =>  getWeightOfAccept('application/xml', true),
            'application/*' => getWeightOfAccept('application/*', true),
            '*/*' => getWeightOfAccept('*/*', true),
        ];

        return array_search(max($listWeights), $listWeights);    
    }

    public function sendErrorHeaders(){
        if(empty($this->head)){return;}
        foreach($this->head as $header){
            header($header);
        }
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
?>