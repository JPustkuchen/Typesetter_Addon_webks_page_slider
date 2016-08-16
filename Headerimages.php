<?php

require_once('Headerimage.php');

/**
* Represents a set of header images (typically for a page).
*/
class webksPageSlider_Headerimages {

  /**
  * Header images array.
  * @var array
  */
  protected $headerimages;

  /**
  * Constrcutor.
  *
  * @param array $headerimages [description]
  */
  public function __construct(array $headerimages = array()){
    $this->setHeaderimages($headerimages);
  }

  public static function createFromArray(array $headerimagesArray, $onlyIfActive = false){
    $headerimages = new self();
    if(!empty($headerimagesArray)){
      foreach($headerimagesArray as $headerimageArray){
        $headerimages->addHeaderimage(webksPageSlider_Headerimage::createFromArray($headerimageArray), $onlyIfActive);
      }
    }
    return $headerimages;
  }

  /**
  * Sets the list of header images (objects of type webksPageSlider_Headerimage)
  *
  * @param array $headerimages [description]
  *@return webksPageSlider_Headerimages $this
  */
  public function setHeaderimages(array $headerimages = array()){
    if(!empty($headerimages)){
      foreach($headerimages as $headerimage){
        $this->addHeaderimage();
      }
    }
    return $this;
  }

  /**
  *
  * Merges the given $headerimagesObj header images into this.
  * Overrides entries already existing with the same index kex.
  *
  * @param  webksPageSlider_Headerimages $headerimagesObj [description]
  * @return [type]                                          [description]
  */
  public function merge(webksPageSlider_Headerimages $headerimagesObj){
    $headerimageObjArray = $headerimagesObj->getHeaderImages();
    if(!empty($headerimageObjArray)){
      foreach($headerimageObjArray as $headerimageObj){
        $this->addHeaderimage($headerimageObj);
      }
    }
    return $this;
  }

  public function hasIdx($idx){
    $headerimageObjArray = $this->getHeaderImages();
    return !empty($headerimageObjArray[$idx]);
  }

  /**
  * Returns a webksPageSlider_Headerimage object by its index.
  *
  * @param  string $idx
  * @return webksPageSlider_Headerimage
  */
  public function getByIdx($idx){
    $headerimageObjArray = $this->getHeaderImages();
    if(!empty($headerimageObjArray[$idx])){
      return $headerimageObjArray[$idx];
    }
  }

  /**
  * Adds a header image to the list of header images.
  *
  * @param webksPageSlider_Headerimage $headerimage [description]
  *
  * @return webksPageSlider_Headerimages $this
  */
  public function addHeaderimage(webksPageSlider_Headerimage $headerimage, $onlyIfActive = false){
    if(!$onlyIfActive || ($onlyIfActive && $headerimage->isActive())){
      $this->headerimages[$headerimage->getIdx()] = $headerimage;
    }
    return $this;
  }

  /**
  * Remove the given header image from the list (by its index).
  *
  * @param  webksPageSlider_Headerimage $headerimage
  */
  public function removeHeaderimage(webksPageSlider_Headerimage $headerimage){
    unset($this->headerimages[$headerimage->getIdx()]);
    return $this;
  }

  /**
  *
  *
  * @return array
  */
  public function getHeaderImages(){
    return $this->headerimages;
  }

  /**
  * Returns the list of headerimgages as full array.
  * All headerimages are also converted to their array representation
  * (despite of getHeaderimages()) which returns them as objects.
  *
  * @return array
  */
  public function toArray(){
    $result = array();
    $headerimages = $this->getHeaderImages();
    if(!empty($headerimages)){
      foreach($headerimages as $headerimageObj){
        $result[] = $headerimageObj->toArray();
      }
    }
    return $result;
  }

  /**
  * Returns the string JSON representation.
  *
  * @return string
  */
  public function __toString(){
    return json_encode($this->toArray());
  }
}
