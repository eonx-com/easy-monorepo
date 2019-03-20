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
    */
    'mapping' => [
        DecisionInterface::TYPE_YESNO_AFFIRMATIVE => AffirmativeDecision::class,
        DecisionInterface::TYPE_YESNO_CONSENSUS => ConsensusDecision::class,
        DecisionInterface::TYPE_YESNO_UNANIMOUS => UnanimousDecision::class,
        DecisionInterface::TYPE_VALUE => ValueDecision::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Decisions
    |--------------------------------------------------------------------------
    |
    | Here you will define the mapping between the different types of decision
    | and the implementation to use for it. It will allow you to create your
    | own decision type and/or implementations and use it. It also allow you
    | to control which decision types are available within your application.
    |
    */
    'decisions' => [
        'decision-name' => [
            'type' => DecisionInterface::TYPE_YESNO_UNANIMOUS,

            'providers' => [

            ],

            'expressions' => [
                'functions' => [],
                'providers' => []
            ]
        ]
    ]
];
