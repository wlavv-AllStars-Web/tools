@if(empty($embedded))
<!DOCTYPE html>
<html>
<head>
    <title>KPI ALL STARS GROUP</title>
    <script>
        setTimeout(function(){ window.location.reload(1); }, 300000);
    </script>
@endif
    <style>
        body {
            background: #e5e5e5;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .kpi-dashboard {
            height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 10px;
            box-sizing: border-box;
            overflow: hidden;
        }

        .panel {
            display: grid;
            gap: 10px;
            min-height: 0;
            box-sizing: border-box;
        }

        .panel.top {
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            height: 18vh;
        }

        .panel.middle {
            grid-template-columns: 22% 56% 22%;
            height: 37vh;
        }

        .panel.bottom {
            grid-template-columns: repeat(2, 1fr);
            height: 43vh;
        }

        .card {
            background: #FFF;
            border: 2px solid #c2c2c2;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 0;
            box-sizing: border-box;
            overflow: hidden;
        }

        .counter {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            font-size: 60px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label {
            margin-top: 10px;
            font-size: 22px;
            font-weight: bold;
        }
        .awaiting { background: #8A2BE2; color:#fff; }
        .packing { background: #048DCD; color:#fff; }
        .shipping_area { background: #E122BC; color:#fff; }
        .shipped { background: #00644A; color:#fff; }
        .warranty { background: #EBC5E0; color:#111; }
        .backorders { background: #F78E1F; color:#fff; }
        .pending { background: #ACACAC; color:#111; }

        .title {
            font-size: 28px;
            color: #666;
        }

        .section-title {
            font-size: clamp(24px, 2.6vw, 36px);
            color: dodgerblue;
            line-height: 1;
            white-space: nowrap;
        }

        .bigNumber {
            font-size: clamp(42px, 3.2vw, 72px);
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
        }

        .mediumNumber {
            font-size: clamp(36px, 3.2vw, 58px);
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
        }

        .groupResult {
            font-size: clamp(48px, 5vw, 86px);
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
        }

        .progressText {
            font-size: clamp(24px, 2.2vw, 34px);
            margin-top: 8px;
            line-height: 1.1;
            white-space: nowrap;
        }

        .ok { color: darkgreen; }
        .nok { color: red; }
        .top { color: dodgerblue; }

        .currency-bar {
            display: flex;
            justify-content: center;
            gap: 42px;
            margin-top: 10px;
            font-size: clamp(24px, 2vw, 32px);
            border-top: 2px solid #ccc;
            padding-top: 8px;
            line-height: 1;
            white-space: nowrap;
        }

        .daily-card-link {
            color: #5c6670;
            font-size: 58px;
            line-height: 1;
            text-align: center;
            text-decoration: none;
        }

        .daily-card-link .label {
            font-size: 22px;
        }

        .shop-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 15px;
        }

        .logo-container {
            height: 35%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-container img {
            max-height: 150px;
            max-width: 100%;
            object-fit: contain;
        }

        .shop-content {
            height: 65%;
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            width: 100%;
            text-align: center;
        }

        .shop-card .section-title {
            font-size: clamp(24px, 2vw, 30px);
        }

        .shop-card .mediumNumber {
            font-size: clamp(34px, 3vw, 54px);
        }

.goal-result {
    display: flex;
    justify-content: center;
    gap: 60px;
    margin-top: 10px;
    font-size: clamp(26px, 2.2vw, 36px);
}        
    </style>
@if(!empty($embedded))
    <style>.kpi-dashboard { height: auto; min-height: 900px; width: 100%; }</style>
@else
</head>
<body>
@endif
    <div class="kpi-dashboard">
        <div class="panel top">
            @foreach([
                ['awaiting',$awaiting, 'AWAITING'],
                ['packing',$packing],
                ['shipped',$shipped],
                ['warranty',$warranty],
                ['backorders',$backorders],
                ['pending',$pending],
            ] as $item)
                <div class="card">
                    <div class="counter {{ $item[0] }}">{{ $item[1] }}</div>
                    <div class="label">{{ $item[2] ?? strtoupper($item[0]) }}</div>
                </div>
            @endforeach
            @if(!empty($canUseDailyDashboard))
                <div class="card">
                    <a class="daily-card-link" href="#" onclick="getDailyStats(); return false;">
                        <div><i class="fa-solid fa-chart-simple"></i></div>
                        <div class="label">DAILY</div>
                    </a>
                </div>
            @endif
        </div>
        <div class="panel middle">
            <div class="card">
                <div class="title">TODAY</div>
                <div class="section-title">FORCAST</div>
                <div class="bigNumber">{{$today->forcast}}</div>
                <div class="section-title" style="margin-top: 40px;">REALIZED</div>
                <div class="bigNumber {{ $today->reached ? 'ok' : 'nok' }}"> {{$today->realized}} </div>
            </div>
            <div class="card">
                <div class="section-title">GROUP RESULT</div>
                <div class="groupResult">{{$group_result}}</div>
                @php
                    $statusClass = 'nok';
                
                    if ($status >= 120) {
                        $statusClass = 'top';   // azul
                    } elseif ($status >= 100) {
                        $statusClass = 'ok';    // verde
                    }
                @endphp

                <div class="progressText {{ $statusClass }}"> {{ number_format($status,2,',',' ') }} % </div>
                <div class="goal-result">
                    <div class="progressText">
                        GOAL: {{ number_format($objective_until_today,2,',',' ') }} €
                    </div>
                
                    <div class="progressText {{ $realised_until_today > $objective_until_today ? 'ok' : 'nok' }}">
                        RESULT: {{ number_format($realised_until_today,2,',',' ') }} €
                    </div>
                </div>
                <div class="currency-bar">
                    <div>元 {{$yuan}}</div>
                    <div>£ {{$pound}}</div>
                    <div>$ {{$dollar}}</div>
                    <div>¥ {{$yen}}</div>
                </div>
            </div>
            <div class="card">
                <div class="title">YESTERDAY</div>
                <div class="section-title">FORCAST</div>
                <div class="bigNumber">{{$yesterday->forcast}}</div>
                <div class="section-title" style="margin-top: 40px;">REALIZED</div>
                <div class="bigNumber {{ $yesterday->reached ? 'ok' : 'nok' }}"> {{$yesterday->realized}} </div>
            </div>
        </div>
        <div class="panel bottom">
            @foreach([
                ['asd',$forcast_asd,$realized_asd,$reached_asd],
                ['asm',$forcast_asm,$realized_asm,$reached_asm],
            ] as $shop)
                <div class="card shop-card">
                    <div class="logo-container"> <img src="/uploads/logos/{{$shop[0]}}-350x250.png?v=1"> </div>
                    <div class="shop-content">
                        <div>
                            <div class="section-title">FORCAST</div>
                            <div class="mediumNumber">{{$shop[1]}}</div>
                        </div>
                        <div>
                            <div class="section-title">REALIZED</div>
                            <div class="mediumNumber {{ $shop[3] ? 'ok' : 'nok' }}">
                                {{$shop[2]}}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@if(empty($embedded))
</body>
</html>
@endif
