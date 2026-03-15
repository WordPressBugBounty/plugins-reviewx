<?php

defined( 'ABSPATH' ) || exit;

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
		    return \ReviewX\Utilities\Helper::getAuthToken();
	    },
	    '__' => function ($text, $domain = 'reviewx') {
		    return __($text, $domain); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain
	    },
	    'esc_html__' => function ($text, $domain = 'reviewx') {
		    return esc_html(__($text, $domain)); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain
	    },
	    'esc_attr__' => function ($text, $domain = 'reviewx') {
		    return esc_attr(__($text, $domain)); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain
	    },
    ]
];