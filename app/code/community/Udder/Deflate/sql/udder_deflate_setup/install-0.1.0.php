<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Create our questions table
// Table definitiion
$imageTable = $installer->getConnection()->newTable($installer->getTable('udder_deflate/image'))
    ->addColumn('image_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 9, array(
        'unsigned'       => true,
        'nullable'       => false,
        'primary'        => true,
        'identity'       => true,
        'auto_increment' => true
    ), 'Auto increment ID for image')
    ->addColumn('deflate_hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true
    ), 'Handshake')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_TEXT, false, array(
        'nullable' => true
    ), 'Path to image')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, false, array(
        'nullable' => false
    ), 'File name of image')
    ->addColumn('file_path_hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, 52, array(
        'nullable' => true
    ), 'MD5 hash of path and name')
    ->addColumn('file_sha1', Varien_Db_Ddl_Table::TYPE_VARCHAR, 40, array(
        'nullable' => true
    ), 'Store the sha1 of the file so we don\'t attempt to compress it twice')
    ->addColumn('magento_type', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
        'nullable' => true
    ), 'The location type of image for Magento')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
        'nullable' => true
    ), 'The current status of the image')
    ->addColumn('compression_type', Varien_Db_Ddl_Table::TYPE_TEXT, 10, array(
        'nullable' => true
    ), 'The compression type used on this image')
    ->addColumn('original_url', Varien_Db_Ddl_Table::TYPE_TEXT, false, array(
        'nullable' => true
    ), 'The original URL hosted by Deflate')
    ->addColumn('original_size', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
        'nullable' => true
    ), 'The original size of the image')
    ->addColumn('deflated_url', Varien_Db_Ddl_Table::TYPE_TEXT, false, array(
        'nullable' => true
    ), 'The original URL hosted by Deflate')
    ->addColumn('deflated_size', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
        'nullable' => true
    ), 'The original size of the image')
    ->addColumn('difference', Varien_Db_Ddl_Table::TYPE_INTEGER, 12, array(
        'nullable' => true
    ), 'The difference size of the image')
    ->addColumn('compressed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
    ), 'Compressed At')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
    ), 'Created At')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
    ), 'Updated At')
    ->setComment('Deflate Images table');

// Actually create the table
$installer->getConnection()->createTable($imageTable);

// Create our unique index across path and name
$installer->getConnection()->addIndex(
    $installer->getTable('udder_deflate/image'),
    $installer->getIdxName(
        'udder_deflate/image',
        array(
            'file_path_hash'
        ),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array(
        'file_path_hash'
    ),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

// Create our "secret" handshake token, this is used to verify the response back from the deflate API
$config = Mage::getModel('core/config');
$config->saveConfig('udder_deflate/general/handshake', md5(uniqid("DEFLATE_", true)), 'default', 0);

// Store a flag saying the module hasn't yet been setup, this way we can let the user know configuration is needed
if (!Mage::getStoreConfig('udder_deflate/general/api_key')) {
    $config->saveConfig('udder_deflate/general/setup', 0, 'default', 0);
}

// Setup is complete!
$installer->endSetup();