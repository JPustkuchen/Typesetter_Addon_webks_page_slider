<?php

defined('is_running') or die('Not an entry point...');
require_once('Headerimage.php');
require_once('Headerimages.php');

/**
* Contains all library functions for the Headerimage functionality.
*/
class webksPageSlider_Library {
  /**
  * The relative path to the modules config file
  * @var string
  */
  static $configFilePath = 'config.php';

  /**
  * The config master area array key.
  * @var string
  */
  static $configAreaKey = 'config';

  /**
  * The array key of the pages within the configuration array.
  * @var string
  */
  static $configPagesKey = 'pages';

  /**
  * The array key of the images folder path within the configuration array.
  * @var string
  */
  static $configImagesDirPathKey = 'path';

  public static function getImagesPathsFromDirectory($dirPath){
    global $dataDir;
    $images = array();

    // Remove preceding slash
    $dirPath = ltrim($dirPath, '/');
    $dirPath = '/data/_uploaded/' . $dirPath;
    $files = scandir($dataDir . $dirPath);

    includeFile('admin/admin_uploaded.php');
    foreach ($files as $filename) {
      if ($filename == '.' || $filename == '..') {
        continue;
      }

      $imgPath = $dirPath . '/' . $filename;
      if (admin_uploaded::isImg($imgPath)) {
        $images[$filename] = $imgPath;
      }
    }
    return $images;
  }

  public static function getCurrentPageIdxCleaned(){
    global $page;

    $index = trim($page->gp_index);
    if(empty($index)){
      throw new webksPageSlider_NoIndexException('Current page has no index!');
    }

    return \gp\tool::UrlChars($index);
  }

  public static function loadHeaderImagesConfig(){
    $config = gpFiles::Get(self::getHeaderImagesConfigFilePathAbsolute(), self::$configAreaKey);
    if(empty($config)){
      $config = array(
        // Defaults:
        self::$configPagesKey => array(),
        self::$configImagesDirPathKey => '/image/slides',
      );
    }
    return $config;
  }

  public static function getHeaderImagesConfigFilePathAbsolute(){
    global $addonPathData;
    return $addonPathData. '/' . self::$configFilePath;
  }

  public static function getHeaderImagesPageConfig($pageIdx){
    $configArray = self::loadHeaderImagesConfig();
    if(!empty($configArray[self::$configPagesKey][$pageIdx])){
      return $configArray[self::$configPagesKey][$pageIdx];
    } else {
      return array();
    }
  }

  public static function savePageHeaderImagesToConfig($pageIdx, webksPageSlider_Headerimages $pageHeaderimages){
    // Retrieve the latest full config
    $configArray = self::loadHeaderImagesConfig();
    // Override valus for the current page:
    $configArray[self::$configPagesKey][$pageIdx] = $pageHeaderimages->toArray();
    self::saveConfig($configArray);
  }

  public static function removePageHeaderImagesFromConfig($pageIdx){
    // Retrieve the latest full config:
    $configArray = self::loadHeaderImagesConfig();
    // Override valus for the current page:
    if(isset($configArray[self::$configPagesKey][$pageIdx])){
      unset($configArray[self::$configPagesKey][$pageIdx]);
      self::saveConfig($configArray);
      return true;
    } else {
      return false;
    }
  }

  /**
  * Returns the relative path to the header images directory as configured.
  * @return string
  */
  public static function getHeaderImagesDirPath(){
    $configArray = self::loadHeaderImagesConfig();
    return $configArray[self::$configImagesDirPathKey];
  }

  protected static function saveHeaderImagesDirPathToConfig($path){
    // Retrieve the latest full config:
    $configArray = self::loadHeaderImagesConfig();
    // Set new path:
    $configArray[self::$configImagesDirPathKey] = $path;
    // Save:
    self::saveConfig($configArray);
  }

  public static function saveConfig(array $configArray){
    if(!isset($configArray[self::$configPagesKey])){
      msg('Config will not be saved. Important key: "'. self::$configPagesKey . '" missing.');
    } elseif(!isset($configArray[self::$configImagesDirPathKey])){
      msg('Config will not be saved. Important key: "'. self::$configImagesDirPathKey . '" missing.');
    }
    if( !gpFiles::SaveData(self::getHeaderImagesConfigFilePathAbsolute(), self::$configAreaKey, $configArray) ){
      msg($langmessage['OOPS']);
    }
  }

