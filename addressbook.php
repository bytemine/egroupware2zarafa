<?php
    // Copyright (c) 2013 bytemine GmbH
    // Author: Daniel Rauer <rauer@bytemine.net>
    // http://www.bytemine.net/


    $mysql_server = "localhost";
    $mysql_database = "egroupware";
    $mysql_user = "";
    $mysql_password = "";
    $output_file = "addressbook.csv";

    try {
        $fh = fopen($output_file, 'wb') or die("Can't open output file ".$output_file);
        
        $con = mysql_connect($mysql_server, $mysql_user, $mysql_password);
        if (!$con) {
            die("Connection to database cannot be established");
        }
        
        mysql_select_db($mysql_database, $con);
        
        // fetch addressbook data
        $result = mysql_query("select n_prefix, n_given, n_middle, cat_id, n_family, n_suffix, n_fn, n_fileas, contact_bday, org_name, org_unit, contact_title, contact_role, contact_assistent, contact_room, adr_one_street , adr_one_street2, adr_one_locality, adr_one_region, adr_one_postalcode, adr_one_countryname, contact_label, adr_two_street, adr_two_street2, adr_two_locality, adr_two_region, adr_two_postalcode, adr_two_countryname, tel_work, tel_cell, tel_fax, tel_assistent, tel_car, tel_pager, tel_home, tel_fax_home, tel_cell_private, tel_other, tel_prefer, contact_email, contact_email_home, contact_url, contact_url_home from egw_addressbook where contact_owner is not null;");
        if (!$result) {
            echo "Initial query unsuccessful";
            mysql_close($con);
            die();
        } else {
            // write out csv field names like Outlook uses
            $header = utf8_decode("\"Anrede\",\"Vorname\",\"Weitere Vornamen\",\"Nachname\",\"Suffix\",\"Firma\",\"Abteilung\",\"Position\",\"Straße geschäftlich\",\"Straße geschäftlich 2\",\"Straße geschäftlich 3\",\"Ort geschäftlich\",\"Region geschäftlich\",\"Postleitzahl geschäftlich\",\"Land/Region geschäftlich\",\"Straße privat\",\"Straße privat 2\",\"Straße privat 3\",\"Ort privat\",\"Bundesland/Kanton privat\",\"Postleitzahl privat\",\"Land/Region privat\",\"Weitere Straße\",\"Weitere Straße 2\",\"Weitere Straße 3\",\"Weiterer Ort\",\"Weiteres/r Bundesland/Kanton\",\"Weitere Postleitzahl\",\"Weiteres/e Land/Region\",\"Telefon Assistent\",\"Fax geschäftlich\",\"Telefon geschäftlich\",\"Telefon geschäftlich 2\",\"Rückmeldung\",\"Autotelefon\",\"Telefon Firma\",\"Fax privat\",\"Telefon privat\",\"Telefon privat 2\",\"ISDN\",\"Mobiltelefon\",\"Weiteres Fax\",\"Weiteres Telefon\",\"Pager\",\"Haupttelefon\",\"Mobiltelefon 2\",\"Telefon für Hörbehinderte\",\"Telex\",\"Abrechnungsinformation\",\"Benutzer 1\",\"Benutzer 2\",\"Benutzer 3\",\"Benutzer 4\",\"Beruf\",\"Büro\",\"E-Mail-Adresse\",\"E-Mail-Typ\",\"E-Mail: Angezeigter Name\",\"E-Mail 2: Adresse\",\"E-Mail 2: Typ\",\"E-Mail 2: Angezeigter Name\",\"E-Mail 3: Adresse\",\"E-Mail 3: Typ\",\"E-Mail 3: Angezeigter Name\",\"Empfohlen von\",\"Geburtstag\",\"Geschlecht\",\"Hobby\",\"Initialen\",\"Internet Frei/Gebucht\",\"Jahrestag\",\"Kategorien\",\"Kinder\",\"Konto\",\"Name Assistent\",\"Name des/r Vorgesetzten\",\"Notizen\",\"Organisationsnr.\",\"Ort\",\"Partner\",\"Postfach geschäftlich\",\"Postfach privat\",\"Priorität\",\"Privat\",\"Regierungsnr.\",\"Reisekilometer\",\"Sprache\",\"Stichwörter\",\"Vertraulichkeit\",\"Verzeichnisserver\",\"Webseite\",\"Weiteres Postfach\"");
            fwrite($fh, $header);
            
            while ($row = mysql_fetch_array($result)) {
                $cats = "";
                
                // get categories for this entry
                if ($row["cat_id"] != "") {
                    $ids = split(",", $row["cat_id"]);
                    foreach($ids as $id) {
                        $result_cat = mysql_query("select cat_name from egw_categories where cat_id=".$id.";");
                        $row_cat = mysql_fetch_array($result_cat);
                        if ($row_cat["cat_name"] != "") {
                            $cats .= $row_cat["cat_name"].";";
                        }
                    }
                    $cats = rtrim($cats, ";");
                    $row["cat_id"] = $cats;
                }
                
                // build a csv file that can be parsed natively by Outlook without custom mappings
                $f_row = "";
                $f_row .= "\"".$row["n_prefix"]."\",";
                $f_row .= "\"".$row["n_given"]."\",";
                $f_row .= "\"".$row["n_middle"]."\",";
                $f_row .= "\"".$row["n_family"]."\",";
                $f_row .= "\"".$row["n_suffix"]."\",";
                $f_row .= "\"".$row["org_name"]."\",";
                $f_row .= "\"".$row["org_unit"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["adr_two_street"]."\",";
                $f_row .= "\"".$row["adr_two_street2"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["adr_two_locality"]."\",";
                $f_row .= "\"".$row["adr_two_region"]."\",";
                $f_row .= "\"".$row["adr_two_postalcode"]."\",";
                $f_row .= "\"".$row["adr_two_countryname"]."\",";
                $f_row .= "\"".$row["adr_one_street"]."\",";
                $f_row .= "\"".$row["adr_one_street2"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["adr_one_locality"]."\",";
                $f_row .= "\"".$row["adr_one_region"]."\",";
                $f_row .= "\"".$row["adr_one_postalcode"]."\",";
                $f_row .= "\"".$row["adr_one_countryname"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["tel_assistent"]."\",";
                $f_row .= "\"".$row["tel_fax"]."\",";
                $f_row .= "\"".$row["tel_work"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["tel_car"]."\",";
                $f_row .= "\"".$row["tel_other"]."\",";
                $f_row .= "\"".$row["tel_fax_home"]."\",";
                $f_row .= "\"".$row["tel_home"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["tel_cell_private"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["tel_pager"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["contact_email"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["contact_title"]."\",";
                $f_row .= "\"".$row["contact_email_home"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["contact_title"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$cats."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["contact_assistent"]."\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"\",";
                $f_row .= "\"".$row["contact_url"]."\",";
                $f_row .= "\"\",";
                
                $f_row = rtrim($f_row, ",");
                fwrite($fh, $f_row . "\r\n");
            }
        }

        mysql_close($con);
        fclose($fh);
    } catch (Exception $e) {
        echo "Exception: Database connection cannot be established";
    }
?>
