<?php

/**
* Represents a single header image.
*/
class webksPageSlider_Headerimage {
  public $imgPath;
  public $imgActive;
  public $imgTitle;
  public $imgText;
  public $imgLinkUrl;
  public $imgPosition;
  public $imgAlt;
  public $imgNote;

  /**
  * Constructor.
  *
  * @param string $imgPath     [description]
  * @param boolean $imgActive   [description]
  * @param string $imgTitle    [description]
  * @param string $imgText     [description]
  * @param string $imgLinkUrl      [description]
  * @param string $imgPosition [description]
  * @param string $imgAlt      [description]
  * @param string $imgNote     [description]
  */
  public function __construct($imgPath, $imgActive = 0, $imgTitle = '', $imgText = '', $imgLinkUrl = '', $imgPosition = '', $imgAlt = '', $imgNote = ''){
    $this->imgPath = $imgPath;
    $this->imgActive = $imgActive;
    $this->imgTitle = $imgTitle;
    $this->imgText = $imgText;
    $this->imgLinkUrl = $imgLinkUrl;
    $this->imgPosition = $imgPosition;
    $this->imgAlt = $imgAlt;
    $this->imgNote = $imgNote;
  }

  /**
  * Returns the unique index of the image to used to identify and compare it.
  *
  * @return string
  */
  public function getIdx(){
    return htmlspecialchars($this->imgPath, ENT_QUOTES);
  }

  /**
  * Returns true if the image is active (in the given page context).
  *
  * @return boolean
  */
  public function isActive(){
    return !empty($this->imgActive);
  }

  /**
  * Creates a new Headerimage object from the given array.
  *
  * @param  array  $headerimagesArray [description]
  * @return webksPageSlider_Headerimage
  */
  public static function createFromArray(array $headerimagesArray){
    return new self($headerimagesArray['imgPath'], $headerimagesArray['imgActive'], $headerimagesArray['imgTitle'], $headerimagesArray['imgText'], $headerimagesArray['imgLinkUrl'], $headerimagesArray['imgPosition'], $headerimagesArray['imgAlt'], $headerimagesArray['imgNote']);
  }

  /**
  * Returns an array representation of the Headerimage (which can be saved).
  *
  * @return array
  */
  public function toArray(){
    return array(
      'imgPath' => trim($this->imgPath),
      'imgActive' => trim($this->imgActive),
      'imgTitle' => trim($this->imgTitle),
      'imgText' => trim($this->imgText),
      'imgLinkUrl' => trim($this->imgLinkUrl),
      'imgPosition' => trim($this->imgPosition),
      'imgAlt' => trim($this->imgAlt),
      'imgNote' => trim($this->imgNote),
    );
  }

  /**
  * Returns the string representation in JSON.
  *
  * @return string
  */
  public function __toString(){
    return json_encode($this->toArray());
  }
}