  /**
  * Returns all selected header images for all pages.
  *
  */
  public static function getAllPageHeaderimages(){
    $configArray = self::loadHeaderImagesConfig();
    $allConfigHeaderimagePages = $configArray[self::$configPagesKey];
    $pagesArray = array();

    if(!empty($allConfigHeaderimagePages)){
      foreach($allConfigHeaderimagePages as $pageIdx => $pageConfigHeaderimages) {
        $pageHeaderimagesObj = new webksPageSlider_Headerimages();
        foreach($pageConfigHeaderimages as $headerimageArray){
          $headerimageObj = webksPageSlider_Headerimage::createFromArray($headerimageArray, true);
          $pageHeaderimagesObj->addHeaderimage($headerimageObj);
        }
        $pagesArray[$pageIdx] = clone($pageHeaderimagesObj);
      }
    }

    // Retrieve as object:
    return $pagesArray;
  }

  /**
  * Returns the selected header images only for the given page.
  *
  * @param  [type] $pageIdx [description]
  * @return [type]          [description]
  */
  public static function getPageHeaderimages($pageIdx){
    // Get the header images for the given page:
    $headerImagesPageConfig = self::getHeaderImagesPageConfig($pageIdx);
    // Retrieve as object:
    return webksPageSlider_Headerimages::createFromArray($headerImagesPageConfig, true);
  }

  /**
  * Returns the available header images as webksPageSlider_Headerimages object.
  *
  * @return webksPageSlider_Headerimages
  */
  public static function getAvailableHeaderimages(){
    $headerimagesObj = new webksPageSlider_Headerimages();
    $headerImages = self::getImagesPathsFromDirectory(self::getHeaderImagesDirPath());
    if(!empty($headerImages)){
      foreach($headerImages as $headerImageFilepath => $headerImageFilename){
        $headerimageObj = new webksPageSlider_Headerimage($headerImageFilename);
        $headerimagesObj->addHeaderimage($headerimageObj);
      }
    }
    return $headerimagesObj;
  }

  /**
  * Returns the header images with their information for the given page.
  *
  * @param  string $pageIdx
  * @return webksPageSlider_Headerimages
  */
  public function getHeaderimagesOptionlist($pageIdx){
    // Get all available images from the folder
    $headerimagesOptionList = self::getAvailableHeaderimages();
    // Get all information for the already selected images.
    $pageHeaderimages = self::getPageHeaderimages($pageIdx);
    // Merge both.
    $headerimagesOptionList->merge($pageHeaderimages);

    return $headerimagesOptionList;
  }

  /**
  * Extracts the selected / set header image options from the given form $post array.
  * Returns the webksPageSlider_Headerimages object to save.
  *
  * @param  string $pageIdx
  * @param  array $post    The $_POST array.
  */
  public function getHeaderimagesOptionlistPost($pageIdx, $post){
    // Get available options
    $headerimagesOptionList = self::getHeaderimagesOptionlist($pageIdx);
    $result = new webksPageSlider_Headerimages();
    if(!empty($post['values']) && is_array($post['values'])){
      foreach($post['values'] as $idx => $value){
        if($headerimagesOptionList->hasIdx($idx) && !empty($value['imgActive'])){
          $headerimageObjLoaded = $headerimagesOptionList->getByIdx($idx);
          $headerimageObjModified = $headerimageObjLoaded;

          $headerimageObjModified->imgActive = 1;
          $headerimageObjModified->imgTitle = $value['imgTitle'];
          $headerimageObjModified->imgText = $value['imgText'];
          $headerimageObjModified->imgLinkUrl = $value['imgLinkUrl'];
          $headerimageObjModified->imgPosition = $value['imgPosition'];
          $headerimageObjModified->imgAlt = $value['imgAlt'];
          $headerimageObjModified->imgNote = $value['imgNote'];

          $result->addHeaderimage($headerimageObjModified);
        }
      }
    }
    return $result;
  }
}

class webksPageSlider_NoIndexException extends Exception {

}
