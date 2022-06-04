<?
 
 

class hm{
     public $good ;
     public $error="";
     public $classname="hm";
     
     private $mode= modes::$create;
     public function __construct( ) {
        $this->good=new good();
     }
     public function setMode($mode){
      $this->mode=$mode;
     }


 

     public  function setPrice($price){
      $price= str_replace("TL","",$price);
      $price= str_replace(",","",$price);
      $price= str_replace(".","",$price);
      $price= str_replace(".","",$price);
      $price= substr($price,0,strlen($price)-2).".".substr($price,strlen($price)-2,2);
      $this->good->Price= $price;
      $this->good->SellingPrice=$price;
      $this->good->TotalArrivalPrice= $price;
      $this->good->TotalSellingPrice=$price;
     }


       public  function setPriceVariant($variant,$price){
     
        $variant->Price= $price;
        $variant->SellingPrice=$price;
        $variant->TotalArrivalPrice= $price;
        $variant->TotalSellingPrice=$price;
      }




     public   function goodGetir($url){
    
       $curl=new curl();
       $curl->setCacheFolder(__DIR__."/../cache/".$this->classname."/");
       $good=$this->good;   
       $getPage= $curl->getPage($url);    
     
       echo $curl->getHttpcode()."<br>";
       if($curl->getHttpcode()=="404" ||$curl->getHttpcode()=="410"  ){
          $this->error = fetchError::$sayfabulunamadi;
          return false;
       } 
         
       
        $f=  f::clean(($getPage));
       
        $splity=explode("productArticleDetails = {",$f);
        $splity2=explode("};",$splity[1]);
         
        $hmjson="{".$splity2[0]."}";
       
        $hmjson =  preg_replace("#(isDesktop.*?:\s')#","'",$hmjson);
   
        $hmjson = str_replace("\'","",trim($hmjson));
        $hmjson = str_replace("'","\"",trim($hmjson));
     

        $hmjson=str_replace("\n","",$hmjson);
        $hmjson = str_replace("\x0B", '', $hmjson);
        $hmjson = str_replace('	', '', $hmjson);	
        $hmjson = str_replace("\r", '', $hmjson);
        $hmjson=str_replace("},}","}}",$hmjson);



        $hm  = (json_decode($hmjson));
     
       if(!isset($hm->alternate)){
       
          $this->error = fetchError::$skubulunamadi;
          return false;
 
       }
       preg_match_all('#\@type"\:\"ListItem\".*?name"\:"(.*?)".*?\}#si',$f,$m2);
       $m2=$m2[1];
       unset($m2[0]);
       // Re-index the array elements
       $m2 = array_values($m2);
       unset($m2[count($m2)-1]);
       $m2 = array_values($m2);
       
       $good->Category = $m2[count($m2)-1];
       $good->CategoryTree = join(">",$m2);
        //$good->GoodId=$goodid[1]; 
        $good->Title=   (trim(current(explode("- {",$hm->alternate))));
        //$good->REFERENCE=   (trim(current(explode("_",$hm->productKey))));
        $good->REFERENCE=null;
        $good->Brand="HM";
        $good->Description= $hm->{$hm->articleCode}->description;
        $good->GoodId=  (trim(current(explode("_",$hm->productKey))));
        $good->SKU= $hm->articleCode;
   
      
      
      ///// varyantlar
     

   
       
  
    

        if(veri::isStockIdExists($good->GoodId,get_class($this)) && $this->mode= modes::$create){  
     
          $this->error= fetchError::$stokzatenvar;    
          return false;
      }  

       
      //colorss
     // if($size->backSoon!=0)continue;
      //    if(!$good->Price){ $this->setPrice($size->price);}
    
  

      foreach ($hm as $key => $color) {
     
        if(is_numeric($key)){
           if(isset($hm->{$key}->images)){
              if(!$good->Price){ $this->setPrice($color->whitePriceValue);}
              $images=$this->resimleriGetir($color->images);
              foreach($color->sizes as $size){

                $variant= new variant($size->name,$color->name);
                $variant->VariantId = $good->GoodId.$size->sizeCode;
                $variant->SKU=$size->sizeCode;
                $variant->VaryantReference=null;
                $variant->Stock=100;
                $good->Stock+=$variant->Stock;
                $this->setPriceVariant( $variant,$color->whitePriceValue);

                foreach ($images as $image) {
                  $variant->addImage($image);
                }
                if(!variantimages::isExists($variant->VariantId,get_class($this))){
                  $variantImages= new variantimages($variant->VariantId,json_encode($variant->Images["Src"]),get_class($this));
                  $variantImages->insert();
                }


                $good->addVariant($variant);
              }
           }
        }
      }
       

       

       return $good;
    }


function isVariantImageExists($variant,$imageurl){
 
  if(in_array($imageurl,$variant->Images["Src"])){
    return true;
  }
   
  return false;
}
public function resimleriGetir($images)
{
  $resimler=[];
  foreach ($images as $image) {
    $resimler[]="https://".$image->fullscreen;
  }
  return $resimler;
}
 




}
?>