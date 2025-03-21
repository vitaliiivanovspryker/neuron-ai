<?php

namespace NeuronAI\MCP;

class StdioTransport implements McpTransportInterface
{
    private $process;

    private $pipes;

    /**
     * Create a new StdioTransport with the given configuration
     */
    public function __construct(protected array $config) {}

    /**
     * Connect to the MCP server by spawning the process
     */
    public function connect(): void
    {
        $descriptorSpec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];

        $command = $this->config['command'];
        $args = $this->config['args'] ?? [];
        $env = $this->config['env'] ?? [];

        // Build command with arguments
        $commandLine = $command;
        foreach ($args as $arg) {
            $commandLine .= ' ' . escapeshellarg($arg);
        }

        // Start the process
        $this->process = proc_open(
            $commandLine,
            $descriptorSpec,
            $this->pipes,
            null,
            $env
        );

        if (!is_resource($this->process)) {
            throw new \Exception("Failed to start the MCP server process");
        }
    }

    /**
     * Send a request to the MCP server
     */
    public function send($data): void
    {
        if (!is_resource($this->process)) {
            throw new \Exception("Process is not running. You must call the 'connect()' method before sending a request.");
        }

        fwrite($this->pipes[0], json_encode($data) . "\n");
        fflush($this->pipes[0]);
    }

    /**
     * Receive a response from the MCP server
     */
    public function receive(): array
    {
        if (!is_resource($this->process)) {
            throw new \Exception("Process is not running. You must call the 'connect()' method before sending a request.");
        }

        $response = fgets($this->pipes[1]);
        if ($response === false) {
            throw new \Exception("Failed to read response from MCP server");
        }

        return json_decode($response, true);
    }

    /**
     * Disconnect from the MCP server
     */
    public function disconnect(): void
    {
        if (is_resource($this->process)) {
            fclose($this->pipes[0]);
            fclose($this->pipes[1]);
            fclose($this->pipes[2]);
            proc_close($this->process);
        }
    }
}
