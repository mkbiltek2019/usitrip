<?php
/*
  $Id: sales_report.php,v 0.1 2002/12/06 23:52:29 cwi Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  class sales_report {
    var $mode, $globalStartDate, $startDate, $endDate, $info, $previous, $next, $startDates, $endDates, $size, $referer;

    function sales_report($mode, $startDate = "", $endDate = "", $filter = "", $referer = 0) {
      // startDate and endDate have to be a unix timestamp. Use mktime !
      // if set then both have to be valid startDate and endDate
      $this->mode = $mode;
      $this->previous = "";
      $this->next = "";
      $this->filter = "";
      $this->info = array(array());
	  $this->referer = $referer;

      // get date of first sale
      $first_query = tep_db_query("select UNIX_TIMESTAMP(min(date_purchased)) as first FROM " . TABLE_ORDERS);
      $first = tep_db_fetch_array($first_query);
      $this->globalStartDate = mktime(0, 0, 0, date("m", $first['first']), date("d", $first['first']), date("Y", $first['first']));

      // get all possible status for filter
      $tmp_query = tep_db_query("SELECT * FROM " . TABLE_ORDERS_STATUS . " order by orders_status_name");
      $i = 0;
      while ($status = tep_db_fetch_array($tmp_query)) {
        $tmp[$i]['index'] = $status['orders_status_id'];
        $tmp[$i]['value'] = $status['orders_status_name'];
        $i++;
      }
      $this->status_available = $tmp;
      $this->status_available_size = $i;

      if ($endDate == "" or $startDate == "") {
        // set startDate to nothing
        $dateGiven = false;
        $startDate = 0;
        // endDate is today
        $this->endDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
      } else {
        $dateGiven = true;
        if ($endDate > mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
          $this->endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
        } else {
          // set endDate to the given Date with "round" on days
          $this->endDate = mktime(0, 0, 0, date("m", $endDate), date("d", $endDate) + 1, date("Y", $endDate));
        }
      }
      switch ($this->mode) {
        // hourly
        case '1':
          if ($dateGiven) {
            // "round" to midnight
            $this->startDate = mktime(0, 0, 0, date("m", $startDate), date("d", $startDate), date("Y", $startDate));
            $this->endDate = mktime(0, 0, 0, date("m", $startDate), date("d", $startDate) + 1, date("Y", $startDate));
            // size to number of hours
            $this->size = 24;
          } else {
            // startDate to start of this day
            $this->startDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $this->endDate = mktime(date("G") + 1, 0, 0, date("m"), date("d"), date("Y"));
            // size to number of hours
            $this->size = date("G") + 1;
            if ($this->startDate < $this->globalStartDate) {
              $this->startDate = $this->globalStartDate;
            }
          }
          for ($i = 0; $i < $this->size; $i++) {
            $this->startDates[$i] = mktime($i, 0, 0, date("m", $this->startDate), date("d", $this->startDate), date("Y", $this->startDate));
            $this->endDates[$i] = mktime($i + 1, 0, 0, date("m", $this->startDate), date("d", $this->startDate), date("Y", $this->startDate));
          }
          break;
        // day
        case '2':
          if ($dateGiven) {
            // "round" to day
            $this->startDate = mktime(0, 0, 0, date("m", $startDate), date("d", $startDate), date("Y", $startDate));
            // size to number of days
            $this->size = ($this->endDate - $this->startDate) / (60 * 60 * 24);
          } else {
            // startDate to start of this week
            $this->startDate = mktime(0, 0, 0, date("m"), date("d") - date("w"), date("Y"));
            $this->endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
            // size to number of days
            $this->size = date("w") + 1;
            if ($this->startDate < $this->globalStartDate) {
              $this->startDate = $this->globalStartDate;
            }
          }
          for ($i = 0; $i < $this->size; $i++) {
            $this->startDates[$i] = mktime(0, 0, 0, date("m", $this->startDate), date("d", $this->startDate) + $i, date("Y", $this->startDate));
            $this->endDates[$i] = mktime(0, 0, 0, date("m", $this->startDate), date("d", $this->startDate) + ($i + 1), date("Y", $this->startDate));
          }
          break;
        // week
        case '3':
          if ($dateGiven) {
            $this->startDate = mktime(0, 0, 0, date("m", $startDate), date("d", $startDate) - date("w", $startDate), date("Y", $startDate));

          } else {
            // startDate to beginning of first week of this month
            $firstDayOfMonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
            $this->startDate = mktime(0, 0, 0, date("m"), 1 - date("w", $firstDayOfMonth), date("Y"));
          }
          if ($this->startDate < $this->globalStartDate) {
            $this->startDate = $this->globalStartDate;
          }
          // size to the number of weeks in this month till endDate
          $this->size = ceil((($this->endDate - $this->startDate + 1) / (60 * 60 * 24)) / 7);
          for ($i = 0; $i < $this->size; $i++) {
            $this->startDates[$i] = mktime(0, 0, 0, date("m", $this->startDate), date("d", $this->startDate) +  $i * 7, date("Y", $this->startDate));
            $this->endDates[$i] = mktime(0, 0, 0, date("m", $this->startDate), date("d", $this->startDate) + ($i + 1) * 7, date("Y", $this->startDate));
          }
          break;
        // month
        case '4':
          if ($dateGiven) {
            $this->startDate = mktime(0, 0, 0, date("m", $startDate), 1, date("Y", $startDate));
            // size to number of days
          } else {
            // startDate to first day of the first month of this year
            $this->startDate = mktime(0, 0, 0, 1, 1, date("Y"));
            // size to number of months in this year
          }
          if ($this->startDate < $this->globalStartDate) {
            $this->startDate = mktime(0, 0, 0, date("m", $this->globalStartDate), 1, date("Y", $this->globalStartDate));
          }
          $this->size = (date("Y", $this->endDate) - date("Y", $this->startDate)) * 12 + (date("m", $this->endDate) - date("m", $this->startDate)) + 1;
          $tmpMonth = date("m", $this->startDate);
          $tmpYear = date("Y", $this->startDate);
          for ($i = 0; $i < $this->size; $i++) {
            // the first of the $tmpMonth + $i
            $this->startDates[$i] = mktime(0, 0, 0, $tmpMonth + $i, 1, $tmpYear);
            // the first of the $tmpMonth + $i + 1 month
            $this->endDates[$i] = mktime(0, 0, 0, $tmpMonth + $i + 1, 1, $tmpYear);
          }
          break;
        // year
        case '5':
          if ($dateGiven) {
            $this->startDate = mktime(0, 0, 0, 1, 1, date("Y", $startDate));
            $this->endDate = mktime(0, 0, 0, 1, 1, date("Y", $endDate) + 1);
          } else {
            // startDate to first of current year - $max_years
            $this->startDate = mktime(0, 0, 0, 1, 1, date("Y") - 5 + 1);
            // endDate to today
            $this->endDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
          }
          if ($this->startDate < $this->globalStartDate) {
            $this->startDate = $this->globalStartDate;
          }
          $this->size = date("Y", $this->endDate) - date("Y", $this->startDate) + 1;
          $tmpYear = date("Y", $this->startDate);
          for ($i = 0; $i < $this->size; $i++) {
            $this->startDates[$i] = mktime(0, 0, 0, 1, 1, $tmpYear + $i);
            $this->endDates[$i] = mktime(0, 0, 0, 1, 1, $tmpYear + $i + 1);
          }
          break;
      }

      if (($this->mode < 3) or ($this->mode == 4)) {
        // set previous to start - diff
        $tmpDiff = $this->endDate - $this->startDate;
        if ($this->size == 0) {
          $tmpUnit = 0;
        } else {
          $tmpUnit = $tmpDiff / $this->size;
        }
        //echo $tmpDiff . " " . $tmpUnit . "<br>";

        switch($this->mode) {
          // hourly
          case '1':
            $tmp1 =  24 * 60 * 60;
            break;
          // daily
          case '2':
            $tmp1 = 7 * 24 * 60 * 60;
            break;
          // weekly
          case '3':
            $tmp1 = 30 * 24 * 60 * 60;
            break;
          // monthly
          case '4':
            $tmp1 = 365 * 24 * 60 * 60;
            break;
        }
        $tmp = ceil($tmpDiff / $tmp1);
        if ($tmp > 1) {
          $tmpShift = ($tmp * $tmpDiff) + $tmpUnit;
        } else {
          $tmpShift = $tmp1 + $tmpUnit;
        }

        $tmpStart = $this->startDate - $tmpShift + $tmpUnit;
        $tmpEnd = $this->startDate - $tmpUnit;
        if ($tmpStart >= $this->globalStartDate or $this->mode == 4) {
          //echo strftime("%T %x", $tmpStart). " - " . strftime("%T %x", $tmpEnd) . "<br>";
          $this->previous = "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd . "&referer=".$this->referer;
        }

        $tmpStart = $this->endDate;
        $tmpEnd = $this->endDate + $tmpShift - 2 * $tmpUnit;
        if ($tmpEnd < mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
          //echo strftime("%T %x", $tmpStart). " - " . strftime("%T %x", $tmpEnd);
          $this->next = "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd . "&referer=".$this->referer;
        } else {
          if ($tmpEnd - $tmpDiff < mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
            $tmpEnd = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            //echo strftime("%T %x", $tmpStart). " - " . strftime("%T %x", $tmpEnd);
            $this->next = "report=" . $this->mode . "&startDate=" . $tmpStart . "&endDate=" . $tmpEnd . "&referer=".$this->referer;
          }
        }
      }
      
      // handle filters
      // submit the filters that way:
      // 01001 means use filter for status 2 and 5 set.
      if (strlen($filter) > 0) {
        $tmp = "";
        $tmp1 = "";
        for ($i = 0; $i < $this->status_available_size; $i++) {
          if (substr($filter, $i, 1) == "1") {
            $tmp1 .= "1";
            if (strlen($tmp) == 0) {
              $tmp = "o.orders_status <> " . $this->status_available[$i]['index'];
            } else {
              $tmp .= " and o.orders_status <> " . $this->status_available[$i]['index'];
            }
          } else {
            $tmp1 .= "0";
          }
        }
        $this->filter_sql = $tmp;
        $this->filter = $tmp1;
      }else{
	  
	  	$tmp = "";
        $tmp1 = "";
        for ($i = 0; $i < $this->status_available_size; $i++) {
		  if ($this->status_available[$i]['index'] == "6" || $this->status_available[$i]['index'] == "100060" || $this->status_available[$i]['index'] == "100061" || $this->status_available[$i]['index'] == "100055") {
            $tmp1 .= "1";
            if (strlen($tmp) == 0) {
              $tmp = "o.orders_status <> " . $this->status_available[$i]['index'];
            } else {
              $tmp .= " and o.orders_status <> " . $this->status_available[$i]['index'];
            }
          } else {
            $tmp1 .= "0";
          }
        }
        $this->filter_sql = $tmp;
        $this->filter = $tmp1;
	  
	  
	  }
      $this->filter_link = "report=" . $this->mode . "&startDate=" . $startDate . "&endDate=" . $endDate;


      // if ($dateGiven) {
      //  echo "<br>" . strftime("%H %x", $this->startDate). " - " . strftime("%H %x", $this->endDate);
      //} else {
        $this->query();
      //}
    }

    function query() {
		$this->info['filter'] = $this->filter_sql;
		$not_in_customers_ids = '';
		$where_referer = '';
		if((int)$this->referer){	//filter referer customers
			$where_referer = ' AND c.customers_referer_type ="'.(int)$this->referer.'" ';
			$customers_sql = tep_db_query('SELECT c.customers_id FROM '.TABLE_CUSTOMERS.' c WHERE c.customers_referer_type="'.(int)$this->referer.'" ');
			while($customers_rows = tep_db_fetch_array($customers_sql)){
				$not_in_customers_ids .= $customers_rows['customers_id'].',';
			}
			$not_in_customers_ids = substr($not_in_customers_ids,0,(strlen($not_in_customers_ids)-1) );
			if($not_in_customers_ids!=''){
				$not_in_customers_ids = ' AND o.customers_id IN('.$not_in_customers_ids.') ';
			}else{
				$not_in_customers_ids = ' AND o.customers_id IN(0) ';
			}
			//echo $not_in_customers_ids;
		}

      if (strlen($this->filter_sql) > 0) {
        $tmp_query = "SELECT sum(op.final_price * op.products_quantity) as value, sum(op.final_price_cost * op.products_quantity) as costval, avg(op.final_price * op.products_quantity) as avg, count(o.orders_id) as count FROM " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op WHERE ot.orders_id = o.orders_id and o.orders_id = op.orders_id and ot.class = 'ot_total' and (" . $this->filter_sql . ")".$not_in_customers_ids;
		//$tmp_query = "SELECT sum(ot.value) as value, sum(o.order_cost) as costval, avg(ot.value) as avg, count(ot.value) as count FROM " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . " o WHERE ot.orders_id = o.orders_id and ot.class = 'ot_total' and (" . $this->filter_sql . ")".$not_in_customers_ids;
      } else {
        $tmp_query = "SELECT sum(op.final_price * op.products_quantity) as value, sum(op.final_price_cost * op.products_quantity) as costval, avg(op.final_price * op.products_quantity) as avg, count(o.orders_id) as count FROM " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op WHERE ot.orders_id = o.orders_id and o.orders_id = op.orders_id and ot.class = 'ot_total'".$not_in_customers_ids;
		//$tmp_query = "SELECT sum(ot.value) as value, sum(o.order_cost) as costval, avg(ot.value) as avg, count(ot.value) as count FROM " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . " o WHERE ot.orders_id = o.orders_id and ot.class = 'ot_total'".$not_in_customers_ids;
      }

      for ($i = 0; $i < $this->size; $i++) {
        $report_query = tep_db_query($tmp_query . " AND o.date_purchased >= '" . tep_db_input(date("Y-m-d\TH:i:s", $this->startDates[$i])) . "' AND o.date_purchased < '" . tep_db_input(date("Y-m-d\TH:i:s", $this->endDates[$i])) . "'");
        $report = tep_db_fetch_array($report_query);
        $this->info[$i]['cost'] = $report['costval'];
		$this->info[$i]['sum'] = $report['value'];
        $this->info[$i]['avg'] = $report['avg'];
        $this->info[$i]['count'] = $report['count'];
		
		$report_signups_query = tep_db_query("select count(c.customers_id) as signup from ".TABLE_CUSTOMERS." c, ".TABLE_CUSTOMERS_INFO." ci where c.customers_id = ci.customers_info_id and ci.customers_info_date_account_created >= '" . tep_db_input(date("Y-m-d\TH:i:s", $this->startDates[$i])) . "' AND ci.customers_info_date_account_created < '" . tep_db_input(date("Y-m-d\TH:i:s", $this->endDates[$i])) . "' ".$where_referer);
		$report_signups = tep_db_fetch_array($report_signups_query);
		$this->info[$i]['signup'] = $report_signups['signup'];
        switch ($this->mode) {
          // hourly
          case '1':
            $this->info[$i]['text'] = strftime("%H", $this->startDates[$i]) . " - " . strftime("%H", $this->endDates[$i]);
            $this->info[$i]['link'] = "";
            break;
          // daily
          case '2':
		 	$this->info[$i]['ddate']=  date('Y-m-d',$this->startDates[$i]);
            $this->info[$i]['text'] = strftime("%m/%d/%Y", $this->startDates[$i]);
            $this->info[$i]['link'] = "report=1&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date("m", $this->endDates[$i]), date("d", $this->endDates[$i]) + 1, date("Y", $this->endDates[$i]));
            break;
          // weekly
          case '3':
		  $this->info[$i]['ddate'] =  date('Y-m-d',$this->startDates[$i]);
		  $this->info[$i]['dedate'] = date('Y-m-d',$this->endDates[$i]);
          $this->info[$i]['text'] = strftime("%m/%d/%Y", $this->startDates[$i]) . " - " . strftime("%m/%d/%Y", mktime(0, 0, 0, date("m", $this->endDates[$i]), date("d", $this->endDates[$i]) - 1, date("Y", $this->endDates[$i])));
            $this->info[$i]['link'] = "report=2&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date("m", $this->endDates[$i]), date("d", $this->endDates[$i]) - 1, date("Y", $this->endDates[$i]));
            break;
          // monthly
          case '4':
            $this->info[$i]['text'] = strftime("%b %y", $this->startDates[$i]);
            $this->info[$i]['link'] = "report=3&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date("m", $this->endDates[$i]), date("d", $this->endDates[$i]) - 1, date("Y", $this->endDates[$i]));
            break;
          // yearly
          case '5':
            $this->info[$i]['text'] = date("Y", $this->startDates[$i]);
            $this->info[$i]['link'] = "report=4&startDate=" . $this->startDates[$i] . "&endDate=" . mktime(0, 0, 0, date("m", $this->endDates[$i]) - 1, date("d", $this->endDates[$i]), date("Y", $this->endDates[$i]));
            break;
        }
      }
      $tmp_query =  "select sum(ot.value) as shipping FROM " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . " o WHERE ot.orders_id = o.orders_id and ot.class = 'ot_shipping'".$not_in_customers_ids;

      for ($i = 0; $i < $this->size; $i++) {
        $report_query = tep_db_query($tmp_query . " AND o.date_purchased >= '" . tep_db_input(date("Y-m-d\TH:i:s", $this->startDates[$i])) . "' AND o.date_purchased < '" . tep_db_input(date("Y-m-d\TH:i:s", $this->endDates[$i])) . "'");
        $report = tep_db_fetch_array($report_query);
        $this->info[$i]['shipping'] = $report['shipping'];
      }
    }
  }
?>