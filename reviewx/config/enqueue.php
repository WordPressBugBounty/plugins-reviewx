<?php
use Rvx\Utilities\Helper;
$rvx_user_access_script = get_option('__user_setting_access') ?? '';

return [
    'admin' => [
        'scripts' => [
            [
                'handle' => 'reviewx-admin-onBoarding-js',
                'src' => 'frontend/dist/apps/onBoarding/rvx-on-boarding.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-dashboard-js',
                'src' => 'frontend/dist/apps/dashboard/rvx-dashboard.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-reviews-js',
                'src' => 'frontend/dist/apps/reviews/rvx-reviews.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-reviewReminder-js',
                'src' => 'frontend/dist/apps/reviewReminder/rvx-review-reminder.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-reviewDiscount-js',
                'src' => 'frontend/dist/apps/reviewDiscount/rvx-review-discount.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-generalSettings-js',
                'src' => 'frontend/dist/apps/generalSettings/rvx-general-settings.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'rvx_user_access_script',
                'src' => 'resources/js/rvx_user_access_script.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
            ],
            [
                'handle' => 'reviewx-admin-customPost-js',
                'src' => 'frontend/dist/apps/customPost/rvx-custom-post.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-integration-js',
                'src' => 'frontend/dist/apps/integration/rvx-integration.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-review-import-export-js',
                'src' => 'frontend/dist/apps/importExport/rvx-review-import-export.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-admin-review-import-judgeme-js',
                'src' => 'frontend/dist/apps/importJudgeMe/rvx-review-import-judgeme.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'type' => 'module',
                    ],
            ],
        ],
   
       'localize_scripts' => [
        ['handle' => 'rvx_user_access_script', 'objectName' => 'rvxUserAccess', 'data' => [
           'rvx_user_settings_access' => json_decode($rvx_user_access_script),
       ]],
       ['handle' => 'rvx_user_access_script', 'objectName' => 'rvx_locals', 'data' => [
        'rvx_localization_data_for_admin' => Helper::prepareLangArray(),
           'rvx_full_domain_name' => Helper::domainSupport(),
           'rvx_full_domain_api' => Helper::getRestAPIurl(),
    ]],
    ],
       
        'styles' => []
    ],
    'frontend' => [
        'scripts' => [
            [
                'handle' => 'alpine-js',
                'src' => 'resources/js/alpine.js',
                'deps' => [],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        'defer' => true,
                        'async' => true,
                    ],
            ],
            [
                'handle' => 'reviewx-storefront',
                'src' => 'resources/js/reviewx-storefront.js',
                'deps' => ['alpine-js'],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        // 'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-helper',
                'src' => 'resources/js/helper.js',
                'deps' => ['alpine-js'],
                'ver' => false,
                'in_footer' => true,
                'attributes' => []
            ],
            [
                'handle' => 'reviewx-rvxMediaUploadComponent',
                'src' => 'resources/js/mediaUploadComponent.js',
                'deps' => ['alpine-js'],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        // 'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-rvxReviewSuccessModalComponent',
                'src' => 'resources/js/reviewSuccessModalComponent.js',
                'deps' => ['alpine-js'],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        // 'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-rvxReviewMultiCriteriaComponent',
                'src' => 'resources/js/multiCriteriaRatingComponent.js',
                'deps' => ['alpine-js'],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                        // 'type' => 'module',
                    ],
            ],
             [
                'handle' => 'reviewx-rvxReviewFilterComponent',
                'src' => 'resources/js/reviewFilterComponent.js',
                'deps' => ['alpine-js'], 'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                    // 'type' => 'module',
                    ],
            ],
            [
                'handle' => 'reviewx-rvxEventDispatch',
                'src' => 'resources/js/eventDispatch/notify/index.js',
                'deps' => ['alpine-js'],
                'ver' => false,
                'in_footer' => true,
                'attributes' =>
                    [
                    // 'type' => 'module',
                    ],
            ],
        ],
    //    'localize_scripts' => [['handle' => 'reviewx-storeFront-js', 'objectName' => 'rvx_locals', 'data' => [
    //        'rvx_user_settings_access' => json_decode($rvx_user_access_script),
    //    ]]],
        'styles' => [
            [
                'handle' => 'reviewx-store-front-font-css',
                'src' => 'resources/assets/font.css',
                'deps' => [],
                'ver' => false,
                'media' => 'all',
                'in_footer' => false
            ],
            [
                'handle' => 'reviewx-store-front-icon-font-css',
                'src' => 'resources/assets/icon.font.css',
                'deps' => [],
                'ver' => false,
                'media' => 'all',
                'in_footer' => false
            ],
            [
                'handle' => 'reviewx-store-front-css',
                'src' => 'resources/assets/widget.css',
                'deps' => [],
                'ver' => false,
                'media' => 'all',
                'in_footer' => false
            ]
        ]
    ],
];
