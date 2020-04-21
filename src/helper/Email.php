<?php

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

class Email {

	public $sender;
	public $recipients;
	public $subject;
	public $body;
	public $html;

	public function __construct(string $sender, $recipients, string $subject, string $html) {
		$this->sender = $sender ?? DEFAULT_SENDER_EMAIL;
		$this->recipients = is_array($recipients) ? $recipients : [$recipients];
		$this->subject = $subject;
		$this->body = strip_tags($html);
		$this->html = $html;
	}

	public function sendMessage() {
		$SesClient = new SesClient([
			'profile' => 'default',
			'version' => '2010-12-01',
			'region'  => 'us-east-1'
		]);

		$result = $SesClient->sendEmail([
			'Destination' => [
				'ToAddresses' => $this->recipients,
			],
			'ReplyToAddresses' => [$this->sender],
			'Source' => $this->sender,
			'Message' => [
				'Body' => [
					'Html' => [
						'Charset' => 'UTF-8',
						'Data' => $this->html,
					],
					'Text' => [
						'Charset' => 'UTF-8',
						'Data' => $this->body,
					],
				],
				'Subject' => [
					'Charset' => 'UTF-8',
					'Data' => $this->subject,
				],
			]
		]);
		
		$messageId = $result['MessageId'];
		
		return $messageId;
	
	}

}






