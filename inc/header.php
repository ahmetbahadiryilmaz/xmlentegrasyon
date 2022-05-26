<?
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
define('MAX_FILE_SIZE', 6000000); 

require_once "vendor/autoload.php";
require_once "curl.php";
require_once "db.php";
require_once "f.php";
require_once "simple_html_dom.php";
require_once "botayar.php";



require_once("model/goods.model.php");
require_once "model/veri.model.php";
require_once "model/variantimages.model.php";
require_once "model/fetchError.model.php";


require_once "botlar/bershaka.php";
require_once "botlar/massimodutti.php";
require_once "botlar/pulland.php";
require_once "botlar/oysho.php";
require_once "botlar/stradiv.php";
require_once "botlar/hm.php";



?>