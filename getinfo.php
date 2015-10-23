<?php
    // 選択した駅の駅コードを取得
    $get_sName = $_POST["s1"];

    // 駅データのAPIに駅コードで検索をかける
    $s_url = "http://www.ekidata.jp/api/s/" . $get_sName . ".xml";

    // 選択した駅の情報を取得
    $s_json_data = file_get_contents($s_url, true);
    //var_dump($s_json_data);

    // XML形式で取得したので、読み取れる形に変換
    $s_data = new SimpleXMLElement($s_json_data);
    //var_dump($s_data);

    // ライン名、駅名、緯度経度の取得
    $l_name = $s_data->station[0]->line_name;
    $st_name = $s_data->station[0]->station_name;
    $s_lon = $s_data->station[0]->lon;
    $s_lat = $s_data->station[0]->lat;

    // ぐるなびAPIで店を検索
    require("config.php");
    $uri = "http://api.gnavi.co.jp/RestSearchAPI/20150630/";
    $acckey = $key_id;
    $format = "json";
    $s_show = 200;
    $s_range = 3;

    //$url = "http://api.gnavi.co.jp/RestSearchAPI/20150630/?keyid=" .$acckey. "&latitude=" .$s_lat. "&longitude=" .$s_lon. "& range=3&hit_per_page=" .&s_show. "&format=json";
    //var_dump($url)

    // APIに渡す引数を指定
    $url = sprintf("%s%s%s%s%s%s%s%s%s%s%s%s%s", $uri, "?format=", $format, "&keyid=", $acckey, "&latitude=", $s_lat,"&longitude=",$s_lon,"&range=",$s_range,"&hit_per_page=",$s_show);    
    //var_dump($url);    

    // APIからデータを取得
    $json_data = file_get_contents($url, true);
    //var_dump($json_data);

    $d_data = json_decode($json_data);
    //var_dump($d_data);

    $s_name[] = null;
    $s_lat[] = null;
    $s_lon[] = null;
    $s_urllist[] = null;

    // 選択した駅周辺のお店の名前、緯度経度、URL情報を配列にて管理
    for($i=0; $i < $s_show; $i++){
        $s_name[$i]=$d_data->rest[$i]->name;
        $s_lat[$i]=$d_data->rest[$i]->latitude;
        $s_lon[$i]=$d_data->rest[$i]->longitude;
        $s_urllist[$i]=$d_data->rest[$i]->url;
        
        //var_dump($s_name[$i]);
        //var_dump($s_lat[$i]);
        //var_dump($s_lon[$i]);
        //var_dump($d_data->rest[$i]->name);
        //var_dump($d_data->rest[$i]->address);
        //echo "<hr>";
    }

?>

<html lang="ja">
    <head>
        <style>
            #container{
                margin-top: 20px;
            }
            #h_label{
                margin-left: 20px;
            }
            #s_info{
                margin-top: 20px;
                margin-bottom: 20px;
                margin-left: 20px;
            }
        </style>
        <script src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <script>
            var map = null;
            var marker = null;
            window.addEventListener("load", function(){
                // ライン名、駅名の情報取得
                var line_name = "<?php print $l_name; ?>";
                var station_name = "<?php print $st_name; ?>";

                // ライン名、駅名の情報をHTML上に表示
                document.getElementById("line_name").innerHTML = line_name;
                document.getElementById("station_name").innerHTML = station_name;
                                
                // 選択した駅の緯度経度の情報取得
                var lat = <?php print $s_lat ?>;
                var lon = <?php print $s_lon ?>;
                
                // 選択した駅周辺情報をマップに表示
                var map = new google.maps.Map(
                    document.getElementById("myGoogleMap"),{
                        zoom: 16,
                        center: new google.maps.LatLng(lat, lon),
                        //center: new google.maps.LatLng(36, 135),
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    }
                );
                
                if(!navigator.geolocation){
                    return;
                }
                
                var ss_name = <?php print json_encode($s_name); ?>;
                var ss_lat = <?php print json_encode($s_lat); ?>;
                var ss_lon = <?php print json_encode($s_lon); ?>;
                var ss_url = <?php print json_encode($s_urllist); ?>;
                var s_count = <?php print $s_show; ?>;
            
                // 選択した駅周辺のお店をMakerにて表示
                for(var i=0; i < s_count; i++){
                    var sp_name = ss_name[i];
                    var sp_lat = ss_lat[i];
                    var sp_lon = ss_lon[i];
                    var sp_url = ss_url[i];
                    
                    var currentPosition = new google.maps.LatLng(sp_lat, sp_lon);

                    // 新規にマーカーを表示する
                    /*
                    if (marker){
                        marker.setMap(null);	// マーカーを削除
                    }
                    */
                    marker = new google.maps.Marker({
                        position: currentPosition,
                        title: sp_name,
                        map: map
                    });
                    
                    //クリックしたら指定したurlに遷移するイベント
                    google.maps.event.addListener(marker, 'click', (function(url){
                            return function(){location.href = url;};
                    })(sp_url));
                    
                }
                    
            });
        </script>

    </head>
    <body>
        <div id="container">
            <h3 id="h_label">選択した駅周辺のお店を検索します</h3>
            <table id="s_info">
                <tr>
                    <td width="100">
                        ライン名：
                    </td>
                    <td>
                        <div id="line_name"></div>
                    </td>
                </tr>
                <tr>
                    <td width="100">
                        駅名：
                    </td>
                    <td>
                        <div id="station_name"></div>
                    </td>
                </tr>
            </table>
            <div id="myGoogleMap"  style="width:100%; height:90%; border: 1px solid black;">
            </div>
        </div>
    </body>
</head>