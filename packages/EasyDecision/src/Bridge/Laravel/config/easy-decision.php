<?php
declare(strict_types=1);

use StepTheFkUp\EasyDecision\Decisions\AffirmativeDecision;
use StepTheFkUp\EasyDecision\Decisions\ConsensusDecision;
use StepTheFkUp\EasyDecision\Decisions\UnanimousDecision;
use StepTheFkUp\EasyDecision\Decisions\ValueDecision;
use StepTheFkUp\EasyDecision\Interfaces\DecisionInterface;

return [
    /*
    |--------------------------------------------------------------------------
    | Expression Language
    |--------------------------------------------------------------------------
    |
    | ExpressionLanguage can be used to implement your business rules.
    |
    */
    'expressions' => [
        /*
        |--------------------------------------------------------------------------
        | Global Expression Functions List
        |--------------------------------------------------------------------------
        |
        | Here you will define all the expression functions you want to share across
        | all the decisions instances. The expression functions defined here can be
        | under each format accepted by the ExpressionFunctionFactory.
        |
        | Example:
        | new \App\Decisions\Expressions\MyOwnFunction('myOwn', function () {}), -> Function instance
        | ['name' => 'myOwn', 'evaluator' => function () {}], -> Associative array
        | ['myOwn', function () {}] -> Simple array
        |
        */
        'functions' => [],

        /*
        |--------------------------------------------------------------------------
        | Global Expression Functions Providers List
        |--------------------------------------------------------------------------
        |
        | Here you will define all the expression functions providers you want to
        | share across all the decisions instances. The providers will be instantiated
        | using the service container which means you can either return an instance
        | directly from here or return the service locator of your provider.
        |
        | Your providers must implement:
        | \StepTheFkUp\EasyDecision\Interfaces\Expressions\ExpressionFunctionProviderInterface
        |
        | Example:
        | \App\Decisions\Expressions\MyOwnFunctionsProvider::class, -> Instantiate from container
        | new \App\Decisions\Expressions\MyOwnFunctionsProvider()
        |
        */
        'providers' => []
    ],

    /*
    |--------------------------------------------------------------------------
    | Decision Types/Implementation Mapping
    |--------------------------------------------------------------------------
    |
    | Here you will define the mapping between the different types of decision
    | and the implementation to use for it. It will allow you to create your
    | own decision type and/or implementations and use it. It also allow you
    | to control which decision types are available within your application.
    |
    | Your decisions must implement:
    | \StepTheFkUp\EasyDecision\Interfaces\DecisionInterface
    |
    */
    'mapping' => [
        DecisionInterface::TYPE_YESNO_AFFIRMATIVE => AffirmativeDecision::class,
        DecisionInterface::TYPE_YESNO_CONSENSUS => ConsensusDecision::class,
        DecisionInterface::TYPE_YESNO_UNANIMOUS => UnanimousDecision::class,
        DecisionInterface::TYPE_VALUE => ValueDecision::class
        // 'My-Own-Type' => MyOwnDecision::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Decisions
    |--------------------------------------------------------------------------
    |
    | Here you will define your actual decisions and their configuration.
    | Decisions list must be an associative array where the keys are the names
    | of each decision and the value is the configuration.
    |
    | Each decision's configuration can either be defined directly in this file
    | using an associative array:
    |
    | 'my-decision' => [
    |     'type' => DecisionInterface::TYPE_YESNO_UNANIMOUS,
    |     'providers' => [
    |          \App\Decisions\MyDecisionRuleProvider::class, -> Instantiated from container
    |          new \App\Decisions\MyDecisionAdditionalRuleProvider()
    |     ]
    |     *** Optional ***
    |     'expressions' => [
    |          'functions' => [] -> Same logic as global expressions functions
    |          'providers' => [] -> Same logic as global expressions functions providers
    |     ]
    | ]
    |
    | Or you have the possibility to use a decision config provider if you prefer.
    | Your decision config providers must implement:
    | \StepTheFkUp\EasyDecision\Bridge\Laravel\DecisionConfigProviderInterface
    |
    | 'my-decision' => \App\Decisions\MyDecisionConfigProvider::class -> Instantiated from container
    | 'my-other-decision' => new \App\Decisions\MyOtherDecisionConfigProvider()
    |
    */
    'decisions' => [
        // Your decisions here...
    ]
];
