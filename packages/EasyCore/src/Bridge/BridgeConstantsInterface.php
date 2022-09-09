<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_ELASTICSEARCH_HOST = 'easy_core.elasticsearch_host';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_BASE_URI = 'easy_core.open_api_normalizer.base_uri';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_CONTEXTS_FILE = 'easy_core.open_api_normalizer.contexts_file';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_DEFAULT_PROCESSORS_ENABLED =
        'easy_core.open_api_normalizer.default_processors_enabled';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_ENDPOINTS_REMOVE_BODY =
        'easy_core.open_api_normalizer.doc_path_processor.endpoints_remove_body';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_ENDPOINTS_REMOVE_PARAMS =
        'easy_core.open_api_normalizer.doc_path_processor.endpoints_remove_params';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_ENDPOINTS_REMOVE_RESPONSE =
        'easy_core.open_api_normalizer.doc_path_processor.endpoints_remove_response';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_DOC_PATH_PROCESSOR_SKIP_METHOD_NAMES =
        'easy_core.open_api_normalizer.doc_path_processor.skip_method_names';

    /**
     * @var string
     */
    public const PARAM_OPEN_API_NORMALIZER_PROCESSORS = 'easy_core.open_api_normalizer.processors';

    /**
     * @var string
     */
    public const PARAM_TRIM_STRINGS_EXCEPT = 'easy_core.trim_strings.except';

    /**
     * @var string
     */
    public const SERVICE_PROFILER_STORAGE_FLYSYSTEM = 'easy_core.profiler_storage_flysystem';
}
