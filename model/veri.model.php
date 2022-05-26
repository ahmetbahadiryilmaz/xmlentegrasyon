<?
class veri{
    public $id;
    public $stockid;
    public $url;
    public $md5url;
    public $veri;
    public $active;
    public $site;
    public $error;
    
    public function __construct($site,$url,$veri,$stockid,$id="") {
        $this->site=$site;
        $this->url=$url;
        $this->veri=$veri;
        $this->md5url=md5($url);
        $this->id=$id;
        $this->active=1;
        $this->stockid=$stockid;
    }


  public function disabled(){
    $this->active=0;
  }


   public  static function fetch($id)
   {
     $r =    db::fetch("select * from veri where id=?",array("id"=>$id));
     $r=$r[0];
     $verinew=  new veri($r["site"],$r["url"],$r["veri"],$r["stockid"],$r["id"]);
     $r["active"]?"":$verinew->disabled();
     return $verinew;
   }

   public static function isUrlExists($url){
 
    $r=  db::fetch("select * from veri where md5url=:url ",array(
      "url"=>md5($url) 
    ));

    if (count($r)>0){
        return  true;
    }else{
      return  false;
    }
   }


   public static function isStockIdExists($stockid,$site){

      $r=  db::rowCount("select stockid from veri where  stockid=:stockid and site=:site",array(
        "stockid"=>$stockid,
        "site"=>$site
      ));
    
      if ( ($r)>0){
          return  true;
      }else{
        return  false;
      }
   } 

   public  static function fetchAll() { 
 
      $veriler=array();
      $verilerR = db::fetch("select * from veri");
 
      foreach( $verilerR as $r){
   
        $verinew = new veri($r["site"],$r["url"],$r["veri"],$r["stockid"],  $r["id"]);
        $r["active"]?"":$verinew->disabled();
        $veriler[]= $verinew;

      }
      return $veriler;
   }
   public  static function fetchBySiteAll($site) { 
 
    $veriler=array();
    $verilerR = db::fetch("select * from veri where site=?",array($site));
    foreach( $verilerR as $r){
      $verinew = new veri($r["site"],$r["url"],$r["veri"],$r["stockid"],  $r["id"]);
      $r["active"]?"":$verinew->disabled();
      $veriler[]= $verinew;

    }
    return $veriler;
 }
  


    public  function insert(  )
    {
      try {
        db::insert("veri",[
          "url"=> $this->url,
          "md5url"=> md5($this->url),
          "veri"=> $this->veri,
          "stockid"=> $this->stockid,
          "active"=>$this->active,
          "site"=>$this->site,
          "error"=>$this->error
        ]);
      } catch (Exception $ex) {
          if($ex->errorInfo[1]=="1062"){
            echo "duplicate";
          }else{
            print_r($ex);
            $veri=unserialize($this->veri);
            print_r($veri);
            exit();
          }
     
      }
    
    }
}
?>