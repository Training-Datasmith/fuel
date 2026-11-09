<?php

class Model_Cluster_Comment extends \Orm\Model
{
	protected static $_table_name = 'cluster_comments';

	protected static $_properties = array(
		'id',
		'body',
		'post_id',
	);

	protected static $_belongs_to = array(
		'post' => array(
			'model_to' => 'Model_Cluster_Post',
			'key_from' => 'post_id',
			'key_to' => 'id',
		),
	);
}
