<?php
/**
 * SQLite table layout used because package auth migrations emit MySQL DDL (see dependency-failures.md).
 */

class ClusterSqliteAuthSchema
{
	public static function installSimpleauth()
	{
		\DB::query('CREATE TABLE users (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			username VARCHAR(50) NOT NULL,
			password VARCHAR(255) NOT NULL,
			salt CHAR(16) NOT NULL DEFAULT "",
			`group` INTEGER NOT NULL DEFAULT 1,
			email VARCHAR(255) NOT NULL,
			last_login VARCHAR(25) NOT NULL DEFAULT "",
			login_hash VARCHAR(255) NOT NULL DEFAULT "",
			profile_fields TEXT NOT NULL,
			created_at INTEGER NOT NULL DEFAULT 0,
			updated_at INTEGER NOT NULL DEFAULT 0
		)')->execute();
		\DB::query('CREATE UNIQUE INDEX username ON users (username, email)')->execute();
	}

	public static function installOrmauth()
	{
		\DB::query('CREATE TABLE users (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			username VARCHAR(50) NOT NULL,
			password VARCHAR(255) NOT NULL,
			salt CHAR(16) NOT NULL DEFAULT "",
			group_id INTEGER NOT NULL DEFAULT 1,
			email VARCHAR(255) NOT NULL,
			last_login VARCHAR(25) NOT NULL DEFAULT "",
			previous_login VARCHAR(25) NOT NULL DEFAULT "0",
			login_hash VARCHAR(255) NOT NULL DEFAULT "",
			user_id INTEGER NOT NULL DEFAULT 0,
			profile_fields TEXT,
			created_at INTEGER NOT NULL DEFAULT 0,
			updated_at INTEGER NOT NULL DEFAULT 0
		)')->execute();
		\DB::query('CREATE UNIQUE INDEX ormauth_username ON users (username, email)')->execute();

		\DB::query('CREATE TABLE users_groups (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name VARCHAR(255) NOT NULL,
			user_id INTEGER NOT NULL DEFAULT 0,
			created_at INTEGER NOT NULL DEFAULT 0,
			updated_at INTEGER NOT NULL DEFAULT 0
		)')->execute();
		\DB::query('CREATE TABLE users_group_roles (
			group_id INTEGER NOT NULL,
			role_id INTEGER NOT NULL,
			PRIMARY KEY (group_id, role_id)
		)')->execute();
		\DB::query('CREATE TABLE users_group_permissions (
			group_id INTEGER NOT NULL,
			"perms_id" INTEGER NOT NULL,
			PRIMARY KEY (group_id, perms_id)
		)')->execute();
		\DB::query('CREATE TABLE users_roles (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			name VARCHAR(255) NOT NULL,
			filter VARCHAR(1) NOT NULL DEFAULT "",
			user_id INTEGER NOT NULL DEFAULT 0,
			created_at INTEGER NOT NULL DEFAULT 0,
			updated_at INTEGER NOT NULL DEFAULT 0
		)')->execute();
		\DB::query('CREATE TABLE users_role_permissions (
			role_id INTEGER NOT NULL,
			"perms_id" INTEGER NOT NULL,
			PRIMARY KEY (role_id, perms_id)
		)')->execute();
		\DB::query('CREATE TABLE users_permissions (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			area VARCHAR(25) NOT NULL,
			permission VARCHAR(25) NOT NULL,
			description VARCHAR(255) NOT NULL DEFAULT "",
			user_id INTEGER NOT NULL DEFAULT 0,
			created_at INTEGER NOT NULL DEFAULT 0,
			updated_at INTEGER NOT NULL DEFAULT 0
		)')->execute();
		\DB::query('CREATE UNIQUE INDEX permission ON users_permissions (area, permission)')->execute();
		\DB::query('CREATE TABLE users_metadata (
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			parent_id INTEGER NOT NULL DEFAULT 0,
			key VARCHAR(20) NOT NULL,
			value VARCHAR(100),
			user_id INTEGER NOT NULL DEFAULT 0,
			created_at INTEGER NOT NULL DEFAULT 0,
			updated_at INTEGER NOT NULL DEFAULT 0
		)')->execute();
		\DB::query('CREATE TABLE users_user_roles (
			user_id INTEGER NOT NULL,
			role_id INTEGER NOT NULL,
			PRIMARY KEY (user_id, role_id)
		)')->execute();
		\DB::query('CREATE TABLE users_user_permissions (
			user_id INTEGER NOT NULL,
			"perms_id" INTEGER NOT NULL,
			PRIMARY KEY (user_id, perms_id)
		)')->execute();

		list($group_id) = \DB::insert('users_groups')->set(array('name' => 'Users'))->execute();
		list($role_id) = \DB::insert('users_roles')->set(array('name' => 'user', 'filter' => ''))->execute();
		\DB::insert('users_group_roles')->set(array('group_id' => $group_id, 'role_id' => $role_id))->execute();
	}
}
