<?php
header('Content-Type: application/json');

$manifest = [
    "name" => "Barbreon Admin",
    "short_name" => "Barbreon Admin",
    "icons" => [
        [
            "src" => "img/android-chrome-192x192.png",
            "sizes" => "192x192",
            "type" => "image/png"
        ],
        [
            "src" => "img/android-chrome-384x384.png",
            "sizes" => "384x384",
            "type" => "image/png"
        ]
    ],
    "start_url" => "/?rd=adm",
    "display" => "standalone",
    "orientation" => "any",
    "background_color" => "#222222",
    "theme_color" => "#078d5d"
];

echo json_encode($manifest, JSON_PRETTY_PRINT);
?>
