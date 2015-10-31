<!-- try用のデータです-->

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
    $s_show = 500;
    $s_range = 3;
    $_input_mode = 2;
    $s_mode = 2;

    // APIに渡す引数を指定
    $url = sprintf("%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s", $uri, "?format=", $format, "&keyid=", $acckey, "&latitude=", $s_lat,"&longitude=",$s_lon,"&range=",$s_range,"&hit_per_page=",$s_show,"&input_coordinates_mode=",$_input_mode,"&coordinates_mode=",$s_mode);

    // APIからデータを取得
    $json_data = file_get_contents($url, true);
    $d_data = json_decode($json_data);

    $s_name[] = null;
    $s_lat[] = null;
    $s_lon[] = null;
    $s_urllist[] = null;

    $hit_count = $d_data->total_hit_count;

    if($hit_count < $s_show){
        $s_count = $hit_count;
    }
    else{
        $s_count = $s_show;
    }

    // 選択した駅周辺のお店の名前、緯度経度、URL情報を配列にて管理
    for($i=0; $i < $s_count; $i++){
        $s_name[$i]=$d_data->rest[$i]->name;
        $s_lat[$i]=$d_data->rest[$i]->latitude;
        $s_lon[$i]=$d_data->rest[$i]->longitude;
        $s_urllist[$i]=$d_data->rest[$i]->url;
    }
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>都道府県から沿線/駅表示プルダウン表示</title>

        <script src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <script>
            var map = null;
            var marker = null;
            window.addEventListener("load", function(){
                        
                // 選択した駅周辺情報をマップに表示
                var map = new google.maps.Map(
                    document.getElementById("myGoogleMap"),{
                        zoom: 5,
                        center: new google.maps.LatLng(36, 135),
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    }
                );
                
                if(!navigator.geolocation){
                    return;
                }
                    
            });
        </script>

        <script type="text/javascript"><!--
            function setMenuItem(type,code){

                var s = document.getElementsByTagName("head")[0].appendChild(document.createElement("script"));
                s.type = "text/javascript";
                s.charset = "utf-8";

                var optionIndex0 = document.form.s0.options.length;	//沿線のOPTION数取得
                var optionIndex1 = document.form.s1.options.length;	//駅のOPTION数取得

                if (type == 0){
                    for ( i=0 ; i <= optionIndex0 ; i++ ){
                        document.form.s0.options[0]=null
                    }	//沿線削除

                    for ( i=0 ; i <= optionIndex1 ; i++ ){
                        document.form.s1.options[0]=null
                    }	//駅削除

                    document.form.s1.options[0] = new Option("----",0);	//駅OPTIONを空に

                    if (code == 0){
                        document.form.s0.options[0] = new Option("----",0);	//沿線OPTIONを空に
                    }
                    else{
                        s.src = "http://www.ekidata.jp/api/p/" + code + ".json";	//沿線JSONデータURL
                    }
                }
                else{
                    for ( i=0 ; i <= optionIndex1 ; i++ ){
                        document.form.s1.options[0]=null
                    }	//駅削除

                    if (code == 0){
                        document.form.s1.options[0] = new Option("----",0);	//駅OPTIONを空に
                    }
                    else{
                        s.src = "http://www.ekidata.jp/api/l/" + code + ".json";	//駅JSONデータURL
                    }
                }
            }

            var xml = {};
            xml.onload = function(data){
                var line = data["line"];
                var station_l = data["station_l"];

                if(line != null){
                    document.form.s0.options[0] = new Option("----",0);	//OPTION1番目はNull

                    for( i=0; i<line.length; i++){
                        ii = i + 1	//OPTIONは2番目から表示
                        var op_line_name = line[i].line_name;
                        var op_line_cd = line[i].line_cd;
                        document.form.s0.options[ii] = new Option(op_line_name,op_line_cd);
                    }
                }

                if(station_l != null){
                    document.form.s1.options[0] = new Option("----",0);	//OPTION1番目はNull

                    for( i=0; i<station_l.length; i++){
                        ii = i + 1	//OPTIONは2番目から表示
                        var op_station_name = station_l[i].station_name;
                        var op_station_cd = station_l[i].station_cd;
                        document.form.s1.options[ii] = new Option(op_station_name,op_station_cd);
                    }
                }
            }
        // -->
        </script>
        <style>
            #container{
                width: 800px;
                margin: 50px auto 0 auto;
            }
        </style>
    </head>
    <body>
        <div id="container">
            都道府県から沿線/駅表示プルダウン表示<br>
            <!--<form name="form">-->
            <form name="form" action="index.php" method="post">
                <lable width="50">都道府県：</lable>
                <select name="pref" onChange="setMenuItem(0,this[this.selectedIndex].value)">
                    <option value="0" selected>-----
                    <option value="1">北海道
                    <option value="2">青森県
                    <option value="3">岩手県
                    <option value="4">宮城県
                    <option value="5">秋田県
                    <option value="6">山形県
                    <option value="7">福島県
                    <option value="8">茨城県
                    <option value="9">栃木県
                    <option value="10">群馬県
                    <option value="11">埼玉県
                    <option value="12">千葉県
                    <option value="13">東京都
                    <option value="14">神奈川県
                    <option value="15">新潟県
                    <option value="16">富山県
                    <option value="17">石川県
                    <option value="18">福井県
                    <option value="19">山梨県
                    <option value="20">長野県
                    <option value="21">岐阜県
                    <option value="22">静岡県
                    <option value="23">愛知県
                    <option value="24">三重県
                    <option value="25">滋賀県
                    <option value="26">京都府
                    <option value="27">大阪府
                    <option value="28">兵庫県
                    <option value="29">奈良県
                    <option value="30">和歌山県
                    <option value="31">鳥取県
                    <option value="32">島根県
                    <option value="33">岡山県
                    <option value="34">広島県
                    <option value="35">山口県
                    <option value="36">徳島県
                    <option value="37">香川県
                    <option value="38">愛媛県
                    <option value="39">高知県
                    <option value="40">福岡県
                    <option value="41">佐賀県
                    <option value="42">長崎県
                    <option value="43">熊本県
                    <option value="44">大分県
                    <option value="45">宮崎県
                    <option value="46">鹿児島県
                    <option value="47">沖縄県
                </select>

                <lable width="50">路線名：</lable>
                <select name="s0" onChange="setMenuItem(1,this[this.selectedIndex].value)">
                    <option selected>----
                </select> 

                <lable width="50">駅名：</lable>
                <select name="s1">
                    <option selected>----
                </select>

                <input type="submit" id="jadge" name="jadge" value="決定">
            </form>
            
            <div id="myGoogleMap" style="width:800px; height:500px;">
            </div>
        </div>
    </body>
    
</html>