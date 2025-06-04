<?php

namespace NeuronAI\Tools\Toolkits\MySQL;

use NeuronAI\Tools\Toolkits\ToolkitInterface;
use PDO;

class MySQLToolkit implements ToolkitInterface
{
    public function __construct(protected PDO $pdo)
    {
    }

    public function tools(): array
    {
        return [
            MySQLSchemaTool::make($this->pdo),
            MySQLSelectTool::make($this->pdo),
        ];
    }
}
