<?php

include_once('api_key.php');

function usage()
{
        echo "usage: proof.php --summoner=SMN_NAME" . PHP_EOL;
}

function getSummonerIdFromName($name) {
    $key = getApiKey();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://na.api.pvp.net/api/lol/na/v1.4/summoner/by-name/$name?api_key=$key");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if($data && $data[$name]) {
        return $data[$name]['id'];
    } else {
        return 0;
    }
}

function getChampionNameFromId($champId) {
    $key = getApiKey();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://na.api.pvp.net/api/lol/static-data/na/v1.2/champion/$champId?api_key=$key");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if($data && $data['name']) {
        return $data['name'];
    } else {
        return '';
    }
}

function alignTeams($players, $stats, $gameId) {
    $one = array();
    $two = array();
    foreach($players as $player) {
        if($player->teamId == 100) {
            $one[] = $player;
        } else {
            $two[] = $player;
        }
    }
    $stats[$gameId]['enemies'] = array();
    $stats[$gameId]['friends'] = array();
    if(count($one) > count($two)) {
        foreach($one as $enemy) {
            $stats[$gameId]['enemies'][$enemy->championId] = 0;
        }
        foreach($two as $friend) {
            $stats[$gameId]['friends'][$friend->championId] = 0;
        }
    } else {
        foreach($two as $enemy) {
            $stats[$gameId]['enemies'][$enemy->championId] = 0;
        }
        foreach($one as $friend) {
            $stats[$gameId]['friends'][$friend->championId] = 0;
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

    return array('friends' => $friends, 'enemies' => $enemies, 'totals' => $totals);
}

function printStats($stats) {
    $bestFriend;
    $traitor;
    $worstNightmare;
    $frienemy;

    foreach($stats['friends'] as $champId => $score) {
        if(!isset($bestFriend)) {
            $bestFriend = array($champId => $score);
        } else if($score > current($bestFriend)) {
            $bestFriend = array($champId => $score);
        }
        if(!isset($traitor)) {
            $traitor = array($champId => $score);
        } else if($score < current($traitor)) {
            $traitor = array($champId => $score);
        }
    }

    foreach($stats['enemies'] as $champId => $score) {
        if(!isset($worstNightmare)) {
            $worstNightmare = array($champId => $score);
        } else if($score < current($worstNightmare)) {
            $worstNightmare = array($champId => $score);
        }
        if(!isset($frienemy)) {
            $frienemy = array($champId => $score);
        } else if($score > current($frienemy)) {
            $frienemy = array($champId => $score);
        }
    }

    echo "\nbestFriend = ";
    foreach($bestFriend as $champId => $score) {
        $name = getChampionNameFromId($champId);
        echo "$name => $score";
    }
    echo "\ntraitor = ";
    foreach($traitor as $champId => $score) {
        $name = getChampionNameFromId($champId);
        echo "$name => $score";
    }
    echo "\nworstNightmare = ";
    foreach($worstNightmare as $champId => $score) {
        $name = getChampionNameFromId($champId);
        echo "$name => $score";
    }
    echo "\nfrienemy = ";
    foreach($frienemy as $champId => $score) {
        $name = getChampionNameFromId($champId);
        echo "$name => $score";
    }
}

function getStatsFromId($id) {
    $key = getApiKey();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://na.api.pvp.net/api/lol/na/v1.3/game/by-summoner/$id/recent?api_key=$key");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);
    if(!$data) {
        return 0;
    }

    $stats = array();
    foreach($data->games as $game) {
        $gameId = $game->gameId;
        $stats[$gameId] = array();
        $stats = alignTeams($game->fellowPlayers, $stats, $gameId);
        $stats = tallyStats($game->stats, $stats, $gameId);
    }

    $stats = combineStats($stats);

    return $stats;
}

$options = getopt("v", array("summoner:"));

if (!isset($options['summoner']))
{
        usage();
        exit(1);
}

$summoner = $options['summoner'];
$id = getSummonerIdFromName($summoner);
echo "\nid=$id\n";
if($id) {
    printStats(getStatsFromId($id));
}