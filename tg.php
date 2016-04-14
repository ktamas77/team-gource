#!/usr/bin/php
<?php

// Team Gource Command Line Utility
// Author: Tamas Kalman <ktamas77@gmail.com>
// Web: https://github.com/ktamas77/team-gource

$config = yaml_parse(file_get_contents('tg.conf'));

function updateRepo($org, $repo) {
    if (!is_dir('repos')) {
        mkdir('repos');
    }
    exec("git clone git@github.com:$org/$repo.git repos/$repo");
}

$teams = $config['teams'];
foreach ($teams as $team) {
    $teamName = $team['name'];
    $teamCollection = $team['collection'];
    print "Team: $teamName\n";
    print "Collection: $teamCollection\n";
    $repos = $config['collections'];
    foreach ($repos as $repo) {
        if ($repo['name'] === $teamCollection) {
            $teamOrg = $repo['organization'];
            $teamRepos = $repo['repos'];
            foreach ($teamRepos as $repoName) {
                updateRepo($teamOrg, $repoName);
            }
        }
    }
}