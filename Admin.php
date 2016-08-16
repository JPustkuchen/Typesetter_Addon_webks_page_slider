<?php

defined('is_running') or die('Not an entry point...');

gpPlugin_incl('Gadget.php');

class webksPageSliderAdmin extends webksPageSlider {

  function __construct() {
    if (!common::LoggedIn()) {
      return FALSE;
    }
    parent::__construct();
    $cmd = common::GetCommand();
    switch ($cmd) {
      case 'SavePageSlidesConfig';
      $this->SavePageSlidesConfig();
      break;
    }
    $this->Show_Config();
  }

  /**
  *
  *
  */
  function Show_Config() {
    global $langmessage, $gp_titles;
    require_once('Library.php');
    $headerimagesDirPath = webksPageSlider_Library::getHeaderImagesDirPath();

    echo '<h2>Seiten-Slides verwalten</h2>';
    echo '<form name="wpconfig" action="' . common::GetUrl('Admin_Custom_Page_Header') . '" method="post">';

    echo '<label>Legen Sie das Slide-Bilder-Verzeichnis fest:<br/><input type="text" name="path" value="' . (!empty($headerimagesDirPath) ? htmlspecialchars($headerimagesDirPath, ENT_QUOTES) : '/image/slides') . '" /> &#187; ';
    echo common::Link('Admin_Uploaded', $langmessage['uploaded_files'], '', 'onclick="var pos=this.href.indexOf(\'?\'); if (pos>-1) this.href = this.href.substr(0,pos); this.href += \'?dir=\'+encodeURIComponent(document.forms.wpconfig.path.value);" target="_blank"') . '</label><br/><br/>';
    echo '<button type="submit" name="cmd" value="SavePageSlidesConfig" class="gpsubmit">' . $langmessage['save'] . '</button>';
    echo '</form>';

    $output = '<h2>Aktive Seiten-Slides:</h2>';
    $pages = webksPageSlider_Library::getAllPageHeaderimages();
    if(!empty($pages)){
      foreach($pages as $pageIdx => $pageHeaderimagesObj){
        $allPageHeaderimages = $pageHeaderimagesObj->getHeaderImages();
        if(!empty($allPageHeaderimages)){
          $count = count($allPageHeaderimages);
          $i = 0;
          $output .= '<h3>' . \gp\tool::Link_Page(\gp\tool::IndexToTitle($pageIdx)) . '</h3>';
          $output .= '<table>';
          foreach($allPageHeaderimages as $headerimageObj){
            $idx = $headerimageObj->getIdx();
            $output .= '<tr class="row-' . $i . '">';
            $output .= '<td>';
            $output .= '<img src="' . htmlspecialchars($headerimageObj->imgPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($headerimageObj->imgPath, ENT_QUOTES) . '" title="' . htmlspecialchars($headerimageObj->imgPath, ENT_QUOTES) . '" width="150" />';
            $output .= '</td><td>';
            $output .= '<strong>Titel:</strong> ' . htmlspecialchars($headerimageObj->imgTitle, ENT_QUOTES) . '<br />';
            $output .= '<strong>Text:</strong> ' . nl2br(htmlspecialchars($headerimageObj->imgText, ENT_QUOTES)) . '<br />';
            $output .= '<strong>Link URL:</strong> ' . htmlspecialchars($headerimageObj->imgLinkUrl, ENT_QUOTES);
            $output .= '</td><td class="additional">';
            $output .= '<strong>alt-Text:</strong> ' . htmlspecialchars($headerimageObj->imgAlt, ENT_QUOTES) . '<br />';
            $output .= '<strong>Notiz:</strong> ' . nl2br(htmlspecialchars($headerimageObj->imgNote, ENT_QUOTES)) . '';
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
      }
    } else {
      $output .= '<div><strong>Es wurden noch keine Slides auf Seiten ausgewählt.</strong><br />Ziehen Sie dazu das Gadget in den gew&uuml;nschten Bereich und w&auml;hlen die Seiten-Slides im Admin-Kopfmen&uuml;.</div>';
    }

    echo $output;
  }

  /**
  * Save the addon configuration
  *
  */
  function SavePageSlidesConfig() {
    global $langmessage, $dataDir;

    $s = trim($_POST['path']);
    if ($s == '') {
      $s = '/';
    }
    if ($s[0] != '/') {
      $s = '/' . $s;
    }
    if ($s[strlen($s) - 1] == '/') {
      $s = substr($s, 0, -1);
    }

    //check the directory
    $data_real = realpath($dataDir);
    $dir = realpath($dataDir . '/data/_uploaded/' . trim($s, '/\\'));

    if (!is_dir($dir)) {
      msg('Image directory is not a folder');
      return false;
    }

    if (strpos($dir, $data_real) !== 0) {
      msg('Invalid image directory');
      return false;
    }


    $this->config['path'] = array($s);
    if (gpFiles::SaveArray($this->config_file, 'config', $this->config)) {
      msg($langmessage['SAVED']);
    }
  }

}
