<?
// Main Project Class
class projectClass {
    var $emailSMTPServer;
    var $emailSMTPUsername; 
    var $emailSMTPPassword;
	function mail($fromText, $fromEmail, $toText, $toEmail, $subject, $body) {
		$headers  = "From: =?koi8-r?B?".base64_encode(convert_cyr_string($fromText, "w", "k"))."?= <".$fromEmail.">\n";
		$headers .= "To: =?koi8-r?B?".base64_encode(convert_cyr_string($toText, "w", "k"))."?= <".$toEmail.">\n";
		$headers .= "Subject: =?koi8-r?B?".base64_encode(convert_cyr_string($subject, "w", "k"))."?= \n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "X-Priority: 3\n";

		$headers .= "X-Spam-Status: No, score=-100.3 required=5.0\n";
		$headers .= "X-Spam-Checker-Version: SpamAssassin 3.1.0 (2005-09-13) on [HOST]\n";
		$headers .= "X-Virus-Scanned: ClamAV version 0.88.3, clamav-milter version 0.88.3 on [HOST]\n";
		$headers .= "X-Virus-Status: Clean\n";
		$headers .= "Content-Type: text/html; charset=\"koi8-r\"\n";
		$headers .= "Content-Transfer-Encoding: base64\n";
		$headers .= "Content-Disposition: inline\n";

		$sendBody  = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
		$sendBody .= "<html><head>\n";
		$sendBody .= "<meta http-equiv=\"Content-Type content=\"text/html; charset=\"koi8-r\">\n";
		$sendBody .= "<style></style>\n";
		$sendBody .= "<body bgcolor=\"#ffffff\">\n";
		$sendBody .= convert_cyr_string($body, "w", "k");
		$sendBody .= "</body>";
		$sendBody  = base64_encode($sendBody)."\r\n.\r\n";

		// Sending email with SMTP Auth
		$sID = fsockopen($this->emailSMTPServer, 25);
		fputs($sID, "EHLO ".getenv("HTTP_HOST")."\n");
		fputs($sID, "AUTH LOGIN\n");
		fputs($sID, base64_encode($this->emailSMTPUsername)."\n");
		fputs($sID, base64_encode($this->emailSMTPPassword)."\n");
		fputs($sID, "MAIL FROM: ".$fromEmail."\n");
		fputs($sID, "RCPT TO: ".$toEmail."\n");
		fputs($sID, "DATA\n");
		fputs($sID, $headers.wordwrap($sendBody, 66, "\n", true));
		fputs($sID, "QUIT");
		fclose($sID);






	}
}
?>