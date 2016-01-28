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

trait SpottedSpotterTrait{
	public function deleteFromFifo() {
		expect('db');

		$db = & $GLOBALS['db'];

		$db->query( sprintf(
			"DELETE FROM {$db->getTable('fifo')} " .
			"WHERE spotted_ID = '%d' AND spotter_ID = '%d'",
			$this->spotted_ID,
			$this->spotter_ID
		) );
	}

	public function send() {
		apiRequest('sendMessage', [
			'chat_id' => $this->spotter_chat_id,
			'text'    => sprintf( _("Anonimo: %s"),
				$this->spotted_message
			)
		] );
	}
}

class SpottedSpotter {
	use SpottedTrait;
	use SpotterTrait;
	use SpottedSpotterTrait;

	function __construct() {
		self::prepareSpotted($this);
		self::prepareSpotter($this);
	}
}
