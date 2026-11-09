<?php

use PHPUnit\Framework\Attributes\Group;

#[Group('Packages')]
#[Group('Auth')]
class Tests_Packages_Auth extends ClusterTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		static::wipeClusterDatabase();
	}

	public function test_simpleauth_user_login_and_access()
	{
		ClusterSqliteAuthSchema::installSimpleauth();
		$this->bootAuthDriver('Simpleauth');

		$id = \Auth\Auth::create_user('ada', 'correct-horse', 'ada@example.test', 1);
		$this->assertIsNumeric($id);
		$this->assertTrue(\Auth\Auth::login('ada', 'correct-horse'));
		$this->assertSame('ada', \Auth\Auth::get_screen_name());
		$this->assertSame('Users', \Auth\Auth_Group_Simplegroup::instance()->get_name());
		$this->assertTrue(\Auth\Auth::has_access('comments.read'));
		$this->assertFalse(\Auth\Auth::login('ada', 'nope'));
		\Auth\Auth::logout();
		$this->assertFalse(\Auth\Auth::check());

		$this->assertIncludedPackageFile('fuel/packages/auth/classes/auth/login/simpleauth.php');
	}

	public function test_ormauth_user_login()
	{
		ClusterSqliteAuthSchema::installOrmauth();
		$this->bootAuthDriver('Ormauth');

		$id = \Auth\Auth::create_user('ormada', 'correct-horse', 'ormada@example.test', 1);
		$this->assertIsNumeric($id);
		$this->assertTrue(\Auth\Auth::login('ormada', 'correct-horse'));
		$this->assertFalse(\Auth\Auth::login('ormada', 'nope'));

		$this->assertIncludedPackageFile('fuel/packages/auth/classes/auth/login/ormauth.php');
		$this->assertIncludedPackageFile('fuel/packages/orm/classes/model.php');
	}
}
