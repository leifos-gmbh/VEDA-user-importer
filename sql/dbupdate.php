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

<#5>
<?php
// nothing
?>
?>
<#6>
<?php
if(!$ilDB->tableExists('cron_crnhk_vedaimp_seg')) {
	$ilDB->createTable(
		'cron_crnhk_vedaimp_seg',
		[
			'oid' =>
				[
					'type' => 'text',
					'length' => 64,
					'notnull' => false
				],
			'type' =>
				[
					'type' => 'text',
					'length' => 64,
					'notnull' => false
				]
		]
	);
	$ilDB->addPrimaryKey('cron_crnhk_vedaimp_seg',['oid','type']);
}
?>
<#7>
<?php
if (!$ilDB->tableColumnExists('cron_crnhk_vedaimp_crs', 'modified')) {
    $ilDB->addTableColumn(
        'cron_crnhk_vedaimp_crs',
        'modified',
        [
            "type" => ilDBConstants::T_INTEGER,
            "notnull" => true,
            "length" => 8,
            "default" => 0
        ]
    );
}
?>
<#8>
<?php
if (!$ilDB->tableColumnExists('cron_crnhk_vedaimp_crs', 'type')) {
    $ilDB->addTableColumn(
        'cron_crnhk_vedaimp_crs',
        'type',
        [
            "type" => ilDBConstants::T_INTEGER,
            "notnull" => true,
            "length" => 2,
            "default" => 0
        ]
    );
}
?>
<#9>
<?php
if ($ilDB->tableExists('adv_md_values_text') &&
    $ilDB->tableExists('adv_md_values_ltext')
) {
    // inserts all values from adv_md_values_text into adv_md_values_ltext WITHOUT
    // adv_md_values_ltext.value_index, ignoring duplicate entries.
    $ilDB->manipulate("
        INSERT IGNORE INTO adv_md_values_ltext (field_id, obj_id, `value`, value_index, disabled, sub_type, sub_id)
            SELECT val.field_id, val.obj_id, val.value, '', val.disabled, val.sub_type, val.sub_id
                FROM adv_md_values_text AS val
        ;
    ");

    // inserts all values from adv_md_values_text into adv_md_values_ltext WITH
    // adv_md_values_ltext.value_index, whereas the value_index will be the default
    // lang-code of adv_md_field_int because the old table didn't store this information.
    $ilDB->manipulate("
        INSERT IGNORE INTO adv_md_values_ltext (field_id, obj_id, `value`, value_index, disabled, sub_type, sub_id)
            SELECT val.field_id, val.obj_id, val.value, field.lang_code, val.disabled, val.sub_type, val.sub_id
                FROM adv_md_values_text AS val
                JOIN adv_md_field_int AS field ON field.field_id = val.field_id
        ;
    ");
}
?>
