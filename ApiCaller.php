<?php

class ApiCaller {
    private $api_key;

    public function __construct($k) {
        $this->api_key = $k;
    }

    public function getSummonerIdFromName($name) {
        $url = "https://na.api.pvp.net/api/lol/na/v1.4/summoner/by-name/$name";
        $data = $this->make_api_call($url);
        if($data && $data[$name]) {
            return $data[$name]['id'];
        }

        return 0;
    }

    public function getChampionNameFromId($championId) {
        $url = "https://na.api.pvp.net/api/lol/static-data/na/v1.2/champion/$championId";
        $data = $this->make_api_call($url);
        if($data && $data['name']) {
            return $data['name'];
        }

        return '';
    }

    private function make_api_call($url) {
        $key = getApiKey();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$url?api_key=$key");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
