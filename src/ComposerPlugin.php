<?php

namespace NeuronAI;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // Not needed for this example
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // Not needed for this example
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'displayMessage',
            PackageEvents::POST_PACKAGE_UPDATE => 'displayMessage',
        ];
    }

    private function displayMessage()
    {
        $this->io->write("\n");
        $this->io->write("<fg=blue>    _   __                            ___ _  </>");
        $this->io->write("<fg=blue>   / | / /__  __  __ ____ ____ __  __/   | | </>");
        $this->io->write("<fg=blue>  /  |/ / _ \/ / / / ___/ __  / | / / /| | | </>");
        $this->io->write("<fg=blue> / /|  /  __/ /_/ / /  / /_/ /  |/ / /_| | | </>");
        $this->io->write("<fg=blue>/_/ |_/\___/\__,_/_/   \__,_/_/|__/_/  |_|_| </>");
        $this->io->write("\n");

        $this->io->write("<fg=green;options=bold>Thank you for using Neuron AI!</>");
        $this->io->write("<fg=green>Your AI agent framework is ready to use.</>\n");

        $this->io->write("<fg=yellow;options=bold>🔍 Monitor Your AI Agents</>");
        $this->io->write("<fg=yellow>We recommend Inspector.dev to monitor performance and detect issues:</>\n");

        $this->io->write("  • <fg=white>Real-time visibility into your AI agents' activities</>");
        $this->io->write("  • <fg=white>Performance metrics and latency monitoring</>");
        $this->io->write("  • <fg=white>Error tracking and anomaly detection</>\n");

        $this->io->write("<fg=white;options=bold>📚 Resources:</>");
        $this->io->write("  • Neuron AI Documentation: <fg=green>https://docs.neuronai.dev</>");
        $this->io->write("  • Inspector Integration Guide: <fg=green>https://docs.neuron-ai.dev/advanced/observability</>\n");
    }
}
