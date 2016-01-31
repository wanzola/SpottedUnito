<?php
/*
 * Spotted (UniTo?)
 * Copyright (C) 2015 Wanzo Laviola, Valerio Bozzolan
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

die("You have to rename this file to load.php");

// Fill with database info
$database = '';
$username = '';
$password = '';
$location = 'localhost';

// Database table prefix
$prefix = '';

// Fill with your Telegram super-secret key from @BotFather
define('BOT_TOKEN', '');

// Leave these as they are
define('API_URL',   'https://api.telegram.org/bot' . BOT_TOKEN . '/');

define('ABSPATH', __DIR__);
define('DEBUG',   true);
define('USE_DB_OPTIONS', false);

// Administrator chat_id
define('WANZO', 666);

// Load Boz-PHP - Assuming that you have installed it in the right way
require '/usr/share/boz-php-another-php-framework/load.php';
