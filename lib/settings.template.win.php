<?php

$settings = array(
    'qa' => array(
        'key'=>'2',
        'secret'=>'f7d02059-a105-45e0-85c9-7387565f322b',
        'username'=>'ft.tester',
        'password'=>'FT4life!',
        'baseUrl'=>'https://dc.qa.fellowshiponeapi.com',
        'debug'=>true,
    ),
	'staging' => array(
        'key'=>'2',
        'secret'=>'f7d02059-a105-45e0-85c9-7387565f322b',
        'username'=>'ft.tester',
        'password'=>'FT4life!',
        'baseUrl'=>'https://dc.staging.fellowshiponeapi.com',
        'debug'=>true,
    ),
    'prod' => array(
        'key'=>'',
        'secret'=>'',
        'username'=>'',
        'password'=>'',
        'baseUrl'=>'https://churchcode.fellowshiponeapi.com',
        'debug'=>true,
    )
);
$env = 'qa';
?>