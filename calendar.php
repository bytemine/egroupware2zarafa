<?php
    // Copyright (c) 2013 bytemine GmbH
    // Author: Daniel Rauer <rauer@bytemine.net>
    // http://www.bytemine.net/
    
    $mysql_server = "localhost";
    $mysql_database = "egroupware";
    $mysql_user = "";
    $mysql_password = "";
    $output_file = "calendar.ics";
    
    try {
        $fh = fopen($output_file, 'w') or die("Can't open output file ".$output_file);
        
        // print header
        fwrite($fh, "BEGIN:VCALENDAR
VERSION:2.0
PRODID:eGroupware
METHOD:PUBLISH");
        
        $con = mysql_connect($mysql_server, $mysql_user, $mysql_password);
        mysql_set_charset('utf8', $con);
        date_default_timezone_set('UTC');

        if (!$con) {
            die("Connection to database cannot be established");
        }

        mysql_select_db($mysql_database, $con);

        // initial query to fetch calendar entries
        $cals = mysql_query("select * from egw_cal");
        if (!$cals) {
            echo "Initial query unsuccessful";
            mysql_close($con);
            die();
        } else {
            while ($cal = mysql_fetch_array($cals)) {
                // retrieve public/private flag
                $class = "PUBLIC";
                if ($cal["cal_public"] == 0) {
                    $class = "PRIVATE";
                }
                
                // retrieve start, end and modification timestamps
                $start = "";
                $end = "";
                $dates = mysql_query("select * from egw_cal_dates where cal_id=".$cal["cal_id"]);
                $date = mysql_fetch_array($dates);
                $start = date("Ymd", $date["cal_start"]) . "T" . date("His", $date["cal_start"]) . "Z";
                $end = date("Ymd", $date["cal_end"]) . "T" . date("His", $date["cal_end"]) . "Z";
                $mod = date("Ymd", $cal["cal_modified"]) . "T" . date("His", $cal["cal_modified"]);
                
                // retrieve repeats
                $repeats = mysql_query("select * from egw_cal_repeats where cal_id=".$cal["cal_id"]);
                $repeat = mysql_fetch_array($repeats);
                $repeat_str = "";
                if ($repeat["cal_id"] != "") {
                  $counts = mysql_query("select count(*) as count from egw_cal_dates where cal_id=".$cal["cal_id"]);
                  $count = mysql_fetch_array($counts);
                  $recur_type = $repeat["recur_type"];
                  if ($recur_type == 1) {
                    if ($count > 1) {
                        // repeat only on workdays
                        $repeat_str = "RRULE:FREQ=WEEKLY;COUNT=".$count["count"].";BYDAY=Mo,Tu,We,Th,Fr";
                    } else {
                        $repeat_str = "RRULE:FREQ=DAILY";
                    }
                  } else if ($recur_type == 2) {
                    $repeat_str = "RRULE:FREQ=WEEKLY";
                  } else if ($recur_type == 3) {
                    $repeat_str = "RRULE:FREQ=MONTHLY";
                  } else if ($recur_type == 5) {
                    $repeat_str = "RRULE:FREQ=YEARLY";
                  }
                }
                
                // retrieve categories
                $cats = "";
                if ($cal["cal_category"] != "") {
                    $cat_ids = split(",", $cal["cal_category"]);
                    foreach($cat_ids as $cat_id) {
                        $cat = mysql_query("select cat_name from egw_categories where cat_id=".$cat_id.";");
                        $row_cat = mysql_fetch_array($cat);
                        if ($row_cat["cat_name"] != "") {
                            $cats .= $row_cat["cat_name"].",";
                        }
                    }
                    $cats = rtrim($cats, ",");
                }
                
                // retrieve organizer, no more information existing in database than account_lid
                $account = mysql_query("select * from egw_accounts where account_id =".$cal["cal_owner"]);
                $organizer = mysql_fetch_array($account);
                
                // write calender entry to file
                fwrite($fh, "\r\nBEGIN:VEVENT
UID:".$cal["cal_uid"]."
ORGANIZER;CN=\"".$organizer["account_lid"]."\":MAILTO:info@asmdb.com
LOCATION:".$cal["cal_location"]."
SUMMARY:".$cal["cal_title"]."
DESCRIPTION:".$cal["cal_description"]."
CLASS:".$class."
CATEGORIES:".$cats."
DTSTART:".$start."
DTEND:".$end."
DTSTAMP:".$mod.""
);
                if ($repeat_str != "") {
                    fwrite($fh, "\r\n".$repeat_str);
                }
                fwrite($fh, "\r\nEND:VEVENT");
            }
        }

        mysql_close($con);
        
        // print footer
        fwrite($fh, "\r\nEND:VCALENDAR");
        fclose($fh);
    } catch (Exception $e) {
        echo "Exception: Database connection cannot be established";
    }
?>
