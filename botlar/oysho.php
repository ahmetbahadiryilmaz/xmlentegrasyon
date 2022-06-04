<?
 
class oysho{
     public $good ;
     public $error="";
     
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


     public   function goodGetir($url){

       $curl=new curl();
       $good=$this->good;
       $url= str_replace("tr/en/","tr/",$url);
       $url= str_replace('/tr/','/tr/en/',$url);
   
       $getPage= $curl->getPage($url);    
       $getPage= str_replace('&q;','"',$getPage);    
         
       echo $curl->getHttpcode()."<br>";
       if($curl->getHttpcode()=="404" || $curl->getHttpcode()=="410"  ){
        $this->error = fetchError::$sayfabulunamadi;
        return false;
       }
      
        $f=  f::clean(($getPage));
        $goodobj= str_get_html( $f);
        
        
        
      
        //preg_match('#"sku"\:\s"(.*?)",#',$goodobj,$k);
        /*
        if(count($k)<1){
          return false;
        }
        */



        preg_match('#'.preg_quote('"seoConfigData":{"storeId":{"').'(.*?)\"\:#',$goodobj,$kx);
       
        preg_match('#'. 'catalogs.*?\:.*?\:'.'(.*?)'.'\,#',$goodobj,$kx2);
       // preg_match('#inditex.iCategoryId\s\=\s(.*?);#',$goodobj,$kx3);
        preg_match('#iProductId\:\s(.*?),#',$goodobj,$kx4);
       
        $storeId=$kx["1"];
        $catalogId=$kx2["1"];
    
        //$categoriId=$kx3["1"];
        $productId=$kx4["1"];
    
        $produrl="https://www.oysho.com/itxrest/2/catalog/store/"       .$storeId. "/".$catalogId."/category/0/product/$productId/detail?appId=2&languageId=-43";
        $prod = json_decode($curl->getPage($produrl));
        //print_r($prod);
        //exit();
     

        //$good->GoodId=$goodid[1]; 
        $good->Title=   $prod->name;
        $ref=$prod->detail->reference;
      
        $good->REFERENCE= $ref;
         
        $good->Brand="Oysho";

       /* 
       $images= $goodobj->find('.image-item');
    
       foreach($images as $img){
         $good->addImage($img->{"data-original"});
       }*/
  
     
      
        $descs="";
        foreach ($prod->attributes as $attr) {
          if($attr->type=="DESCRIPTION"){
            $descs.= $attr->value."<br>";
          }

        } 
        $descs.=$prod->detail->description;
        $descs.="<br>".$prod->detail->longDescription;
        $good->Description=$descs;
        $goodid=explode("-",$prod->detail->reference);
      ///// varyantlar
     

   
       
  
       
       $good->GoodId=$goodid[0];
 
          if(veri::isStockIdExists($good->GoodId,get_class($this)) && $this->mode= modes::$create){  
            $this->error = fetchError::$stokzatenvar;
            return false;
        } 

        $good->SKU=$prod->id;
        $colors= $prod->detail->colors;
        if(!$colors){
          $this->error = fetchError::$colorsbulunamadi;
          return false;
        }
        //echo $prod->familyName;
   
        if(isset($prod->detail->subfamilyInfo->subFamilyName)){
          $good->Category=  $prod->detail->subfamilyInfo->subFamilyName;
        }elseif(isset($prod->detail->familyInfo->familyName)){
          $good->Category=  $prod->detail->familyInfo->familyName;
        }else{
          //exit($produrl);
          //return false;
        }
          //$prod->bundleProductSummaries[0]->detail->subfamilyInfo->subFamilyName;
       
         $imgurlprefix="https://static.oysho.net/6/photos2";
         
     
        foreach($colors as $color){         
            foreach($color->sizes as $size){
              if($size->backSoon!=0)continue;
              if(!$good->Price){ $this->setPrice($size->price);}

              $variant= new variant($size->name,$color->name);
              $variant->SKU=$size->sku;
              $sizep = explode("-",$size->partnumber);
              $variant->VariantId=$sizep[0];
              $variant->VaryantReference=$ref;
              $size->price= $size->price/100;
              $variant->Price=$size->price;
              $variant->SellingPrice=$size->price;
              $variant->TotalArrivalPrice=$size->price;
              $variant->SellingPrice=$size->price;
             // $variant->Stock=$size->isBuyable?100:0;  
          
              $variant->Stock=100;//$size->visibilityValue=="SHOW"?100:0;            
              $good->Stock+=$variant->Stock;
              $image= $color->image;
             

              /////resimler
           
        
              if(variantimages::isExists($variant->VariantId, get_class($this)) ){
                $variantImages  = variantimages::fetch($variant->VariantId);
                $variantImages =   json_decode($variantImages->images);
                foreach($variantImages as $image){
                  $variant->addImage($image);
                }
               
               
              }else{             

            
             $resimler= $this->resimGetir2( $prod ,$color->id);
             for ($i=0; $i <count($resimler)-1 ; $i++) { 
               $resim=$resimler[$i];
               $variant->addImage($resim);
                
             }
            
             $variantImages= new variantimages($variant->VariantId,json_encode($variant->Images["Src"]),get_class($this));
             $variantImages->insert();
          
            }

             $good->addVariant($variant);
        }  
      }
 

     //////
     
 
        # code...

       return $good;
    }


    function resimGetir2($prod,$colorcode){
      $imgurlprefix="https://static.oysho.net/6/photos2";
      $resimler=[];
      foreach ($prod->detail->xmedia as $media ){
        if($media->colorCode==$colorcode){
            for ($imedialt=0; $imedialt < count($media->xmediaItems); $imedialt++) { 
              foreach ($media->xmediaItems[$imedialt]->medias as $mediaalt){
                $md5hash=isset($mediaalt->extraInfo->hash[0]->md5Hash)?$mediaalt->extraInfo->hash[0]->md5Hash."-":"";
                $imageurl=$imgurlprefix.$media->path."/".$md5hash.$mediaalt->idMedia."0.jpg";
                if(!in_array($imageurl,$resimler)){
                  if(f::get_http_response_code($imageurl)!="404"){
                    $resimler[]=$imageurl;
                  }
                }
              };//medialtson
          }//forimedialtson
        };
     }
     return $resimler;

    }




  function isVariantImageExists($variant,$imageurl){
    if(in_array($imageurl,$variant->Images["Src"])){
      return true;
    }
    return false;
  }
  




}
?>