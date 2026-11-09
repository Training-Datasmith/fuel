<?php

use PHPUnit\Framework\Attributes\Group;

#[Group('Packages')]
#[Group('Orm')]
class Tests_Packages_Orm extends ClusterTestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		static::resetClusterDatabase();
		\DB::query('CREATE TABLE cluster_posts (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8')->execute();
		\DB::query('CREATE TABLE cluster_comments (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			body TEXT NOT NULL,
			post_id INT UNSIGNED NOT NULL,
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8')->execute();
	}

	public function test_post_comment_relation_and_query()
	{
		$post = \Model_Cluster_Post::forge(array('title' => 'Cluster post'));
		$post->comments[] = \Model_Cluster_Comment::forge(array('body' => 'Cluster comment'));
		$this->assertTrue($post->save());

		$posts = \Model_Cluster_Post::find('all', array('related' => array('comments')));
		$this->assertCount(1, $posts);
		$post = reset($posts);
		$this->assertSame('Cluster post', $post->title);
		$this->assertCount(1, $post->comments);
		$comment = reset($post->comments);
		$this->assertSame('Cluster comment', $comment->body);

		$empty = \Model_Cluster_Post::find('all', array(
			'where' => array(array('title', '=', 'missing title')),
		));
		$this->assertSame(array(), $empty);

		$this->assertIncludedPackageFile('fuel/packages/orm/classes/model.php');
		$this->assertIncludedPackageFile('fuel/packages/orm/classes/hasmany.php');
		$this->assertIncludedPackageFile('fuel/packages/orm/classes/belongsto.php');
		$this->assertIncludedPackageFile('fuel/packages/orm/classes/query.php');
	}
}
