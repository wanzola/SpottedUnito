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

// This page is the bot itself

require 'load.php';

$content = file_get_contents('php://input');
$update = json_decode($content, true);

// Die if something doesn't go as expected
isset( $update['message'], $update['message']['text'] )
	|| die("What? A lamer? Do the fuck you want.");

if( isset($update['message']['document']) ) {

	// It's a document!

	apiRequest('sendMessage', [
		'chat_id' => $update['message']['chat']['id'],
		'text' => "Il <b>file_id</b> del documento inviato è: ".$update['message']['document']['file_id'],
		'parse_mode' => 'HTML',
		'disable_web_page_preview' => true,
		'reply_to_message_id' => $update['message']['message_id'],
		'reply_markup' => ""
	] );
} elseif( isset($update['inline_query']) ) {

	// It's an inline query!

	$id = $update['inline_query']['id'];
	$from = $update['inline_query']['from'];
	$query = $update['inline_query']['query'];
	if( is_command($query, 'chat id') ) {
		apiRequest('answerInlineQuery', [
			'inline_query_id' => $id,
			'results' => [ [
				'type' => 'article',
				'id' => 0,
				'title' => "Premi qua e scopri il tuo chat id",
				'message_text' => "Il tuo chat id è: ".$from["id"],
				'parse_mode' => 'HTML'
			] ],
			'cache_time' => 0
		] );
	} elseif( is_command($query, 'presenta') ) {
		apiRequest('answerInlineQuery', [
			'inline_query_id' => $id,
			'results' => [ [
					'type' => 'article',
					'id' => 0,
					'title' => "Premi qua per presentare questo bot a qualcuno (volgare)",
					'message_text' => "Vuoi fare un complimento a quella strafiga che è in aula studio con te? Vuoi insultare qualcuno? Vuoi far sapere a tutti il tuo record di seghe al giorno? E vorresti farlo in modo anonimo magari? Bene, con <a href=\"telegram.me/spottedunitobot\">SpottedUnitoBot</a> fai questo ed altro.",
					'parse_mode' => 'HTML'
				], [
				'type' => 'article',
				'id' => 1,
				'title' => "Premi qua per presentare questo bot a qualcuno (educato)",
				'message_text' => "Prova anche tu il nuovo bot per lo Spotted di Unito, <a href=\"telegram.me/spottedunitobot\">CLICCA QUI</a>",
				'parse_mode' => 'HTML'
			] ],
			'cache_time' => 0
		] );
	}
} else {

	// It's a message!

	$message = & $update['message'];
	$text    = trim( $message['text'] );

	if( is_command($text, '/start') ) {

		// Exists?
		$exists = $db->getRow( sprintf(
			"SELECT 1 FROM {$db->getTable('spotter')} " .
			"WHERE spotter_chat_ID = '%d'",
			$message['chat']['id']
		) );

		// Insert if not exists
		$exists || $db->insertRow('spotter', [
			new DBCol('spotter_chat_id',  $message['chat']['id'], 'd'),
			new DBCol('spotter_datetime', 'NOW()',                '-')
		] );

		apiRequest('sendMessage', [
			'chat_id' => $message['chat']['id'],
			'text' => _("Ecco... /ciclo, /mamma o /spotted?")
		] );
	} elseif( is_command($text, '/ciclo') ) {
		apiRequest('sendMessage', [
			'chat_id' => $message['chat']['id'],
			'text' => _("Non ho cicli mestruali. Screanzato!")
		] );
	} elseif( is_command($text, '/mamma') ) {
		apiRequest('sendMessage', [
			'chat_id' => $message['chat']['id'],
			'text' => _("TUA MADRE?")
		] );
	} elseif( is_command($text, '/spotted') ) {
		$spotted = ltrim( str_replace('/spotted', '', $text) );

		if( strlen( $spotted ) === 0 ) {
			apiRequest('sendMessage', [
				'chat_id' => $message['chat']['id'],
				'text' => _("Questo comando serve a mandare messaggi accazzo.\n Esempio:\n\n/spotted Dio Cane!")
			] );
		} else {
			$spotted = str_truncate($spotted, 300, '...');

			$db->insertRow('spotted', [
				new DBCol('spotted_datetime', 'NOW()', '-'),
				new DBCol('spotted_message', $spotted, 's'),
				new DBCol('spotted_chat_id', $message['chat']['id'], 'd')
			] );
			$spotted_ID = $db->getLastInsertedID();

			$spotters = $db->getResults("SELECT spotter_ID FROM {$db->getTable('spotter')}", 'Spotter');
			$fifo_rows = [];
			foreach($spotters as $spotter) {
				$fifo_rows[] = [$spotted_ID, $spotter->spotter_ID];
			}
			$db->insert('fifo', [
					'spotted_ID' => 'd',
					'spotter_ID' => 'd'
				],
				$fifo_rows
			);

			apiRequest('sendMessage', [
				'chat_id' => $message['chat']['id'],
				'text' => sprintf(
					_("Sta roba sta per esser mandata a *%d* persone:\n- «%s»\n\nA me pare na gran cazzata... Però sarà fatto fra pochi istanti."),
					count( $spotters ),
					$spotted
				),
				'parse_mode' => 'markdown'
			] );
		}
	} else {
		apiRequest('sendMessage', [
			'chat_id' => $message['chat']['id'],
			'text' => _("Porcoddio quanta ignoranza a non saper usare un bottino.. Quali cazzo di problemi hai? Sei un aborto.")
		] );
	}

}

// At the end... Try sending some messages...
spotted_fifo(4);
