<?php

include_once('api_key.php');
include_once('ApiCaller.php');

function usage()
{
        echo "usage: proof.php --summoner=SMN_NAME" . PHP_EOL;
}

function alignTeams($players, $stats, $gameId) {
    $one = array();
    $two = array();
    foreach($players as $player) {
        if($player['teamId'] == 100) {
            $one[] = $player;
        } else {
            $two[] = $player;
        }
    }
    $stats[$gameId]['enemies'] = array();
    $stats[$gameId]['friends'] = array();
    if(count($one) == 0) {
        foreach($two as $friend) {
            $stats[$gameId]['friends'][$friend['championId']] = 0;
        }
    } else if(count($two) == 0) {
        foreach($one as $friend) {
            $stats[$gameId]['friends'][$friend['championId']] = 0;
        }
    } else if(count($one) > count($two)) {
        foreach($one as $enemy) {
            $stats[$gameId]['enemies'][$enemy['championId']] = 0;
        }
        foreach($two as $friend) {
            $stats[$gameId]['friends'][$friend['championId']] = 0;
        }
    } else {
        foreach($two as $enemy) {
            $stats[$gameId]['enemies'][$enemy['championId']] = 0;
        }
        foreach($one as $friend) {
            $stats[$gameId]['friends'][$friend['championId']] = 0;
        }
    }

    return $stats;
}

function tallyStats($gameStats, $stats, $gameId) {
    foreach($gameStats as $gameStat => $value) {
        switch($gameStat) {
            case "goldEarned":
                foreach($stats[$gameId]['friends'] as $champId => $score) {
                    $stats[$gameId]['friends'][$champId] = $score + ($value / 1000);
                }
                foreach($stats[$gameId]['enemies'] as $champId => $score) {
                    $stats[$gameId]['enemies'][$champId] = $score + ($value / 1000);
                }
            break;
            case "championsKilled":
            case "assists":
                foreach($stats[$gameId]['friends'] as $champId => $score) {
                    $stats[$gameId]['friends'][$champId] = $score + $value;
                }
                foreach($stats[$gameId]['enemies'] as $champId => $score) {
                    $stats[$gameId]['enemies'][$champId] = $score + $value;
                }
            break;
            case "numDeaths":
                foreach($stats[$gameId]['friends'] as $champId => $score) {
                    $stats[$gameId]['friends'][$champId] = $score - $value;
                }
                foreach($stats[$gameId]['enemies'] as $champId => $score) {
                    $stats[$gameId]['enemies'][$champId] = $score - $value;
                }
            break;
            case "totalDamageTaken":
                foreach($stats[$gameId]['friends'] as $champId => $score) {
                    $stats[$gameId]['friends'][$champId] = $score - ($value / 10000);
                }
                foreach($stats[$gameId]['enemies'] as $champId => $score) {
                    $stats[$gameId]['enemies'][$champId] = $score - ($value / 10000);
                }
            break;
            case "win":
                foreach($stats[$gameId]['friends'] as $champId => $score) {
                    $stats[$gameId]['friends'][$champId] = $value == 1 ? $score + 10 : $score - 10;
                }
                foreach($stats[$gameId]['enemies'] as $champId => $score) {
                    $stats[$gameId]['enemies'][$champId] = $value == 1 ? $score + 10 : $score - 10;
                }
            break;
        }
    }

    return $stats;
}

function combineStats($stats) {
    $friends = array();
    $enemies = array();
    $totals = array();
    foreach($stats as $gameId => $gameStats) {
        foreach($gameStats['friends'] as $champId => $score) {
            if(!isset($friends[$champId])) {
                $friends[$champId] = 0;
            }
            if(!isset($totals[$champId])) {
                $totals[$champId] = 0;
            }
            $friends[$champId] += $score;
            $totals[$champId] += $score;
        }
        foreach($gameStats['enemies'] as $champId => $score) {
            if(!isset($enemies[$champId])) {
                $enemies[$champId] = 0;
            }
            if(!isset($totals[$champId])) {
                $totals[$champId] = 0;
            }
            $enemies[$champId] += $score;
            $totals[$champId] += $score;
        }
    }

    asort($friends);
    asort($enemies);
    asort($totals);

    return array('friends' => $friends, 'enemies' => $enemies, 'totals' => $totals);
}

function printStats($stats, $count) {
    $apiCaller = new ApiCaller(getApiKey());
    $bestFriend = array_reverse(array_slice($stats['friends'], ($count * -1), $count, true), true);
    $traitor = array_slice($stats['friends'], 0, $count, true);
    $worstNightmare = array_slice($stats['enemies'], 0, $count, true);
    $frienemy = array_reverse(array_slice($stats['enemies'], ($count * -1), $count, true), true);

    echo "\nbestFriends:     ";
    foreach($bestFriend as $champId => $score) {
        $name = $apiCaller->getChampionNameFromId($champId);
        echo "$name => $score\t";
    }
    echo "\ntraitors:        ";
    foreach($traitor as $champId => $score) {
        $name = $apiCaller->getChampionNameFromId($champId);
        echo "$name => $score\t";
    }
    echo "\nworstNightmares: ";
    foreach($worstNightmare as $champId => $score) {
        $name = $apiCaller->getChampionNameFromId($champId);
        echo "$name => $score\t";
    }
    echo "\nfrienemies:      ";
    foreach($frienemy as $champId => $score) {
        $name = $apiCaller->getChampionNameFromId($champId);
        echo "$name => $score\t";
    }
}

function getStatsFromId($summonerId) {
    $apiCaller = new ApiCaller(getApiKey());
    $data = $apiCaller->getStatsFromSummonerId($summonerId);

    $stats = array();
    foreach($data['games'] as $game) {
        $gameId = $game['gameId'];
        $stats[$gameId] = array();
        $stats = alignTeams($game['fellowPlayers'], $stats, $gameId);
        $stats = tallyStats($game['stats'], $stats, $gameId);
    }

    $stats = combineStats($stats);

    return $stats;
}

$options = getopt("v", array("summoner:", "count:"));

if (!isset($options['summoner']))
{
        usage();
        exit(1);
}

$summoner = $options['summoner'];
$count = isset($options['count']) ? $options['count'] : 1;
$apiCaller = new ApiCaller(getApiKey());
$id = $apiCaller->getSummonerIdFromName($summoner);
if($id) {
    printStats(getStatsFromId($id), $count);
    echo "\n";
}
