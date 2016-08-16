<?php

defined('is_running') or die('Not an entry point...');

class webksPageSlider {

  var $files; //files in selected images directory
  var $config; //image directory, background style, list of page backgrounds for each page
  var $config_file;

  function __construct() {
    if (common::LoggedIn()) {
      gpPlugin::css('switchbg.css');
    }
  }

  /**
  * PageRunScript Hook
  *
  */
  public function PageRunScript($cmd) {
    global $page, $langmessage;
    if (common::LoggedIn()) {
      $page->admin_links[] = array($page->title, 'Seiten-Slides ausw&auml;hlen', 'cmd=pageSlidesSelectDialog', ' data-cmd="gpabox"');
      switch ($cmd) {
        // on current page
        case 'pageSlidesSelectDialog':
        ob_start();
        $this->pageSlidesSelectDialog();
        $page->contentBuffer = ob_get_clean();
        return 'return';

        case 'setPageSlides':
        $this->setPageSlides();
        return '';
      }
    }
    return $cmd;
  }

  function PrintPageSlides(){
    require_once('Library.php');
    try {
      $pageIdx = webksPageSlider_Library::getCurrentPageIdxCleaned();
    } catch(webksPageSlider_NoIndexException $e){
      // No page index found. Output nothing.
      return;
    }
    $availableHeaderimagesObj = webksPageSlider_Library::getPageHeaderimages($pageIdx);
    $availableHeaderimageObjArray = $availableHeaderimagesObj->getHeaderImages();

    $output = '';
    if(!empty($availableHeaderimageObjArray)){
      $output .= '<div class="webks-page-slider-slides">';
      $output .= '<ul>';
      $count = count($availableHeaderimageObjArray);
      $i = 0;
      foreach($availableHeaderimageObjArray as $availableHeaderimageObj){
        // Build classes
        $classes = array('slide-item');
        if($i == 0){
          $classes[] = 'first';
        } else if ($i == $count -1){
          $classes[] = 'last';
        }
        if($i % 2 == 0){
          $classes[] = 'even';
        } else {
          $classes[] = 'odd';
        }
        // List item START:
        $output .= '<li class="' . implode(' ', $classes) . '">';

        // List item content START
        // IMAGE
        $output .= '<img src="' . htmlspecialchars($availableHeaderimageObj->imgPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($availableHeaderimageObj->imgAlt, ENT_QUOTES) . '" title="' . htmlspecialchars($availableHeaderimageObj->imgTitle, ENT_QUOTES) . '" class="slide-image" />';

        // BOX
        if(!empty($availableHeaderimageObj->imgTitle) || !empty($availableHeaderimageObj->imgText)){
          // Position
          $output.= '<div class="slide-item-box position-' . (!empty($availableHeaderimageObj->imgPosition) ? htmlspecialchars($availableHeaderimageObj->imgPosition, ENT_QUOTES) : 'left') . '">';
          // Link
          if(!empty($availableHeaderimageObj->imgLinkUrl)){
            $output .= '<a href="' . htmlspecialchars($availableHeaderimageObj->imgLinkUrl, ENT_QUOTES) . '" title="' . htmlspecialchars($availableHeaderimageObj->imgTitle, ENT_QUOTES) . '">';
          }
          // Title
          if(!empty($availableHeaderimageObj->imgTitle)){
            $output .= '<div class="title">' . htmlspecialchars($availableHeaderimageObj->imgTitle, ENT_QUOTES) . '</div>';
          }
          // Link END
          if(!empty($availableHeaderimageObj->imgLinkUrl)){
            $output .= '</a>';
          }
          // Text
          if(!empty($availableHeaderimageObj->imgText)){
            $output .= '<div class="text">' . nl2br(htmlspecialchars($availableHeaderimageObj->imgText, ENT_QUOTES)) . '</div>';
          }
          $output.= '</div>';
        }
        // // List item content END

        // List item END
        $output .= '</li>';
        $i++;
      }
      $output .= '</ul>';
      $output .= '</div>';
    }
    echo $output;
  }

  /**
  * Display popup for selecting dialog
  *
  */
  function pageSlidesSelectDialog() {
    global $langmessage, $dataDir;
    if (!common::LoggedIn()) {
      return FALSE;
    }

    require_once('Library.php');
    try {
      $pageIdx = webksPageSlider_Library::getCurrentPageIdxCleaned();
    } catch(webksPageSlider_NoIndexException $e){
      // No page index found. Output nothing.
      return;
    }
    $availableHeaderimagesObj = webksPageSlider_Library::getHeaderimagesOptionlist($pageIdx);
    $availableHeaderimageObjArray = $availableHeaderimagesObj->getHeaderImages();

    $output = '';
    $output .= '<h2>Slides ausw&auml;hlen</h2>';
    // HILFE-Text
    $output .= '<div class="help">Laden Sie Bilder in der ' . common::Link('Admin_Uploaded', 'Dateiverwaltung') . ' ins Verzeichnis <em>"' . htmlspecialchars(webksPageSlider_Library::getHeaderImagesDirPath(), ENT_QUOTES) . '"</em>, um diese hier als Slides auszuw&auml;hlen.</div>';
    $output .= '<div class="webks-page-slider-slides-select inline_box"><form method="post" action="?">';
    if(!empty($availableHeaderimageObjArray)){
      $count = count($availableHeaderimageObjArray);
      $i = 0;
      $output .= '<table>';
      foreach($availableHeaderimageObjArray as $availableHeaderimageObj){
        $idx = $availableHeaderimageObj->getIdx();
        $output .= '<tr class="row-' . $i . '">';
        $output .= '<td>';
        $output .= '<input type="hidden" name="values[' . $idx . '][imgActive]" value="0" />';
        if(file_exists($dataDir . $availableHeaderimageObj->imgPath)){
          $output .= '<label><input type="checkbox" name="values[' . $idx . '][imgActive]" value="1" ' . ($availableHeaderimageObj->imgActive?'checked="checked"':'') . ' /><img src="' . htmlspecialchars($availableHeaderimageObj->imgPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($availableHeaderimageObj->imgPath, ENT_QUOTES) . '" title="' . htmlspecialchars($availableHeaderimageObj->imgPath, ENT_QUOTES) . '" width="100" /></label>';
        } else {
          $output .= '<div class="image-missing">Das Slider-Bild <em>"' . htmlspecialchars($availableHeaderimageObj->imgPath, ENT_QUOTES) . '"</em> wurde offenbar gel&ouml;scht oder umbenannt.<br />Wenn Sie speichern, werden auch die zugeh&ouml;rigen Daten entfernt.</div>';
        }
        $output .= '</td><td>';
        $output .= '<label>Titel:</label> <input type="text" name="values[' . $idx . '][imgTitle]" value="' . htmlspecialchars($availableHeaderimageObj->imgTitle, ENT_QUOTES) . '" /><br />';
        $output .= '<label>Text:</label> <textarea name="values[' . $idx . '][imgText]" rows="4">' . htmlspecialchars($availableHeaderimageObj->imgText, ENT_QUOTES) . '</textarea><br />';
        $output .= '<label>Link URL:</label> <input type="text" name="values[' . $idx . '][imgLinkUrl]" value="' . htmlspecialchars($availableHeaderimageObj->imgLinkUrl, ENT_QUOTES) . '" placeholder="http://www.example.de" />';
        $output .= '</td><td class="additional">';
        $output .= '<label>Ausrichtung:</label> <select name="values[' . $idx . '][imgPosition]"><option value="left" ' . ($availableHeaderimageObj->imgPosition == 'left'?'selected="selected"':'') . '>Links</option><option value="right" ' . ($availableHeaderimageObj->imgPosition == 'right'?'selected="selected"':'') . '>Rechts</option></select><br />';
        $output .= '<label>alt-Text:</label> <input type="text" name="values[' . $idx . '][imgAlt]" value="' . htmlspecialchars($availableHeaderimageObj->imgAlt, ENT_QUOTES) . '" placeholder="Der alt-Text des Bildes." /><br />';
        $output .= '<label>Notiz:</label> <textarea name="values[' . $idx . '][imgNote]" rows="4" placeholder="Ihre internen Notizen">' . htmlspecialchars($availableHeaderimageObj->imgNote, ENT_QUOTES) . '</textarea>';
        $output .= '</td>';
        $output .= '</tr>';
        $i++;
      }

    } else {
      $output .= '<tr class="empty">';
      $output .= '<td colspan="3">Es wurden noch keine Slider (in einem erlaubten Bildformat) hochgeladen.</td>';
      $output .= '</tr>';
    }
    $output .= '</table>';
    $output .= '<input type="hidden" name="pageIdx" value="' . htmlspecialchars($pageIdx, ENT_QUOTES) . '" />';
    $output .= '<button type="submit" name="cmd" value="setPageSlides" class="gpsubmit">' . $langmessage['save'] . '</button> ';
    $output .= '<button class="gpcancel" data-cmd="admin_box_close">' . $langmessage['cancel'] . '</button> ';
    $output .= '</form></div>';

    echo $output;
  }

  /**
  * Save the posted style and image
  *
  */
  function setPageSlides() {
    global $langmessage;

    if (!common::LoggedIn() || !isset($_POST['values'])) {
      return false;
    }

    require_once('Library.php');
    $pageIdx = webksPageSlider_Library::getCurrentPageIdxCleaned();
    if($_POST['pageIdx'] !== $pageIdx){
      msg($langmessage['OOPS']);
      return false;
    }
    $headerimagesOptionlistPost = webksPageSlider_Library::getHeaderimagesOptionlistPost($pageIdx, $_POST);
    if(!empty($headerimagesOptionlistPost)){
      webksPageSlider_Library::savePageHeaderImagesToConfig($pageIdx, $headerimagesOptionlistPost);
      unset($_POST);
    }
  }

}
