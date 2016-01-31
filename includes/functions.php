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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.	If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Spotted FIFO management.
 *
 * Please think about max execution time.
 *
 * @return int Spotted sent
 */
function spotted_fifo($n = 1) {
	expect('db');

	$db = & $GLOBALS['db'];

	$fifo = $db->getResults(
		sprintf(
			"SELECT " .
				"spotted.spotted_ID, " .
				"spotted.spotted_message, " .
				"spotter.spotter_ID, " .
				"spotter.spotter_chat_id " .
			"FROM {$db->getTables('fifo', 'spotted', 'spotter')} " .
			"WHERE " .
				"fifo.spotted_ID = spotted.spotted_ID AND " .
				"fifo.spotter_ID = spotter.spotter_ID " .
			"ORDER BY spotted.spotted_datetime ASC " .
			"LIMIT %d",
			$n
		),
		'Fifo'
	);

	$n = count($fifo);

	// First delete from the FIFO
	for($i=0; $i<$n; $fifo[$i++]->deleteFifoRowFromDB() );

	// Then send messages
	for($i=0; $i<$n; $fifo[$i++]->sendFifoRowViaTelegram() );

	return $n;
}

function exec_curl_request($handle) {
	$response = curl_exec($handle);

	if ($response === false) {
		$errno = curl_errno($handle);
		$error = curl_error($handle);
		error_log("Curl returned error $errno: $error\n");
		curl_close($handle);
		return false;
	}

	$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
	curl_close($handle);

	if($http_code >= 500) {
		// do not wat to DDOS server if something goes wrong
		sleep(10);
		return false;
	} elseif($http_code !== 200) {
		$response = json_decode($response, true);
		error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
		if($http_code === 401) {
			throw new Exception("Invalid access token provided");
		}
		return false;
	} else {
		$response = json_decode($response, true);
		if( isset($response['description']) ) {
			error_log("Request was successfull: {$response['description']}\n");
		}
		$response = $response['result'];
	}

	return $response;
}

function apiRequest($method, $parameters) {
	if( ! is_string($method) ) {
		error_log("Method name must be a string\n");
		return false;
	}

	if( ! $parameters ) {
		$parameters = [];
	} elseif( ! is_array($parameters) ) {
		error_log("Parameters must be an array\n");
		return false;
	}

	foreach($parameters as $key => &$val) {
		// Encoding to JSON array parameters, for example reply_markup
		if( ! is_numeric($val) && ! is_string($val) ) {
			$val = json_encode($val);
		}
	}
	$url = API_URL . $method . '?' . http_build_query($parameters);

	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($handle, CURLOPT_TIMEOUT, 60);

	return exec_curl_request($handle);
}

function is_command($text, $cmd) {
	return strpos($text, $cmd) !== false;
}

function refresh_admin_keyboard($chat_id, $text) {
	expect('db');
	$db = & $GLOBALS['db'];

	$keyboard = [];
	$tojudge = $db->getResults("SELECT spotted_ID, spotted_message FROM {$db->getTable('spotted')} WHERE spotted_approved <> 1 ORDER BY spotted_datetime ASC LIMIT 30", 'Spotted');

	foreach($tojudge as $value) {
		$keyboard[] = [ str_truncate($value->spotted_message, 200) ];
		$keyboard[] = [ "Pubblica " . $value->spotted_ID, "Elimina " . $value->spotted_ID ];
	}
	$keyboard[] = ["Termine lista"];

	apiRequest('sendMessage', [
		'chat_id' => WANZO,
		'text' => $text,
		'reply_markup' => [
			'keyboard' => $keyboard,
			'resize_keyboard' => true
		]
	] );
}
