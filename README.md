# [@SpottedUnitoBot](https://telegram.me/spottedunitobot)
L'originale bot Telegram per lo Spotted di Unito!

# Utilizzo
Invia `/start` e avrai istruzioni!

https://telegram.me/spottedunitobot

# Installation
This Telegram bot is based on PHP, Apache, MySQL/MariaDB and Boz-PHP (Another PHP framework).

Please install Boz-PHP in your GNU/Linux webserver in the `/usr/share` folder:

    bzr branch lp:boz-php-another-php-framework /usr/share/boz-php-another-php-framework

Then import the `database-schema.sql` file in MySQL/MariaDB.

Then rename `load-sample.php` to `load.php` and fill it with your database credentials and your Telegram bot secret key.

Happy hacking!

# License (GNU AGPL)
This program is free as in freedom software: you can redistribute it and/or modify it under the terms of the **GNU Affero General Public License** as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program.  If not, see http://www.gnu.org/licenses/.
