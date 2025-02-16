<?php
/*
  $Id: banner_yearly.php,v 1.1.1.1 2004/03/04 23:39:56 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_WS_CLASSES . 'phplot.php');

  $stats = array(array('0', '0', '0'));
  $banner_stats_query = tep_db_query("select year(banners_history_date) as year, sum(banners_shown) as value, sum(banners_clicked) as dvalue from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . $banner_id . "' group by year");
  while ($banner_stats = tep_db_fetch_array($banner_stats_query)) {
    $stats[] = array($banner_stats['year'], (($banner_stats['value']) ? $banner_stats['value'] : '0'), (($banner_stats['dvalue']) ? $banner_stats['dvalue'] : '0'));
  }

  $graph = new PHPlot(600, 400, 'images/graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);
  $graph->use_ttf =1;
  $graph->SetFileFormat($banner_extension);
  $graph->SetIsInline(1);
  $graph->SetPrintImage(0);

  $graph->SetSkipBottomTick(1);
  $graph->SetDrawYGrid(1);
  $graph->SetPrecisionY(0);
  //$graph->SetPlotType('lines');
  $graph->SetPlotType('bars');

  $graph->SetPlotBorderType('left');
  $graph->SetTitleFontSize('9');
  $graph->SetTitle(sprintf(TEXT_BANNERS_YEARLY_STATISTICS, iconv('GB2312','UTF-8'.'//IGNORE',$banner['banners_title'])));

  $graph->SetBackgroundColor('white');

  $graph->SetVertTickPosition('plotleft');
  $graph->SetDataValues($stats);
  $graph->SetDataColors(array('blue','red'),array('blue', 'red'));

  $graph->DrawGraph();

  $graph->PrintImage();
?>
