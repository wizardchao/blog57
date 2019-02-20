<?php
return [
    'name' => "Chaos' Blog",
    'title' => "Chaos' Blog",
    'subtitle' => 'https://www.wanchao.me',
    'description' => '人若无名，便可专心练剑！',
    'author' => 'Chaos',
    'page_image' => 'home-bg.jpg',
    'posts_per_page' => 5,
    'rss_size' => 25,
    'uploads' => [
        'storage' => 'public',
        'webpath' => '/storage',
    ],
    'contact_email'=>env('MAIL_FROM'),
];
