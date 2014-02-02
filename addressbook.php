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
    $mysql_database = "";
    $mysql_user = "";
    $mysql_password = "";

    try {

        // field names like Outlook uses
        $header = utf8_decode("\"Anrede\",\"Vorname\",\"Weitere Vornamen\",\"Nachname\",\"Suffix\",\"Firma\",\"Abteilung\",\"Position\",\"Straße geschäftlich\",\"Straße geschäftlich 2\",\"Straße geschäftlich 3\",\"Ort geschäftlich\",\"Region geschäftlich\",\"Postleitzahl geschäftlich\",\"Land/Region geschäftlich\",\"Straße privat\",\"Straße privat 2\",\"Straße privat 3\",\"Ort privat\",\"Bundesland/Kanton privat\",\"Postleitzahl privat\",\"Land/Region privat\",\"Weitere Straße\",\"Weitere Straße 2\",\"Weitere Straße 3\",\"Weiterer Ort\",\"Weiteres/r Bundesland/Kanton\",\"Weitere Postleitzahl\",\"Weiteres/e Land/Region\",\"Telefon Assistent\",\"Fax geschäftlich\",\"Telefon geschäftlich\",\"Telefon geschäftlich 2\",\"Rückmeldung\",\"Autotelefon\",\"Telefon Firma\",\"Fax privat\",\"Telefon privat\",\"Telefon privat 2\",\"ISDN\",\"Mobiltelefon\",\"Weiteres Fax\",\"Weiteres Telefon\",\"Pager\",\"Haupttelefon\",\"Mobiltelefon 2\",\"Telefon für Hörbehinderte\",\"Telex\",\"Abrechnungsinformation\",\"Benutzer 1\",\"Benutzer 2\",\"Benutzer 3\",\"Benutzer 4\",\"Beruf\",\"Büro\",\"E-Mail-Adresse\",\"E-Mail-Typ\",\"E-Mail: Angezeigter Name\",\"E-Mail 2: Adresse\",\"E-Mail 2: Typ\",\"E-Mail 2: Angezeigter Name\",\"E-Mail 3: Adresse\",\"E-Mail 3: Typ\",\"E-Mail 3: Angezeigter Name\",\"Empfohlen von\",\"Geburtstag\",\"Geschlecht\",\"Hobby\",\"Initialen\",\"Internet Frei/Gebucht\",\"Jahrestag\",\"Kategorien\",\"Kinder\",\"Konto\",\"Name Assistent\",\"Name des/r Vorgesetzten\",\"Notizen\",\"Organisationsnr.\",\"Ort\",\"Partner\",\"Postfach geschäftlich\",\"Postfach privat\",\"Priorität\",\"Privat\",\"Regierungsnr.\",\"Reisekilometer\",\"Sprache\",\"Stichwörter\",\"Vertraulichkeit\",\"Verzeichnisserver\",\"Webseite\",\"Weiteres Postfach\"");

        $con = mysql_connect($mysql_server, $mysql_user, $mysql_password);
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

        // fetch addressbook data
        $result = mysql_query("select egw_addressbook.n_prefix, egw_addressbook.n_given, egw_addressbook.n_middle, egw_addressbook.cat_id, egw_addressbook.n_family, egw_addressbook.n_suffix, egw_addressbook.n_fn, egw_addressbook.n_fileas, egw_addressbook.contact_bday, org_name, egw_addressbook.org_unit, egw_addressbook.contact_title, egw_addressbook.contact_role, egw_addressbook.contact_assistent, egw_addressbook.contact_room, egw_addressbook.adr_one_street , egw_addressbook.adr_one_street2, egw_addressbook.adr_one_locality, egw_addressbook.adr_one_region, egw_addressbook.adr_one_postalcode, egw_addressbook.adr_one_countryname, egw_addressbook.contact_label, egw_addressbook.adr_two_street, egw_addressbook.adr_two_street2, egw_addressbook.adr_two_locality, egw_addressbook.adr_two_region, egw_addressbook.adr_two_postalcode, egw_addressbook.adr_two_countryname, egw_addressbook.tel_work, egw_addressbook.tel_cell, egw_addressbook.tel_fax, egw_addressbook.tel_assistent, egw_addressbook.tel_car, egw_addressbook.tel_pager, egw_addressbook.tel_home, egw_addressbook.tel_fax_home, egw_addressbook.tel_cell_private, egw_addressbook.tel_other, egw_addressbook.tel_prefer, egw_addressbook.contact_email, egw_addressbook.contact_email_home, egw_addressbook.contact_url, egw_addressbook.contact_url_home, egw_accounts.account_lid from egw_addressbook, egw_accounts where contact_owner is not null and egw_accounts.account_id = egw_addressbook.contact_owner;");
        if (!$result) {
            echo "Initial query unsuccessful";
            mysql_close($con);
            die();
        } else {
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

                // build a csv row that can be parsed natively by Outlook without custom mappings
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

                // store the contact data inside the accounts array
                $data[$row["account_lid"]] = $data[$row["account_lid"]] . $f_row . "\r\n";
            }
        }

        mysql_close($con);

        foreach($data as $account => $content) {
            // ignore empty address books
            if ($content != "") {
                $fh = fopen($account . ".csv", 'wb') or die("Can't open output file ".$account . ".csv");
                fwrite($fh, $header .  "\r\n");
                fwrite($fh, $content . "\r\n");
                fclose($fh);
            }
        }
    } catch (Exception $e) {
        echo "Exception: Database connection cannot be established";
    }
?>
