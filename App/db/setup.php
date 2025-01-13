<?php
namespace App\Db;
use App\Plugins\Di\Injectable;

class Setup extends Injectable
{
    public function runSetup()
    {
        $schemaFile = __DIR__ . '/database_schema.sql';
        $query = file_get_contents($schemaFile);
        $this->db->executeQuery($query, []);
    }
}
