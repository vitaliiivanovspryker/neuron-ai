<?php

namespace NeuronAI\Tools;

enum PropertyType: string
{
    case INTEGER = 'int';
    case STRING = 'string';
    case NUMBER = 'number';
    case NULL = 'null';
    case BOOLEAN = 'boolean';
    case ARRAY = 'array';
    case OBJECT = 'object';
}
