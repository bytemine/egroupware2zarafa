<?php
  /*
   * Copyright (c) 2013 bytemine GmbH <info@bytemine.net>
   *
   * Permission to use, copy, modify, and distribute this software for any
   * purpose with or without fee is hereby granted, provided that the above
   * copyright notice and this permission notice appear in all copies.
   *
   * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
   * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
   * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
   * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
   * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
   * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
   * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
   */

    // Author: Daniel Rauer <rauer@bytemine.net>
    // http://www.bytemine.net/

    $mysql_server = "localhost";
    $mysql_database = "egroupware";
    $mysql_user = "";
    $mysql_password = "";
    // organizational mail address
    $mail_address = "info@example.com";

    try {
        $con = mysql_connect($mysql_server, $mysql_user, $mysql_password);
        mysql_set_charset('utf8', $con);
        date_default_timezone_set('UTC');

        if (!$con) {
            die("Connection to database cannot be established");
        }

        mysql_select_db($mysql_database, $con);

        // get all account names and stick them into an array
        $res = mysql_query("select account_lid from egw_accounts;");
        if (!$res) {
            echo "Account name query unsuccessful";
            mysql_close($con);
            die();
        } else {
            $data = array();
            while($row = mysql_fetch_array($res)) {
                $data[$row[0]] = "";
            }
        }

        // initial query to fetch calendar entries
        $cals = mysql_query("select egw_cal.cal_id, egw_cal.cal_modified, egw_cal.cal_category, egw_cal.cal_owner, egw_cal.cal_uid, egw_cal.cal_title, egw_cal.cal_description, egw_cal.cal_location, egw_cal.cal_public, egw_accounts.account_lid from egw_cal, egw_accounts where egw_accounts.account_id = egw_cal.cal_owner");
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

                // write calender entry to accounts array
                $data[$cal["account_lid"]] = $data[$cal["account_lid"]] . "\r\nBEGIN:VEVENT
UID:".$cal["cal_uid"]."
ORGANIZER;CN=\"".$cal["account_lid"]."\":MAILTO:\"".$mail_address."
LOCATION:".$cal["cal_location"]."
SUMMARY:".$cal["cal_title"]."
DESCRIPTION:".$cal["cal_description"]."
CLASS:".$class."
CATEGORIES:".$cats."
DTSTART:".$start."
DTEND:".$end."
DTSTAMP:".$mod."";
                if ($repeat_str != "") {
                    $data[$cal["account_lid"]] = $data[$cal["account_lid"]] . "\r\n".$repeat_str;
                }
                $data[$cal["account_lid"]] = $data[$cal["account_lid"]] . "\r\nEND:VEVENT";
            }
        }

        mysql_close($con);

        foreach($data as $account => $content) {
            // ignore empty address books
            if ($content != "") {
                $fh = fopen($account . ".ics", 'wb') or die("Can't open output file ".$account . ".ics");
                fwrite($fh, "BEGIN:VCALENDAR
VERSION:2.0
PRODID:eGroupware
METHOD:PUBLISH");
                fwrite($fh, $content . "\r\n");
                fwrite($fh, "END:VCALENDAR");
                fclose($fh);
            }
        }
    } catch (Exception $e) {
        echo "Exception: Database connection cannot be established";
    }
?>
