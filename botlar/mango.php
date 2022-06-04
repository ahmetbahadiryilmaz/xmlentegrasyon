<?
 
 

class mango{
     public $good ;
     public $error="";
     public $classname="mango";
     public $stockid="";
     
     public function __construct( ) {
        $this->good=new good();
     }

     public static function getProductUrls(string $kategorihtml){
        $prodlar=[];
        preg_match_all("#linkTo\"\:\"(.*?)\"#si",$kategorihtml,$pp);
        $pp= ($pp[1]);
        foreach ($pp as $p) {
            $prodlar[]= "https://shop.mango.com/".$p;
        }
        return $prodlar;
     }

    public  function getSubstringBetweenTwoSubstrings($string, $start, $end)
    {
         $substringStart = strpos($string, $start);
         $substringStart += strlen($start);
         $size = strpos($string, $end, $substringStart) - $substringStart;
        return substr($string, $substringStart, $size);
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
      $curl->cache=false;
       $curl->setCacheFolder(__DIR__."/../cache/".$this->classname."/");
       $good=$this->good;   
       $getPage= $curl->getPage($url);  
       $productJson = $this->getSubstringBetweenTwoSubstrings($getPage, 'var dataLayerV2Json = ', 'var dataLayer');
       $productJson = rtrim(rtrim($productJson), ';');
       $productData = json_decode($productJson, true);
       $prodId=$productData["ecommerce"]["detail"]["products"]["0"]["id"];
       $prodcolor=$productData["ecommerce"]["detail"]["products"]["0"]["colorId"];
       
       $mangostockid =  $curl->getPage("https://shop.mango.com/services/stock-id");
       $mangostockid= json_decode($mangostockid);
       $mangostockid= $mangostockid->key;
       
       $this->stockid=$mangostockid;
       
      
       $curl->addReqHeader("Referer:".$url);
      
       $curl->addReqHeader("stock-id:".$this->stockid);
  
       $getPage= $curl->getPage("https://shop.mango.com/services/garments/" .$prodId.$prodcolor); 
     
       $proobj= json_decode($productJson) ;
       $getpageobj= json_decode($getPage) ;
       $desc= join(" ",$getpageobj->details->descriptions->bullets);
       $desc.= "<br>\n";
       if(isset($getpageobj->details->descriptions->measures)){
         $desc .= join( "<br>\n",$getpageobj->details->descriptions->measures);
       }
    
       $good->GoodId=$getpageobj->id;
       $good->SKU=$getpageobj->id;
       $good->Brand="mango";
       $good->Category= $proobj->ecommerce->detail->products[0]->category;
       $good->Description=$desc;//$proobj->ecommerce->detail->products[0]->description;
       $this->setPrice($getpageobj->colors->colors[0]->price->price);
       $good->Title =$getpageobj->name;
       $dizi = [];
       foreach($proobj->ecommerce->detail->products[0]->categories as $cat)
       {
          array_push($dizi,$cat->name);
       }
       $good->CategoryTree =implode(" > ", $dizi);
       
      foreach($getpageobj->colors->colors as $key=>$val)
      {
        $prodgarmentid= $this->good->GoodId.$val->id;
        if(!isset($val->sizes)){
    
          $curl->cache=false;
           
          $curl->emptyReqHeader();
          $curl->addReqHeader("Referer:".$url);
          
          $prodgarmentid= $val->productDataLayer->ecommerce->detail->products[0]->id.$val->id;
          $mangostockid =  $curl->getPage("https://shop.mango.com/services/stock-id");
          $mangostockid= json_decode($mangostockid);
          $mangostockid= $mangostockid->key;
          $curl->emptyReqHeader();
          $curl->addReqHeader("stock-id:".$mangostockid);      
          $getPage= $curl->getPage("https://shop.mango.com/services/garments/" . $prodgarmentid);
          $getpageobj=json_decode($getPage);
          $val=$getpageobj->colors->colors[$key];
          $curl->cache=true;
       
        
        }
         
          foreach($val->sizes as $size)
          {
   
            if($size->id !=-1)
            {
              $variant = new variant($size->value,$val->label);
              $variant->VariantId=$prodgarmentid.$size->id;
              $variant->SKU=$prodgarmentid.$size->id;
              foreach($val->images as $image)
              {
                foreach($image as $img)
                {
                  $variant->addImage("https://st.mngbcn.com/rcs/pics/static".$img->url."");
                }
              }
              $this->setPriceVariant($variant,$val->price->price);
          
              $variant->VaryantReference=null;
              $variant->Stock=100;
              
              $good->Stock+=$variant->Stock;
              $good->addVariant($variant);
            }
          }
      }

 
 

       echo "<br>";
      
       $curl->cache=true;
 
       if (!$productData) {
           throw new \RuntimeException('Could not parse');
       }

       if($curl->getHttpcode()=="404" ||$curl->getHttpcode()=="410"  ){
          $this->error = fetchError::$sayfabulunamadi;
          return false;
       } 
         
       
        $f=  f::clean(($getPage));
       
 
    
 
      
      ///// varyantlar
     

   
       
  
    

      if(veri::isStockIdExists($good->GoodId,get_class($this))){  
     
          $this->error= fetchError::$stokzatenvar;    
          return false;
      }  

       
      //colorss
     // if($size->backSoon!=0)continue;
      //    if(!$good->Price){ $this->setPrice($size->price);}
       
       

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