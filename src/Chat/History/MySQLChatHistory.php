<?php

declare(strict_types=1);

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\ChatHistoryException;
use PDO;

class MySQLChatHistory extends AbstractChatHistory
{
    public function __construct(
        protected string $thread_id,
        protected PDO $pdo,
        string $table = 'chat_history',
        int $contextWindow = 50000
    ) {
        parent::__construct($contextWindow);
        $this->table = $this->sanitizeTableName($table);
        $this->load();
    }

    protected function load(): void
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE thread_id = :thread_id");
        $stmt->execute(['thread_id' => $this->thread_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->history = $this->deserializeMessages($messages);
    }

    protected function storeMessage(Message $message): ChatHistoryInterface
    {
        $this->updateTable();
        return $this;
    }

    public function removeOldMessages(int $skipFrom): ChatHistoryInterface
    {
        $this->updateTable();
        return $this;
    }

    public function removeMessage(int $index): ChatHistoryInterface
    {
        $this->updateTable();
        return $this;
    }

    protected function clear(): ChatHistoryInterface
    {
        $this->updateTable();
        return $this;
    }

    public function updateTable(): void
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET messages = :messages WHERE thread_id = :thread_id");
        $stmt->execute(['thread_id' => $this->thread_id, 'messages' => \json_encode($this->jsonSerialize())]);

        if ($stmt->rowCount() <= 0) {
            throw new ChatHistoryException("No rows were updated (thread_id {$this->thread_id} not found)");
        }
    }

    protected function sanitizeTableName(string $tableName): string
    {
        // Remove any potential SQL injection characters
        $tableName = trim($tableName);

        // Whitelist validation
        if (!$this->tableExists($this->table)) {
            throw new ChatHistoryException('Table not allowed');
        }

        // Format validation as backup
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
            throw new ChatHistoryException('Invalid table name format');
        }

        return $tableName;
    }

    protected function tableExists(string $tableName): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name");
        $stmt->execute(['table_name' => $tableName]);
        return $stmt->fetch() !== false;
    }
}
