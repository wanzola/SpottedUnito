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

trait FifoTrait {
	public function prepareFifo( & $fifo ) {
		if( @ $fifo->fifo_ID ) {
			$fifo->fifo_ID = (int) $fifo->fifo_ID;
		}
		if( @ $fifo->fifo_chat_id ) {
			$fifo->fifo_chat_id = (int) $fifo->fifo_chat_id;
		}
	}
}

class Fifo {
	use FifoTrait;

	function __construct() {
		self::prepareFifo($this);
	}
}
