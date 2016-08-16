<?php

class webksPageSlider_View {

  /**
  * Show available images in the current directory
  *
  */
  function getHeaderImageSelectFormDOM() {
    global $title, $dataDir;
    includeFile('admin/admin_uploaded.php');

    $dom = '';

    //get the current image
    $curr_name = '';
    $curr = '';
    if (isset($this->config['pages'][$title])) {
      $curr_name = basename($this->config['pages'][$title]['img']);
      $curr = $this->config['pages'][$title]['img'];
    }

    //display available images
    $path = trim($this->config['path'], '/\\');
    $dir = $dataDir . '/data/_uploaded/' . $path;
    $files = scandir($dir);
    $curr_shown = false;
    $dom .= '<div class="page_header_images">';
    foreach ($files as $file) {

      if ($file == '.' || $file == '..') {
        continue;
      }

      $full = $dir . '/' . $file;
      $img = '/data/_uploaded/' . $path . '/' . $file;
      $img = common::GetDir($img);
      $thumb = self::GetThumbnailPath($img);
      $checked = '';

      if (!admin_uploaded::isImg($full)) {
        continue;
      }

      if ($curr == $img) {
        $checked = ' checked';
        $curr_shown = true;
      }

      $dom .= '<label>';
      $dom .= '<input type="checkbox" name="image[]" value="' . htmlspecialchars($img) . '" ' . $checked . ' />';
      $dom .= '<img src="' . $thumb . '" alt="' . htmlspecialchars($file) . '" title="' . htmlspecialchars($img) . '" />';
      $dom .= '</label>';
    }

    // if choosen image is located in some other directory
    if (!$curr_shown && !empty($curr)) {
      $thumb = self::GetThumbnailPath($curr);
      $dom .= '<label>';
      $dom .= '<input type="radio" name="image" value="' . htmlspecialchars($curr) . '" checked />';
      $dom .= '<img src="' . $thumb . '" alt="' . htmlspecialchars($curr_name) . '" title="' . htmlspecialchars($curr) . '" />';
      $dom .= '</label>';
    }

    $dom .= '<label>';
    $dom .= '<input type="radio" name="image" value="" />';
    $dom .= '<span>- None -</span>';
    $dom .= '</label>';

    $dom .= '</div>';

    return $dom;
  }

}
