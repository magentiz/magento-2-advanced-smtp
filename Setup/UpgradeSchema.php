<?php
/**
 * Copyright © Open Techiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if ($installer->tableExists('mageplaza_smtp_log')) {
            if(! $connection->tableColumnExists('mageplaza_smtp_log', 'error_message')) {
                $connection->addColumn($setup->getTable('mageplaza_smtp_log'), 'error_message', [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => null,
                    'comment'  => 'Error Message'
                ]);
            }

            if(! $connection->tableColumnExists('mageplaza_smtp_log', 'email_template')) {
                $connection->addColumn($setup->getTable('mageplaza_smtp_log'), 'email_template', [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'length'   => 255,
                    'comment'  => 'Email Template'
                ]);
            }
        }

        $installer->endSetup();
    }
}
