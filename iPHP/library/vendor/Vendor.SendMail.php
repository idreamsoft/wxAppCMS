<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) iiiPHP.com. All rights reserved.
 *
 * @author iPHPDev <master@iiiphp.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.1.0
 */
defined('iPHP') OR exit('What are you doing?');
defined('iPHP_LIB') OR exit('iPHP vendor need define iPHP_LIB');
iPHP::import(iPHP_LIB . '/PHPMailer/PHPMailerAutoload.php');

function Sendmail($config) {
	if (empty($config)) {
		return false;
	}

	$mail = new PHPMailer();
	$mail->SetLanguage('zh_cn', iPHP_LIB . '/PHPMailer/language/');
	$mail->IsHTML(true);
	$mail->IsSMTP(); // telling the class to use SMTP

	$mail->CharSet = 'utf-8';
	$mail->AltBody = 'text/html'; // optional, comment out and test
	$mail->SMTPDebug = 0; // enables SMTP debug information (for testing)
	// 1 = errors and messages
	// 2 = messages only
	$mail->SMTPAuth = true; // enable SMTP authentication
	$mail->SMTPSecure = $config['secure']; // sets the prefix to the servier
	$mail->Host = $config['host']; // sets GMAIL as the SMTP server
	$mail->Port = $config['port']; // set the SMTP port for the GMAIL server
	$mail->Username = $config['username']; // GMAIL username
	$mail->Password = $config['password']; // GMAIL password
	$mail->SetFrom($config['setfrom'], $config['title']);
	$mail->AddReplyTo($config['replyto'], $config['title']);
	$mail->Subject = $config['subject'];
	$mail->MsgHTML($config['body']);
	foreach ((array) $config['address'] as $key => $value) {
		$mail->AddAddress($value[0], $value[1]);
	}

	if (!$mail->Send()) {
		return "Mailer Error: " . $mail->ErrorInfo;
	} else {
		return true;
	}
}
