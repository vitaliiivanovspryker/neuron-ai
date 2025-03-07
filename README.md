[![Latest Stable Version](https://poser.pugx.org/inspector-apm/neuron-ai/v/stable)](https://packagist.org/packages/inspector-apm/neuron-ai)
[![License](https://poser.pugx.org/inspector-apm/neuron-ai/license)](//packagist.org/packages/inspector-apm/neuron-ai)

![](./docs/img/logo-black-mini.png)

# Neuron AI

Open source framework to integrate AI Agents into your existing PHP application - powered by [Inspector.dev](https://inspector.dev)

> Before moving on, please consider giving us a GitHub star ⭐️. Thank you!

## Requirements

- PHP: ^8.0

## Install

Install the latest version of the bundle:

```
composer require inspector-apm/neuron-ai
```

## Create an Agent

Extend `NeuronAI\Agent` class to create your own agent:

```php
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;

class MyAgent extends Agent
{
    public function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'ANTHROPIC_MODEL',
        );
    }
}
```


## Talk to the Agent

Send a prompt to the agent to get a response from the underlying LLM:

```php
$response = MyAgent::make()
    ->run(
        new UserMessage("Hi, I'm Valerio")
    );

echo $response->getContent();

// Nice to meet you Valerio, how can I help you today?
```

## Official documentation

**[Go to the official documentation](https://neuron.inspector.dev/)**

## Contributing

We encourage you to contribute to the development of the Inspector bundle!
Please check out the [Contribution Guidelines](CONTRIBUTING.md) about how to proceed. Join us!

## LICENSE

This bundle is licensed under the [MIT](LICENSE) license.
