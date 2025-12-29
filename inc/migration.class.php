<?php
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class PluginProcedimientosMigration extends Migration {
    public function __construct($version = null) {
        parent::__construct($version);
    }

    public function migrate() {
        // Only schema changes here using Migration methods
        // Example for one table:
        $this->addTable('glpi_plugin_procedimientos_accions', [
            'fields' => [
                'id' => ['type' => 'autoincrement', 'primary' => true],
                'name' => ['type' => 'string', 'length' => 255, 'notnull' => true],
                'comment' => ['type' => 'text'],
                'date_mod' => ['type' => 'datetime', 'default' => null],
                'is_recursive' => ['type' => 'bool', 'default' => 0],
                'entities_id' => ['type' => 'integer', 'default' => 0],
                'is_deleted' => ['type' => 'bool', 'default' => 0],
                'plugin_procedimientos_tipoaccions_id' => ['type' => 'integer', 'default' => 0],
                'uuid' => ['type' => 'string', 'length' => 255, 'default' => null],
            ],
            'options' => [
                'engine' => 'InnoDB',
                'charset' => 'utf8mb4',
                'collate' => 'utf8mb4_unicode_ci',
            ],
            'primary' => ['id'],
            'keys' => [
                'name' => ['name'],
                'entities_id' => ['entities_id'],
                'is_recursive' => ['is_recursive'],
                'date_mod' => ['date_mod'],
                'type' => ['plugin_procedimientos_tipoaccions_id'],
            ]
        ]);
        // Repeat addTable for all other tables from install.sql...

        // Add columns if not present (e.g., actiontime)
        $this->addField('glpi_plugin_procedimientos_tareas', 'actiontime', [
            'type' => 'integer',
            'default' => 0,
            'after' => 'tasktemplates_id',
        ]);
    }
}
