<?php
$xpdo_meta_map['Badwords']= array (
  'package' => 'modjochat',
  'version' => '1.1',
  'table' => 'badwords',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'search' => NULL,
    'replace' => NULL,
  ),
  'fieldMeta' => 
  array (
    'search' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
    ),
    'replace' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
    ),
  ),
);
