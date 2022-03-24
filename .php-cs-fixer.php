<?php
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
$rules = [
    '@PSR2'                                       => true,
    'blank_line_after_namespace'                  => true,
    'braces'                                      => true,
    'class_definition'                            => [
        'single_line' => true,
    ],
    'combine_consecutive_issets'=> true,
    'combine_consecutive_unsets'=> true,
    'blank_line_after_opening_tag'                => true,
    'single_blank_line_before_namespace'          => true,
    'ternary_operator_spaces'                     => true,
    'trim_array_spaces'                           => true,
    'linebreak_after_opening_tag'                 => true,
    'unary_operator_spaces'                       => true,
    'compact_nullable_typehint'                   => true,
    'constant_case'                   => [
        'case' => 'lower'
    ],

    'concat_space'                                => [
        'spacing' => 'one'
    ],
    'cast_spaces'                                => [
        'space' => 'single'
    ],
    'elseif'                                      => true,
    'function_declaration'                        => true,
    'indentation_type'                            => true,
    'is_null'                                     => true,
    'line_ending'                                 => true,
    'lowercase_keywords'                          => true,
    'function_typehint_space'                     => true,
    'no_spaces_around_offset'                     =>[
        'positions' =>
            [
                'inside',
                'outside',
            ],
    ],
    'method_argument_space'                       => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => false,
    ],
    'no_break_comment'                            => true,
    'no_closing_tag'                              => true,
    'no_spaces_after_function_name'               => true,
    'no_spaces_inside_parenthesis'                => true,
    'no_trailing_whitespace'                      => true,
    'no_trailing_whitespace_in_comment'           => true,
    'no_whitespace_before_comma_in_array'         => true,
    'single_line_throw'                           => true,
    'no_trailing_comma_in_list_call'              => true,
    'single_blank_line_at_eof'                    => true,
    'single_class_element_per_statement'          => [
        'elements' => ['property'],
    ],
    'single_import_per_statement'                 => true,
    'single_line_after_imports'                   => true,
    'switch_case_semicolon_to_colon'              => true,
    'switch_case_space'                           => true,
    'visibility_required'                         => true,
    'encoding'                                    => true,
    'no_empty_comment'                            => true,
    'lowercase_cast'                              => true,
    'full_opening_tag'                            => true,
    'lowercase_static_reference'                  => true,
    'include'                                     => true,
    'no_alternative_syntax'                       => true,
    'global_namespace_import'                  => [
        'import_classes' => true,
    ],
    'native_function_type_declaration_casing'     => true,
    'magic_constant_casing'                       => true,
    'multiline_comment_opening_closing'           => true,
    'array_syntax'                                => ['syntax' => 'short'],
    'no_unused_imports'                           => true,
    'magic_method_casing'                         => true,
    'no_whitespace_in_blank_line'                 => true,
    'fully_qualified_strict_types'                => true,
    'new_with_braces'                             => true,
    'no_blank_lines_after_class_opening'          => true,
    'no_empty_phpdoc'                             => true,
    'no_empty_statement'                          => true,
    'no_singleline_whitespace_before_semicolons'  => true,
    'multiline_whitespace_before_semicolons'      => [
        'strategy' => 'no_multi_line'
    ],
    'php_unit_method_casing'                      => [
        'case' => 'snake_case'
    ],
    'object_operator_without_whitespace'          => true,
    'php_unit_test_annotation'          => [
        'style' => 'annotation'
    ],
    'ordered_imports'                             => [
        'sort_algorithm' => 'length',
    ],
    'phpdoc_align'                             => [
        'align' => 'vertical',
    ],
    'no_extra_blank_lines'                        => [
        'tokens' => [
            'switch',
            'case',
            'default',
            'break',
            'continue',
            'curly_brace_block',
            'extra',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'throw',
            'use',
            'use_trait',
        ]
    ],
    'array_indentation'                           => true,
    'binary_operator_spaces'                      => [
        'default' => 'align_single_space_minimal',

    ],
    'no_leading_namespace_whitespace'             => true,
    'no_multiline_whitespace_around_double_arrow' => true,
    'whitespace_after_comma_in_array' => false,
    'align_multiline_comment' => [
        'comment_type' => 'phpdocs_only'
    ]
];
$finder = Finder::create()
    ->exclude('bootstrap/')
    ->exclude('public/')
    ->exclude('resources/assets/')
    ->exclude('storage/')
    ->exclude('vendor/')
    ->exclude('document/')
    ->exclude('node_modules/')
    ->notName('*.blade.php')
    ->notName('server.php')
    ->notName('_ide_helper.php')
    ->notName('*.yml')
    ->notName('*.xml')
    ->notName('*.env')
    ->notName('*.json')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->in(__DIR__);


return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);

