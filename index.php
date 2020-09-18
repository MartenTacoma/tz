<?php
ini_set('display_errors', 'on');
/**
 * Simple timezone table
 * Author: Marten Tacoma
 * 
 * Add timezones and labels to array below in form label=>time zone identifier
 * Set night start and end as desired
 * Set timestep in hours as desired
 * 
 * Run from a php capable web server, access with index.php[?d=YYYY-mm-dd], if d parameter is ommitted table is shown for current date (server date)
 */

$zones = [
    'Western Europe'=>'Europe/Amsterdam',
    'UK'=>'Europe/London',
    'America East'=>'America/New_york',
    'America West'=>'America/Los_Angeles',
    'Christchurch'=>'Pacific/Auckland',
    'Hobart'=>'Australia/Hobart',
    'Tokyo'=>'Asia/Tokyo',
    'India'=>'Asia/Kolkata'
];

//times between which you don't want to ask people to be online, all fields filled in as time with format H:i
$night_start = '24:00';//(currently no later than midnight please after substracting event_length)
$night_end = '7:00';
$timestep = '1:00';
$event_length = '1:00';// (will be substracted from night_start for coloring night boxes)

/* No editing below this line please */

function time_to_minutes($time){
    if(strpos($time, ':') !== false){
        list($hour,$minutes) = explode(':', $time);
        return $hour*60+$minutes;
    } else {
        return $time;
    }
}

function minutes_to_time($minutes){
    $hour = floor($minutes/60);
    $minutes = $minutes % 60;
    return sprintf("%'.02d:%'.02d", $hour, $minutes);
}

$event_length = minutes_to_time(time_to_minutes($_GET['l'] ?? $event_length));//make sure time is double digits
$d = $_GET['d'] ?? date('Y-m-d');
$night_start = minutes_to_time(time_to_minutes($night_start)-time_to_minutes($event_length));
$timestep = time_to_minutes($timestep)/60;

?>

<html><head><title>Time Zone Table - <?=$d?></title><style>
* {
    font-family: Arial, Helvetica, Sans-Serif;
}
td, th {
    text-align: center;
    padding: 1px 5px;
}
td.night {
    background: #666;
    color: white;
}
tr:nth-child(odd){
    background-color: #ddd;
}
</style>
<head><body>
<h1>Time Zone Table - <?=$d?></h1>
<form method="get" action="<?=strtok($_SERVER['REQUEST_URI'], '?')?>">Date: <input type="date" name="d" value="<?=$d?>" /> Event length: <input type="time" name="l" value="<?=$event_length?>"/> <input type="submit" value="submit" /></form>

<table><thead><tr><th>UTC</th>

<?php
foreach($zones as $zone=>$id){
    echo '<th>'.$zone.'</th>';
}
echo '</tr></thead><tbody>';

for($opt=0;$opt<24;$opt+=$timestep){
    if(fmod($opt,1) === 0){
        $ts = $opt.':00';
    } else {
        $ts = floor($opt).':'.(fmod($opt,1)*60);
    }
    $t = new DateTime($d.' '.$ts, new DateTimeZone('UTC'));
    $d = $t->format('Ymd');
    echo '<tr><th>'.$t->format('H:i').'</th>';
    foreach($zones as $zone=>$id){
        $t->setTimezone(new DateTimeZone($id));
        $dt = $t->format('Ymd');
        echo '<td '
        . (($t->format('Hi') < str_replace(':', '', $night_end) || $t->format('Hi') > str_replace(':', '', $night_start)) ? 'class="night"' : '')
        . '>'.$t->format('H:i')
        . ($dt < $d 
            ? ' (-1)' 
            : ($dt > $d 
                ? ' (+1)'
                : ''))
        . '</td>';
    }
    echo '</tr>';
}
echo '</tbody></table>';
?>
<a href="https://github.com/MartenTacoma/tz"><img src="github.png" alt="Fork me on github" title="Fork me on github"/></a>
</body></html>