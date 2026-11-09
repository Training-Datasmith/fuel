<?php

use PHPUnit\Framework\Attributes\Group;

#[Group('Packages')]
#[Group('Email')]
class Tests_Packages_Email extends ClusterTestCase
{
	public function test_noop_driver_builds_and_logs_message()
	{
		$email = \Email\Email::forge();
		$email->from('app@example.test', 'App');
		$email->to('ada@example.test', 'Ada');
		$email->subject('Cluster subject');
		$email->body('Hello cluster body');
		$email->string_attach('attachment-bytes', 'note.txt', null, false, 'text/plain');

		$this->assertSame('Cluster subject', $email->get_subject());
		$to = $email->get_to();
		$this->assertArrayHasKey('ada@example.test', $to);
		$this->assertSame('Hello cluster body', $email->get_body());
		$this->assertTrue($email->send());

		$log = $this->readApplicationLogContents();
		$this->assertStringContainsString('Cluster subject', $log);
		$this->assertStringContainsString('Hello cluster body', $log);
		$this->assertStringContainsString('note.txt', $log);
		$this->assertStringContainsString(base64_encode('attachment-bytes'), $log);
		$this->assertDoesNotMatchRegularExpression('/smtp\.|mailgun|mandrill/i', $log);

		$this->assertIncludedPackageFile('fuel/packages/email/classes/email/driver/noop.php');
	}
}
