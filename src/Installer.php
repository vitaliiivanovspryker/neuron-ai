<?php

namespace NeuronAI;

use Composer\Script\Event;

class Installer
{
    public static function postInstall(Event $event)
    {
        self::displayMessage($event->getIO(), 'install');
    }

    public static function postUpdate(Event $event)
    {
        self::displayMessage($event->getIO(), 'update');
    }

    private static function displayMessage($io, $action)
    {
        // Slightly customize the message based on install vs update
        $actionText = ($action === 'install') ? 'installing' : 'updating';

        // Neuron AI logo/banner
        $io->write("\n");
        $io->write("<fg=blue>    _   __                             ___    ___</>");
        $io->write("<fg=blue>   / | / /__  __  _________  ____    /   |  /   |</>");
        $io->write("<fg=blue>  /  |/ / _ \/ / / / ___/ / / / /   / /| | / /| |</>");
        $io->write("<fg=blue> / /|  /  __/ /_/ / /  / /_/ / /___/ ___ |/ ___ |</>");
        $io->write("<fg=blue>/_/ |_/\___/\__,_/_/   \__,_/_____/_/  |_/_/  |_|</>");
        $io->write("\n");

        // Main welcome message
        $io->write("<fg=green;options=bold>Thank you for {$actionText} Neuron AI!</>");
        $io->write("<fg=green>Your AI agent framework is ready to use.</>\n");

        // Inspector.dev promotion
        $io->write("<fg=yellow;options=bold>🔍 Monitor Your AI Agents</>");
        $io->write("<fg=yellow>Want to see what your AI agents are doing in production?</>");
        $io->write("<fg=yellow>We recommend Inspector.dev to monitor performance and detect issues:</>\n");

        // Benefits
        $io->write("  • <fg=white>Real-time visibility into your AI agents' activities</>");
        $io->write("  • <fg=white>Performance metrics and latency monitoring</>");
        $io->write("  • <fg=white>Error tracking and anomaly detection</>");
        $io->write("  • <fg=white>Usage patterns and cost optimization</>\n");

        // Documentation links
        $io->write("<fg=white;options=bold>📚 Resources:</>");
        $io->write("  • Neuron AI Documentation: <fg=green>https://docs.neuronai.dev</>");
        $io->write("  • Inspector Integration Guide: <fg=green>https://docs.neuron-ai.dev/advanced/observability</>\n");

        // Final note
        $io->write("<fg=green>Happy building with Neuron AI</>\n");
    }
}
