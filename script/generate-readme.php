<?php
$content = file_get_contents(__DIR__ . "/README.source.md");
$composerJsonArray = json_decode(file_get_contents(__DIR__ . "/../composer.json"), true);

$regexLineBreaks = '/(\r\n|\r|\n)/';
$split = preg_split($regexLineBreaks, $content);

foreach ($split as $i => &$line) {
    if ('%composer.json.description%' === trim($line)) {
        $line = (
            $composerJsonArray['description']
            . PHP_EOL
            . PHP_EOL
            . '[comment]: # (The README.md is generated using `script/generate-readme.php`)'
            . PHP_EOL
        );
        continue;
    }

    if ('%composer.json.authors%' === trim($line)) {
        $segments = [];
        foreach ($composerJsonArray["authors"] as $author) {
            $segment = "- " . $author["name"];
            if (array_key_exists("email", $author)) {
                $segment .= '<br>E-mail: ' . sprintf(
                    '<a href="mailto:%s">%s</a>',
                    $author['email'],
                    $author['email']
                );
            }
            if (array_key_exists("homepage", $author)) {
                $segment .= '<br>Homepage: ' . sprintf(
                    '<a href="%s">%s</a>',
                    $author['homepage'],
                    $author['homepage']
                );
            }
            $segments[] = $segment;
        }
        if ($segments) {
            $line = implode(PHP_EOL, $segments);
        } else {
            unset($split[$i]);
        }
        continue;
    }

    if ('%composer.json.require%' === trim($line)) {
        $segments = [];
        foreach ($composerJsonArray["require"] as $requireName => $requireVersion) {
            $segments[] = sprintf(
                '"%s": "%s"',
                $requireName,
                $requireVersion
            );
        }
        $line = (
            "```json"
            . PHP_EOL
            . implode("," . PHP_EOL, $segments)
            . PHP_EOL
            . "```"
        );
        continue;
    }

    preg_match('/^%include "(.+)"%$/', $line, $includeMatch);
    if ($includeMatch) {
        $includeContent = file_get_contents($includeMatch[1]);
        $includeSplit = preg_split($regexLineBreaks, $includeContent);
        foreach ($includeSplit as $j => &$includeLine) {
            if (preg_match('/^(.+);\s*\/\/\s*README.md.remove\s*$/', trim($includeLine))) {
                unset($includeSplit[$j]);
                continue;
            }
        }
        $line = implode(PHP_EOL, $includeSplit);
    }
}

$content = implode(PHP_EOL, $split);
file_put_contents(__DIR__ . "/../README.md", $content);
