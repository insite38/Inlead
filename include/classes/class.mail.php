<?php
class mail {
	public $to			= "";
	public $fromText	= "";
	public $fromEmail	= "";
	public $subject 	= "";
	public $body 		= "";

	public function send() {
		// Creating headers
		$header  = "From: ".$this->fromText." <".$this->fromEmail.">\n";
		$header .= "MIME-Version: 1.0\n";
		$header .= "Content-Type: text/plain; charset=utf-8;\n";
		//$header .= "Content-Transfer-Encoding: base64;\n\n";

		$body = $this->body;
		mail($this->to, $this->subject, $body, $header);

		return true;

	}


}

?>
