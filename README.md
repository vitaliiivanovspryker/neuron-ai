[![Latest Stable Version](https://poser.pugx.org/inspector-apm/neuron-ai/v/stable)](https://packagist.org/packages/inspector-apm/neuron-ai)
[![License](https://poser.pugx.org/inspector-apm/neuron-ai/license)](//packagist.org/packages/inspector-apm/neuron-ai)

![](./docs/img/neuron-ai-php-framework.png)


> Before moving on, support the community giving a GitHub star ⭐️. Thank you!

## Requirements

- PHP: ^8.0

## Install

Install the latest version of the package:

```
composer require inspector-apm/neuron-ai
```

## Create an Agent

Neuron provides you with the Agent class you can extend to inherit the main features of the framework,
and create fully functional agents. This class automatically manages some advanced mechanisms for you such as memory,
tools and function calls, up to the RAG systems. You can go deeper into these aspects in the [documentation](https://docs.neuron-ai.dev).
In the meantime, let's create the first agent, extending the `NeuronAI\Agent` class:

```php
use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;

class SEOAgent extends Agent
{
    public function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'ANTHROPIC_MODEL',
        );
    }

    public function instructions()
    {
        return "Act as an expert of SEO (Search Engine Optimization). ".
            "Your role is to analyze a text of an article and provide suggestions ".
            "on how the content can be improved to get a better rank on Google search.";
    }
}
```


## Talk to the Agent

Send a prompt to the agent to get a response from the underlying LLM:

```php
$seoAgent = SEOAgent::make();

$response = $seoAgent->run(new UserMessage("Who are you?"));
echo $response->getContent();
// I'm a SEO expert, how can I help you today?


$response = $seoAgent->run(
    new UserMessage("What do you think about the following article? --- ".file_get_contents('./README.md'))
);
echo $response->getContent();
// It's well done! Anyway, let me give you some advice to get a better rank on Google...
```

## Official documentation

**[Go to the official documentation](https://neuron.inspector.dev/)**

## Contributing

We encourage you to contribute to the development of Neuron AI Framework!
Please check out the [Contribution Guidelines](CONTRIBUTING.md) about how to proceed. Join us!

## LICENSE

This bundle is licensed under the [MIT](LICENSE) license.
