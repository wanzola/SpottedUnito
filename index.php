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

if( isset( $update['inline_query'] ) ) {

	// It's an inline query!

	$id    = & $update['inline_query']['id'];
	$from  = & $update['inline_query']['from'];
	$query = & $update['inline_query']['query'];

	if( is_command($query, 'chat id') ) {

		apiRequest('answerInlineQuery', [
			'inline_query_id' => $id,
			'results' => [ [
				'type' => 'article',
				'id' => "2",
				'title' => "Premi qua e scopri il tuo chat id",
				'message_text' => "Il tuo chat id è: " . $from["id"],
				'parse_mode' => 'HTML'
			] ],
			'cache_time' => 0
		] );
	} elseif( is_command($query, 'presenta') ) {

		apiRequest('answerInlineQuery', [
			'inline_query_id' => $id,
			'results' => [ [
				'type' => 'article',
				'id' => "1",
				'title' => "Premi qua per presentare questo bot a qualcuno",
				'message_text' => "Spotted, un fenomeno molto conosciuto ai giorni nostri, " .
					"dove le persone possono inviare degli appelli anonimi, " .
					"ora è arrivato anche su Telegram, per gli <b>studenti di UniTO</b>! " .
					"\n <a href=\"telegram.me/spottedunitobot?start=byinline\">PREMI QUI</a>, e vieni a leggere le confessioni degli studenti!",
				'parse_mode' => 'HTML'
			] ],
			'cache_time' => 0
		] );
	}
} elseif( isset( $update['message']['text'], $update['message']['chat']['id'] ) ) {

	// It's a message!

	$text    = trim( $update['message']['text'] );
	$chat_id = & $update['message']['from']['id'];
	$first_name = & $update['message']['from']['first_name'];
	$last_name = & $update['message']['from']['last__name'];
	$username = & $update['message']['from']['username'];

	if( is_command($text, '/start') ) {

		// Exists?
		$exists = query_row( sprintf(
			"SELECT 1 FROM {$T('spotter')} " .
			"WHERE spotter_chat_ID = %d",
			$update['message']['chat']['id']
		) );

		// Insert if not exists
		$exists || insert_row('spotter', [
			new DBCol('spotter_chat_id',  $update['message']['chat']['id'], 'd'),
			new DBCol('spotter_datetime', 'NOW()', '-')
		] );

		apiRequest('sendMessage', [
			'chat_id' => $update['message']['chat']['id'],
			'text' => "Benvenuto sul bot di <b>Spotted Unito</b>\n\n".
				"Con questo bot, potrai inviare i tuoi appelli o confessioni anonimamente, a tutti coloro che seguono questo bot.\n".
				"Per inviare uno spot, non ti resta altro che scrivere (indifferente se maiuscolo o minuscolo) <code>spotted messaggio</code>, dove al posto di <code>messaggio</code> dovrete scrivere".
				" il testo desiderato. (Es. <code>spotted Un saluto a tutti!</code>)\n\nNB: attualmente non sono supportate le emoticon",
			'parse_mode' => "HTML",
			'disable_web_page_preview' => true
	  	] );
		apiRequest('sendMessage', [
			'chat_id' => $update['message']['chat']['id'],
			'text' => "Un messaggio di conferma apparirà successivamente. Da quel momento, il messaggio, appena ".
				"i moderatori avranno verificato che non contenga parole inappropriate (bestemmie, minacce, offese, ecc...), verrà pubblicato.".
				"\n\nIn caso di necessità, premere su /help , oppure inviare un messaggio con <code>/help messaggio</code>.",
			'parse_mode' => "HTML",
			'disable_web_page_preview' => true
		] );
	} elseif( stripos($text, 'spotted')===0 ) {
		$spotted = ltrim( str_ireplace('spotted', '', $text) );
		if( strlen( $spotted ) === 0 ) {
			apiRequest('sendMessage', [
				'chat_id' => $update['message']['chat']['id'],
				'text' => _("Il comando <code>spotted</code> è esatto. Tuttavia, per inviare uno spot, deve essere seguito da un messaggio.\n".
					"Es. Spotted Chi da l'esame al posto mio domani?"),
				'parse_mode' => 'HTML'
			] );
		} else {
			$spotted = str_truncate($spotted, 1000, '...');

			insert_row('spotted', [
				new DBCol('spotted_datetime', 'NOW()', '-'),
				new DBCol('spotted_message', $spotted, 's'),
				new DBCol('spotted_chat_id', $update['message']['chat']['id'], 'd'),
				new DBCol('spotted_approved', 0, '-') // Not approved!
			] );

			refresh_admin_keyboard($update['message']['chat']['id'], $spotted, $first_name, $last_name, $username);

			$spotters = query_value("SELECT COUNT(*) as count FROM {$T('spotter')}", 'count');
			apiRequest('sendMessage', [
				'chat_id' => $update['message']['chat']['id'],
				'text' => sprintf(
					_("Il messaggio\n<code>".$spotted."</code>\ne' stato acquisito ed ora è in coda di moderazione per esser mandato a <b>%d</b> persone.\n"),
					$spotters
				),
				'parse_mode' => 'HTML'
			] );
		}
	} elseif( is_command($text, 'Pubblica') ) {
		$spotted_ID = (int) trim( str_replace('Pubblica', '', $text) );

		if($spotted_ID) {
			query( sprintf(
				"UPDATE {$T('spotted')} " .
				"SET " .
					"spotted_approved = 1 " .
				"WHERE " .
					"spotted_ID = %d",
				$spotted_ID
			) );
			$spotters = query_results("SELECT spotter_ID FROM {$T('spotter')}", 'Spotter');
			$fifo_rows = [];
			foreach($spotters as $spotter) {
				$fifo_rows[] = [$spotted_ID, $spotter->spotter_ID];
			}
			insert_values('fifo',
				[
					'spotted_ID' => 'd',
					'spotter_ID' => 'd'
				],
				$fifo_rows
			);
		}
		refresh_admin_keyboard($update['message']['chat']['id'], "Messaggio pubblicato");
	} elseif( is_command($text, 'Elimina') ) {
		$spotted_ID = (int) trim( str_replace("Elimina", '', $text) );

		$spotted_ID && query( sprintf(
			"DELETE FROM {$T('spotted')} WHERE spotted_ID = %d",
			$spotted_ID
		) );
		refresh_admin_keyboard($update['message']['chat']['id'], "Messaggio eliminato");
	} elseif( is_command($text, '/help') ) {
		$text = ltrim( str_replace('/help', '', $text) );

		if( strlen( $text ) === 0 ) {
			apiRequest('sendMessage', [
				'chat_id' => $update['message']['chat']['id'],
				'text' => _("Per inviare un messaggio ai programmatori, scrivi <code>/help messaggio</code>.\n".
					"(Es. /help Salve, non riesco a mandare uno spot perche'...)"),
				'parse_mode' => 'HTML'
			] );
		} else {
			apiRequest('sendMessage', [
				'chat_id' => WANZO,
				'text' => _("E' stato richiesto un help con messaggio: ".$text."\nDa: ".$first_name." ".$last_name." @".$username." ".$chat_id),
				'parse_mode' => 'HTML'
			] );
		}
	} elseif( is_command($text, '/messaggio') ) {
		$text = ltrim( str_replace('/messaggio', '', $text ));
		$text =explode('->', $text);
		$messaggio = $text[1];
		$chat_id = $text[0];
		apiRequest('sendMessage', [
			'chat_id' => $chat_id,
			'text' => _("- <b>Messaggio dal team di SpottedUnito</b> -\n\n".$messaggio),
			'parse_mode' => "HTML"
		] );
	} else {
		apiRequest('sendMessage', [
			'chat_id' => $update['message']['chat']['id'],
			'text' => _("Nessun comando disponibile con le parole <code>$text</code>. Digita o clicca su /start per rivedere le istruzioni."),
			'parse_mode' => "HTML"
		] );
	}
}

// At the end... Try sending some messages...
spotted_fifo(3);
