<?php

namespace NeuronAI\Tools\Toolkits\MySQL;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Toolkits\AbstractToolkit;
use NeuronAI\Tools\Toolkits\ToolkitInterface;
use PDO;

class MySQLToolkit extends AbstractToolkit
{
    public function __construct(protected PDO $pdo)
    {
    }

    public function tools(): array
    {
        return [
            MySQLSchemaTool::make($this->pdo),
            MySQLSelectTool::make($this->pdo),
            MySQLWriteTool::make($this->pdo),
        ];
    }
}
