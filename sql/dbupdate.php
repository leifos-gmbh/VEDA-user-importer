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
