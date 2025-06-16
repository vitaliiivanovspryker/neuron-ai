<?php

namespace NeuronAI\Workflow;

interface ExporterInterface
{
    public function export(Workflow $graph): string;
}
