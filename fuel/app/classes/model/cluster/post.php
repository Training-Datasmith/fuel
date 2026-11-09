<?php

class Model_Cluster_Post extends \Orm\Model
{
	protected static $_table_name = 'cluster_posts';

	protected static $_properties = array(
		'id',
		'title',
	);

	protected static $_has_many = array(
		'comments' => array(
			'model_to' => 'Model_Cluster_Comment',
			'key_from' => 'id',
			'key_to' => 'post_id',
		),
	);
}
