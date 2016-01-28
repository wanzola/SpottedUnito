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
 * This program is distributer in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

trait SpotterTrait {
	public static function prepareSpotter( & $spotter ) {
		if( @ $spotter->spotter_ID ) {
			$spotter->spotter_ID = (int) $spotter->spotter_ID;
		}
		if( @ $spotter->spotter_chat_id ) {
			$spotter->spotter_chat_id = (int) $spotter->spotter_chat_id;
		}
	}
}

class Spotter {
	use SpotterTrait;

	function __construct() {
		self::prepareSpotter($this);
	}
}
