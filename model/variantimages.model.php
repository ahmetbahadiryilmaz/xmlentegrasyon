<?
class variantimages{
    public $variantId;
    public $images;
    public $site;
 
    
    public function __construct($variantId,$images,$site) {
        $this->variantId=$variantId;
        $this->images=$images;
        $this->site=$site;
 
    }


 

   public  static function fetch($id)
   {
     $r =    db::fetch("select * from variantimages where variantId=?",array($id));
     $r=$r[0];
     $verinew=  new variantimages($r["variantId"],$r["images"],$r["site"]);
 
     return $verinew;
   }

   public static function isExists($id,$site){
 
    $r=  db::fetch("select * from variantimages where variantId=:variantid and site=:site",array(
      "variantid" => $id,
      "site" => $site,
    
    ));

    if (count($r)>0){
        return  true;
    }else{
      return  false;
    }
   }

 
   public  static function fetchAll() { 
 
      $veriler=array();
      $verilerR = db::fetch("select * from variantimages");
 
      foreach( $verilerR as $r){
   
        $verinew = new variantimages($r["variantId"],$r["images"],$r["site"]);
      
        $veriler[]= $verinew;

      }
      return $veriler;
   }
    


    public  function insert(  )
    {
      try {
        db::insert("variantimages",[
          "variantId"=> $this->variantId,
          "images"=> $this->images,
          "site"=> $this->site,
        
        ]);
      } catch (Exception $ex) {
          if($ex->errorInfo[1]=="1062"){
            echo "duplicate";
          }else{
            print_r($ex);
            print_r($this);
            exit();
          }
     
      }
    
    }
}
?>