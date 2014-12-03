<?php

/******************************************************************************/

// load the config and prepare to process
include('load_process_emails.php');

/* begin */
$state = 'LOAD_EMAILS';
while ($state <> 'END') {
    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->write('STATE:' . $state, 'INFO');
    }
    switch ($state) {
		
	/**********************************************************************/
    /*                          LOAD_EMAILS 							  */
    /* List the records to proceed       								  */
    /**********************************************************************/
    case 'LOAD_EMAILS' :
		$query = "SELECT * FROM " . EMAILS_TABLE
			. " WHERE email_status = 'W' and send_date is NULL";
		Bt_doQuery($GLOBALS['db'], $query);
		$totalEmailsToProcess = $GLOBALS['db']->nb_result();
		$currentEmail = 0;
		if ($totalEmailsToProcess === 0) {
			Bt_exitBatch(0, 'No e-mails to process');
        }
		$GLOBALS['logger']->write($totalEmailsToProcess . ' e-mails to proceed.', 'INFO');
		$GLOBALS['emails'] = array();
		while ($emailRecordset = $GLOBALS['db']->fetch_object()) {
			$GLOBALS['emails'][] = $emailRecordset;
		}
		$state = 'SEND_AN_EMAIL';
    break;
		
	/**********************************************************************/
    /*                          SEND_AN_EMAIL	 		          	          */
    /* Load parameters and send an e-mail                                    */
    /**********************************************************************/
    case 'SEND_AN_EMAIL' :
		if($currentEmail < $totalEmailsToProcess) {
			$email = $GLOBALS['emails'][$currentEmail];
			$GLOBALS['mailer'] = new htmlMimeMail();
			$GLOBALS['mailer']->setSMTPParams(
				$host = (string)$mailerParams->smtp_host, 
				$port = (string)$mailerParams->smtp_port,
				$helo = (string)$mailerParams->domains,
				$auth = filter_var($mailerParams->smtp_auth, FILTER_VALIDATE_BOOLEAN),
				$user = (string)$mailerParams->smtp_user,
				$pass = (string)$mailerParams->smtp_password
				);
		//Composing email	
			//--> Set from
			$userInfo = $users->get_user($email->user_id);
						$GLOBALS['logger']->write("Sending e-mail from : " 
				. '"' . $userInfo['firstname'].' ' .$userInfo['lastname'] 
				. '" <'.$userInfo['mail'].'>', 'INFO');
				
                        $GLOBALS['mailer']->setFrom($userInfo['firstname'].' '
				. $userInfo['lastname'].' <'.$userInfo['mail'].'> ');

			//
			$GLOBALS['logger']->write("Sending e-mail to : " . $email->to_list, 'INFO');
			if (!empty($email->cc_list))$GLOBALS['logger']->write("Copy e-mail to : " . $email->cc_list, 'INFO');
			if (!empty($email->cci_list))$GLOBALS['logger']->write("Copy invisible e-mail to : " . $email->cci_list, 'INFO');
			
			//--> Set the return path
			$GLOBALS['mailer']->setReturnPath($userInfo['mail']);
			//--> To
			$to = array();
			$to = explode(',', $email->to_list);
			//--> Cc
			if (!empty($email->cc_list)) {
				$GLOBALS['mailer']->setCc($email->cc_list);
			}
			//--> Cci
			if (!empty($email->cci_list)) {
				$GLOBALS['mailer']->setBcc($email->cci_list);
			}
			//--> Set subject
			$GLOBALS['logger']->write("Subject : " . $email->email_object, 'INFO');
			$GLOBALS['mailer']->setSubject($email->email_object);
			//--> Set body: Is Html/raw text ?
			if ($email->is_html == 'Y') {
				$body = str_replace('###', ';', $email->email_body);
				$body = str_replace('___', '--', $body);
				$body = $sendmail_tools->rawToHtml($body);
				$GLOBALS['mailer']->setHtml($body);
			} else {
				$body = $sendmail_tools->htmlToRaw($email->email_body);
				$GLOBALS['mailer']->setText($body);
			}
			//--> Set charset
			$GLOBALS['mailer']->setTextCharset($GLOBALS['charset']);
			$GLOBALS['mailer']->setHtmlCharset($GLOBALS['charset']);
			$GLOBALS['mailer']->setHeadCharset($GLOBALS['charset']);
			
			//--> Set attachments
				//Res master
				if ($email->is_res_master_attached == 'Y') {
					$GLOBALS['logger']->write("Set attachment on res master : " . $email->res_id, 'INFO');

					//Get file from docserver
					$resFile = $sendmail_tools->getResource($GLOBALS['collections'], $email->coll_id, $email->res_id);
					//Get file content
					if(is_file($resFile['file_path'])) {
						//Filename
						$resFilename = $sendmail_tools->createFilename($resFile['label'], $resFile['ext']);
						$GLOBALS['logger']->write("Set attachment filename : " . $resFilename, 'INFO');

						//File content
						$file_content = $GLOBALS['mailer']->getFile($resFile['file_path']);
						//Add file
						$GLOBALS['mailer']->addAttachment($file_content, $resFilename, $resFile['mime_type']); 
					}
				}
				
				//Other version of the document
				if (!empty($email->res_version_id_list)) {
                    $version = explode(',', $email->res_version_id_list);
					foreach($version as $version_id) {
						$GLOBALS['logger']->write("Set attachment for version : " . $version_id, 'INFO');
						$versionFile = $sendmail_tools->getVersion(
								$GLOBALS['collections'],
								$email->coll_id, 
								$email->res_id,
								$version_id
							);
						if(is_file($versionFile['file_path'])) {
							//Filename
							$versionFilename = $sendmail_tools->createFilename($versionFile['label'], $versionFile['ext']);
							$GLOBALS['logger']->write("Set attachment filename for version : " . $versionFilename, 'INFO');

							//File content
							$file_content = $GLOBALS['mailer']->getFile($versionFile['file_path']);
							//Add file
							$GLOBALS['mailer']->addAttachment($file_content, $versionFilename, $versionFile['mime_type']); 
						}
					}
				}
				
				//Res attachment
				if (!empty($email->res_attachment_id_list)) {
                    $attachments = explode(',', $email->res_attachment_id_list);
					foreach($attachments as $attachment_id) {
						$GLOBALS['logger']->write("Set attachment on res attachment : " . $attachment_id, 'INFO');
						$attachmentFile = $sendmail_tools->getAttachment(
								$email->coll_id, 
								$email->res_id,
								$attachment_id
							);
						if(is_file($attachmentFile['file_path'])) {
							//Filename
							$attachmentFilename = $sendmail_tools->createFilename($attachmentFile['label'], $attachmentFile['ext']);
							$GLOBALS['logger']->write("Set attachment filename : " . $attachmentFilename, 'INFO');

							//File content
							$file_content = $GLOBALS['mailer']->getFile($attachmentFile['file_path']);
							//Add file
							$GLOBALS['mailer']->addAttachment($file_content, $attachmentFilename, $attachmentFile['mime_type']); 
						}
					}
                }
				
				//Notes
				if (!empty($email->note_id_list)) {
					$notes = explode(',', $email->note_id_list);
					$noteFile = $sendmail_tools->createNotesFile($email->coll_id, $email->res_id, $notes);
					if(is_file($noteFile['file_path'])) {
						//File content
						$file_content = $GLOBALS['mailer']->getFile($noteFile['file_path']);
						//Add file
						$GLOBALS['mailer']->addAttachment($file_content, $noteFile['filename'], $noteFile['mime_type']); 
					}
				}
			
			//Now send the mail
			$return = $GLOBALS['mailer']->send($to, (string)$mailerParams->type);

			if($return == 1) {
				$exec_result = 'S';
			} else {
				//$GLOBALS['logger']->write("Errors when sending message through SMTP :" . implode(', ', $GLOBALS['mailer']->errors), 'ERROR');
                $GLOBALS['logger']->write("Errors when sending message through SMTP :" . $GLOBALS['mailer']->errors[0].': '.$GLOBALS['mailer']->errors[1], 'ERROR');
				$exec_result = 'E';
			}
			//Update emails table
			$query = "UPDATE " . EMAILS_TABLE 
				. " SET send_date = " . $GLOBALS['db']->current_datetime()
				. ", email_status = '".$exec_result."' "
				. " WHERE email_id = ".$email->email_id;
			$GLOBALS['db']->connect();
			Bt_doQuery($GLOBALS['db'], $query);
			$currentEmail++;
			$state = 'SEND_AN_EMAIL';
		} else {
			$state = 'END';
		}
        break;
	}
}

$GLOBALS['logger']->write('End of process', 'INFO');
Bt_logInDataBase(
    $totalEmailsToProcess, 0, 'process without error'
);
$GLOBALS['db']->disconnect();
//unlink($GLOBALS['lckFile']);
exit($GLOBALS['exitCode']);
?>
