<?php

namespace NeuronAI\Tools\Toolkits\MySQL;

use NeuronAI\Tools\Toolkits\AbstractToolkit;
use PDO;

class MySQLToolkit extends AbstractToolkit
{
    public function __construct(protected PDO $pdo)
    {
    }

    public function provide(): array
    {
        return [
            MySQLSchemaTool::make($this->pdo),
            MySQLSelectTool::make($this->pdo),
            MySQLWriteTool::make($this->pdo),
        ];
    }
}
