<#1>
<?php
if(!$ilDB->tableExists('cron_crnhk_vedaimp_us'))
{
	$ilDB->createTable('cron_crnhk_vedaimp_us',
		array(
			'oid'	=>
				array(
					'type'		=> 'text',
					'length'	=> 64,
					'notnull'	=> false
				),
			'login'	=>
				array(
					'type'		=> 'text',
					'length'	=> 64,
					'notnull'	=> false
				),
			'status_pwd'	=>
				array(
					'type'		=> 'integer',
					'length'	=> 1,
					'notnull'	=> true
				),
			'status_created'	=>
				array(
					'type'		=> 'integer',
					'length'	=> 1,
					'notnull'	=> true
				),
			'import_failure'	=>
				array(
					'type'		=> 'integer',
					'length'	=> 1,
					'notnull'	=> true
				)
		)
	);
	$ilDB->addPrimaryKey('cron_crnhk_vedaimp_us',['oid']);
}
?>
<#2>
<?php
// do nothing
?>
<#3>
<?php
if(!$ilDB->tableExists('cron_crnhk_vedaimp_crs'))
{
	$ilDB->createTable('cron_crnhk_vedaimp_crs',
		array(
			'oid'	=>
				array(
					'type'		=> 'text',
					'length'	=> 64,
					'notnull'	=> false
				),
			'switchp'	=>
				array(
					'type'		=> 'integer',
					'length'	=> 4,
					'notnull'	=> true,
					'default'   => 0
				),
			'switcht'	=>
				array(
					'type'		=> 'integer',
					'length'	=> 4,
					'notnull'	=> true,
					'default'   => 0
				),
			'status_created'	=>
				array(
					'type'		=> 'integer',
					'length'	=> 2,
					'notnull'	=> true
				)
		)
	);
	$ilDB->addPrimaryKey('cron_crnhk_vedaimp_crs',['oid']);
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('cron_crnhk_vedaimp_crs', 'obj_id'))
{
	$ilDB->addTableColumn(
			'cron_crnhk_vedaimp_crs',
			'obj_id',
			[
				"type" => "integer",
				"notnull" => true,
				"length" => 4,
				"default" => 0
			]
	);
}
?>


