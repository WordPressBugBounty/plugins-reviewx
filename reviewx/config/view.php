<?php

return [
    'enable_templating' => true,
    'template_path' => 'resources/views',
    'cache_path' => 'storage/cache/views',
    'cache_lifetime' => 0,
    'template_extension' => 'twig',
    'lexer' => [
        'tag_comment' => ['{#', '#}'],
        'tag_block' => ['{%', '%}'],
        'tag_variable' => ['{{', '}}'],
        'interpolation' => ['#{', '}'],
    ],
    'functions' => [
	    'bearer_token' => function () {
		    return \Rvx\Utilities\Helper::getAuthToken();
	    },
	    '__' => function ($text, $domain = 'reviewx') {
		    return __($text, $domain);
	    },
	    'esc_html__' => function ($text, $domain = 'reviewx') {
		    return esc_html__($text, $domain);
	    },
	    'esc_attr__' => function ($text, $domain = 'reviewx') {
		    return esc_attr__($text, $domain);
	    },
    ]
];