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
        $actionText = ($action === 'install') ? 'installing' : 'updating';

        $io->write("\n");
        $io->write("<fg=blue>    _   __                            ___ _  </>");
        $io->write("<fg=blue>   / | / /__  __  __ ____ ____ __  __/   | | </>");
        $io->write("<fg=blue>  /  |/ / _ \/ / / / ___/ __  / | / / /| | | </>");
        $io->write("<fg=blue> / /|  /  __/ /_/ / /  / /_/ /  |/ / /_| | | </>");
        $io->write("<fg=blue>/_/ |_/\___/\__,_/_/   \__,_/_/|__/_/  |_|_| </>");
        $io->write("\n");

        $io->write("<fg=green;options=bold>Thank you for {$actionText} Neuron AI!</>");
        $io->write("<fg=green>Your AI agent framework is ready to use.</>\n");

        $io->write("<fg=yellow;options=bold>🔍 Monitor Your AI Agents</>");
        $io->write("<fg=yellow>We recommend Inspector.dev to monitor performance and detect issues:</>\n");

        $io->write("  • <fg=white>Real-time visibility into your AI agents' activities</>");
        $io->write("  • <fg=white>Performance metrics and latency monitoring</>");
        $io->write("  • <fg=white>Error tracking and anomaly detection</>\n");

        $io->write("<fg=white;options=bold>📚 Resources:</>");
        $io->write("  • Neuron AI Documentation: <fg=green>https://docs.neuronai.dev</>");
        $io->write("  • Inspector Integration Guide: <fg=green>https://docs.neuron-ai.dev/advanced/observability</>\n");
    }
}
