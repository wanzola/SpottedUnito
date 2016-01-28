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

// Expect Boz-PHP loaded
defined('BOZ_PHP') || die("Uh?");

// Expect a working database connection from Boz-PHP
expect('db');

define('INCLUDES', 'includes');

// On demand requests classes
spl_autoload_register( function($c) {
	$path = ABSPATH . _ . INCLUDES . _ . "class-$c.php";
	if( is_file( $path ) ) {
		require $path;
        }
} );

// General functions
require ABSPATH . _ . INCLUDES . _ . 'functions.php';
