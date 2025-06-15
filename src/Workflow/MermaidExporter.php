<?php

namespace NeuronAI\Workflow;

class MermaidExporter implements ExporterInterface
{
    public function export(Workflow $graph): string
    {
        $output = "graph TD\n";

        foreach ($graph->getEdges() as $edge) {
            $output .= "    {$edge->getFrom()} --> {$edge->getTo()}\n";
        }

        return $output;
    }
}
