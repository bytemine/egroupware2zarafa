# egroupware2zarafa

Some scripts to export eGroupware contacts and calendar data so it can be imported into Outlook/Zarafa

These scripts let you export data from an eGroupware database:

* calendar data gets exported to an ICS file
* contacts get exported to an CSV file

Both files are ready to get imported into Outlook (and thereby Zarafa) using the Outlook standard import tool.

## Usage

Start by adapting the database credentials in both scripts. Afterwards simply execute the scripts by calling "php addressbook.php" / "php calendar.php" and the CSV or ICS file are created.
