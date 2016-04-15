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
    if (!is_dir("repos/$repo")) {
        exec("git clone git@github.com:$org/$repo.git repos/$repo");
    } else {
        $currentDir = getcwd();
        chdir("repos/$repo");
        exec("git pull origin master");
        chdir($currentDir);
    }
}

function getGourceLog($repo) {
    if (!is_dir('tmp')) {
        mkdir('tmp');
    }
    if (!is_dir('logs')) {
        mkdir('logs');
    }
    $logName = tempnam("tmp/", "gource-repo-$repo-");
    exec("gource repos/$repo --output-custom-log $logName");
    return $logName;
}

function appendMasterLog($masterLog, $logName) {
    $logData = file_get_contents($logName);
    file_put_contents($masterLog, $logData, FILE_APPEND);
    unlink($logName);
}

function filterExcludedNamesFromLog($logName, $excluded) {
    $logData = file($logName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newData = "";
    foreach ($excluded as &$item) {
        $item = str_replace(" ", "\ ", $item);
    }
    $excluded = implode("|", $excluded);
    $pattern = "/[0-9]+\|($excluded)\|[A-Z]\|/";
    foreach ($logData as $logLine) {
        preg_match($pattern, $logLine, $matches);
        if (!isset($matches[1])) {
            $newData .= $logLine . "\n";
        }
    }
    file_put_contents($logName, $newData);
}


function addRepoToLog($logName, $repo, $org) {
    $logData = file($logName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newData = "";
    foreach ($logData as $logLine) {
        $newData .= preg_replace("/(\|[A-Z]\|)\//", "$1/$org/$repo/", $logLine) . "\n";
    }
    file_put_contents($logName, $newData);
}

function filterNonMembersFromLog($logName, $members) {
    $logData = file($logName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $fullTeam = [];
    foreach ($members as $member) {
        if (isset($member['aliases'])) {
            foreach ($member['aliases'] as $alias) {
                $fullTeam[$alias] = true;
            }
        }
    }
    $nonTeam = [];
    $newData = "";
    $pattern = "/[0-9]+\|(.*)\|[A-Z]\|/";
    foreach ($logData as $logLine) {
        preg_match($pattern, $logLine, $matches);
        $name = $matches[1];
        if (isset($fullTeam[$name])) {
            $newData .= $logLine . "\n";
        } else {
            if (!isset($nonTeam[$name])) {
                print "Non-team member found: $name\n";
            }
            $nonTeam[$name] = true;
        }
    }
    file_put_contents($logName, $newData);
}

function sortMasterLog($masterLog) {
    $logData = file($masterLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    asort($logData);
    file_put_contents($masterLog, print_r($logData, true));
}

$teams = $config['teams'];
foreach ($teams as $team) {
    $teamName = $team['name'];
    $teamCollection = $team['collection'];
    $excluded = [];
    if (isset($team['excluded'])) {
        $excluded = $team['excluded'];
    }
    print "Team: $teamName\n";
    print "Collection: $teamCollection\n";
    $repos = $config['collections'];
    $members = $team['members'];
    foreach ($repos as $repo) {
        if ($repo['name'] === $teamCollection) {
            $teamOrg = $repo['organization'];
            $teamRepos = $repo['repos'];
            $masterLog = tempnam("tmp/", "gource-master-log-$teamCollection");
            foreach ($teamRepos as $repoName) {
                print "updating repository for $repoName\n";
                updateRepo($teamOrg, $repoName);
                print "extracting logs for $repoName\n";
                $logName = getGourceLog($repoName);
                filterExcludedNamesFromLog($logName, $excluded);
                filterNonMembersFromLog($logName, $members);
                addRepoToLog($logName, $repoName, $teamOrg);
                appendMasterLog($masterLog, $logName);
            }
            sortMasterLog($masterLog);
            rename($masterLog, "logs/gource-master-log-$teamCollection.log");
        }
    }
}