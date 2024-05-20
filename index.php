<?php
    if($_SERVER["REQUEST_METHOD"] == 'OPTIONS'){
         header('Allow: GET, POST, HEAD, OPTIONS');
                http_response_code(200); 
                return '';
    }

    define('PAGE', 'index');
    define('HEADERS', []);
    require("class.php");
    $req = [];
    if(isset($_GET)){
        $req= $_GET;
    }else if(isset($_POST)){
        $req = $_POST;
    }
    //print_r($req);
    //print_r(array_keys($req));
    //echo count($req);
    redirect("music-infos", array_keys($req), $req, true, 301);