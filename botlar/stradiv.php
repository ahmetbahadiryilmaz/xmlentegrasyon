<?
 
 

class stradiv{
     public $good ;
     public $error="";
     
     public function __construct( ) {
        $this->good=new good();
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
      
         
       echo $curl->getHttpcode()."<br>";
       if($curl->getHttpcode()=="404" || $curl->getHttpcode()=="410"  ){
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



        preg_match('#inditex.iStoreId\s\=\s(.*?);#',$goodobj,$kx);
        preg_match('#inditex.iCatalogId\s\=\s(.*?);#',$goodobj,$kx2);
        preg_match('#inditex.iCategoryId\s\=\s(.*?);#',$goodobj,$kx3);
        preg_match('#inditex.iProductId\s\=\s(.*?);#',$goodobj,$kx4);
        $storeId=$kx["1"];
        $catalogId=$kx2["1"];
    
        //$categoriId=$kx3["1"];
        $productId=$kx4["1"];
    
        $imgurlprefix="https://static.e-stradivarius.net/5/photos3";
        $produrl="https://www.stradivarius.com/itxrest/2/catalog/store/"       .$storeId. "/".$catalogId."/category/0/product/$productId/detail?appId=1&languageId=-43";
        $prod = json_decode($curl->getPage($produrl));
      
     

        //$good->GoodId=$goodid[1]; 
        $good->Title=   $prod->name;
        $ref=$prod->detail->reference;
        $good->REFERENCE= $ref;
        $good->Brand="Stradiv";

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
 
        if(veri::isStockIdExists($good->GoodId,get_class($this))){  
            echo "stock exists<br>";
            return false;
        } 

        $good->SKU=$prod->id;
        $colors= $prod->detail->colors;
        if(!$colors){
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
       
         if($prod->image!=null) exit();
    
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
           
               
              if(/*false){ //*/variantimages::isExists($variant->VariantId,get_class($this))){
                $variantImages  = variantimages::fetch($variant->VariantId);
                $variantImages =   json_decode($variantImages->images);
                foreach($variantImages as $image){
                  $variant->addImage( $image );
                }
              
               
              }else{
                $resimler= $this->resimGetir( $prod,$color->id);
                /*
              foreach ($prod->detail->xmedia as $media ){ 
   
                if($media->colorCode==$color->id){
                  for ($imedialt=0; $imedialt < count($media->xmediaItems); $imedialt++) { 
                    foreach ($media->xmediaItems[$imedialt]->medias as $mediaalt){                        
                      $md5hash=isset($mediaalt->extraInfo->hash[0]->md5Hash)?$mediaalt->extraInfo->hash[0]->md5Hash."-":"";
                      $imageurl=$imgurlprefix.$media->path."/".$md5hash.$mediaalt->idMedia."2.jpg";
                      
                      
                      if(!$this->isVariantImageExists($variant,$imageurl)){
                        $httpcode=  curl::getHttpCodeOnly($imageurl);
                        if($httpcode=="200")
                        {
                                $variant->addImage( $imageurl);
                              }
                          }
                      };//medialtson
                  }//forimedialtson
                };
             }
             */
           
              foreach($resimler as $resim){
                    $variant->addImage($resim);
                    //echo "<a href='$resim'>$resim</a><br>";
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


function isVariantImageExists($variant,$imageurl){
 
  if(in_array($imageurl,$variant->Images["Src"])){
    return true;
  }
   
  return false;
}
 


function resimGetir($detay,$colorcode){
  $resimler=[];
  $imgurlprefix="https://static.e-stradivarius.net/5/photos3";
 if ($detay->detail->xmedia){
   
     foreach ($detay->detail->xmedia as $detaya) {
         if($detaya->colorCode!=$colorcode) continue;
         $patch=$detaya->path; 
     
         foreach ($detaya->xmediaLocations[0]->locations[0] as $detayx) { 
             if($detayx) {
                 foreach ($detayx as $value){
                     $hash =  $this->araMedia($detaya->xmediaItems,$value);
                     
                     $resimler[]=  $imgurlprefix.$patch."/".($hash?($hash."-"):"").$value."1.jpg";
                      
                 
             }
         }
     }
    }
 }else {
     foreach ($detay->bundleProductSummaries as $detaya) {
         foreach ($detaya->detail->xmedia as $detayx) {
             $patch=$detayx->path;
             if($detayx) {
                if($detayx->colorCode!=$colorcode) continue;
                 foreach ($detayx->xmediaLocations as $values){
                     foreach ($values->locations[0]->mediaLocations as $value){
                         $hash = $this-> araMediam($detayx->xmediaItems,$value);
                             if($hash!=""){
                               $resimler[]=  $imgurlprefix.$patch."/".$hash."-".$value."1.jpg" ;
                             }
                     }
                    
                 }
             }

         }
     }

 }

return $resimler;
}






function araMedia($mediaItems,$idMedia) {

foreach ($mediaItems as $detayx) {

   foreach ($detayx->medias as $eleman) {

       if($eleman->idMedia==$idMedia) {

           if(isset($eleman->extraInfo->hash[0]->md5Hash)) {
               return $eleman->extraInfo->hash[0]->md5Hash;
           }else{
               continue;
           }
           
       }

   }


}

}


function araMediam($mediaItems,$idMedia) {

foreach ($mediaItems as $detayx) {
 

   foreach ($detayx->medias as $eleman) {

       if($eleman->idMedia==$idMedia) {

           if(isset($eleman->extraInfo->hash[0]->md5Hash)) {
               return $eleman->extraInfo->hash[0]->md5Hash;
           }else{
               continue;
           }
           
       }

   }
}



}



}
?>