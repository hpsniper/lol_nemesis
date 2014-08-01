<?

abstract class ChampionStats {
    // these values represent a unique id for a ChampionStats object
    private $teamate_type;
    private $champion_id;
    private $game_mode;
    private $game_type;
    private $sub_type;

    // these are stats to be saved foreach ChampionStats object
    private $num_games;
    private $gold_earned;
    private $num_deaths;
    private $champions_killed;
    private $damage_dealt;
    private $damage_taken;
    private $num_sprees;
    private $largest_spree;
    private $time_played_with;
    private $assists;
    private $wins;

    private $tracked_stats = array (
        'num_games',
        'time_played_with',
        'gold_earned',
        'num_deaths',
        'champions_killed',
        'damage_dealt',
        'damage_taken',
        'num_sprees',
        'largest_spree',
        'assists',
        'win'
    );

    protected abstract function getMod();

    public fillStatsFromGame($game) {
        foreach($this->tracked_stats as $stat) {
            if(method_exists($this, "fill_$stat")) {
                call_user_func(array($this, "fill_$stat"), $game);
            }
        }
    }

    protected function set_num_games($value) {
        $this->num_games = $value;
    }

    protected function get_num_games() {
        return $this->num_games;
    }

    protected function set_gold_earned($value) {
        $this->gold_earned = $value;
    }

    protected function get_gold_earned() {
        return $this->gold_earned;
    }

    protected function set_num_deaths($value) {
        $this->num_deaths = $value;
    }

    protected function get_num_deaths() {
        return $this->num_deaths;
    }

    protected function set_champions_killed($value) {
        $this->champions_killed = $value;
    }

    protected function get_champions_killed() {
        return $this->champions_killed;
    }

    protected function set_damage_dealt($value) {
        $this->damage_dealt = $value;
    }

    protected function get_damage_dealt() {
        return $this->damage_dealt;
    }

    protected function set_damage_taken($value) {
        $this->damage_taken = $value;
    }

    protected function get_damage_taken() {
        return $this->damage_taken;
    }

    protected function set_num_sprees($value) {
        $this->num_sprees = $value;
    }

    protected function get_num_sprees() {
        return $this->num_sprees;
    }

    protected function set_largest_spree($value) {
        $this->largest_spree = $value;
    }

    protected function get_largest_spree() {
        return $this->largest_spree;
    }

    protected function set_time_played_with($value) {
        $this->time_played_with = $value;
    }

    protected function get_time_played_with() {
        return $this->time_played_with;
    }

    protected function set_assists($value) {
        $this->assists = $value;
    }

    protected function get_assists() {
        return $this->assists;
    }

    protected function set_wins($value) {
        $this->wins = $value;
    }

    protected function get_wins() {
        return $this->wins;
    }

    protected function fill_num_games($game) {
        $this->num_games++;
    }

    protected function fill_time_played_with($game) {
        $this->time_played_with += $game['stats']['timePlayed'];
    }
}
