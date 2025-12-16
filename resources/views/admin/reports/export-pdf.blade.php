<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #4b2036;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #a03464;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #a03464;
            font-size: 20px;
            margin-bottom: 3px;
        }
        .header p {
            color: #7c4c63;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th {
            background-color: #fde7f0;
            color: #a03464;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #f3cbe0;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #f3cbe0;
        }
        tr:nth-child(even) {
            background-color: #fff7fb;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .stat-box {
            border: 1px solid #f3cbe0;
            padding: 15px;
            background-color: #fff7fb;
            border-radius: 5px;
        }
        .stat-box h3 {
            color: #a03464;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .stat-box .value {
            font-size: 20px;
            font-weight: bold;
            color: #4b2036;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #f3cbe0;
            text-align: center;
            color: #7c4c63;
            font-size: 10px;
        }
        .chart-container {
            margin: 15px 0;
        }
        .chart-title {
            font-size: 14px;
            font-weight: bold;
            color: #a03464;
            margin-bottom: 10px;
            text-align: center;
        }
        .bar-chart {
            display: table;
            width: 100%;
            margin: 10px 0;
        }
        .bar-item {
            display: table-row;
            page-break-inside: avoid;
        }
        .bar-label {
            display: table-cell;
            padding: 3px 8px;
            font-size: 9px;
            width: 18%;
            vertical-align: middle;
        }
        .bar-wrapper {
            display: table-cell;
            width: 65%;
            padding: 3px 8px;
            vertical-align: middle;
        }
        .bar {
            height: 18px;
            background-color: #a03464;
            border-radius: 2px;
            display: inline-block;
            min-width: 2px;
        }
        .bar-value {
            display: table-cell;
            padding: 3px 8px;
            font-size: 9px;
            text-align: right;
            width: 17%;
            vertical-align: middle;
            font-weight: bold;
        }
        .line-chart {
            margin: 20px 0;
            position: relative;
            height: 200px;
            border-bottom: 2px solid #f3cbe0;
            border-left: 2px solid #f3cbe0;
        }
        .line-point {
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #a03464;
            transform: translate(-50%, 50%);
        }
        .line-segment {
            position: absolute;
            height: 2px;
            background-color: #a03464;
            transform-origin: left center;
        }
        .line-labels {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .line-label {
            display: table-cell;
            text-align: center;
            font-size: 9px;
            color: #7c4c63;
        }
        .pie-chart {
            margin: 20px auto;
            width: 300px;
            height: 300px;
            position: relative;
        }
        .pie-segment {
            position: absolute;
            width: 100%;
            height: 100%;
            clip-path: polygon(50% 50%, 50% 0%, 100% 0%, 100% 100%, 50% 100%);
        }
        .pie-legend {
            margin-top: 20px;
            display: table;
            width: 100%;
        }
        .pie-legend-item {
            display: table-row;
        }
        .pie-legend-color {
            display: table-cell;
            width: 20px;
            height: 20px;
            padding: 5px;
        }
        .pie-legend-label {
            display: table-cell;
            padding: 5px;
            font-size: 11px;
        }
        .pie-legend-value {
            display: table-cell;
            padding: 5px;
            font-size: 11px;
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Generated on {{ $generatedAt }}</p>
    </div>

    @if(isset($chartData) && $chartData)
        <div class="chart-container">
            <div class="chart-title">Chart Visualization</div>
            @if($chartData['type'] === 'bar')
                <div class="bar-chart">
                    @php
                        $maxValue = max($chartData['datasets'][0]['data']);
                        $maxValue = $maxValue > 0 ? $maxValue : 1;
                    @endphp
                    @foreach($chartData['labels'] as $index => $label)
                        <div class="bar-item">
                            <div class="bar-label">{{ $label }}</div>
                            <div class="bar-wrapper">
                                <div class="bar" style="width: {{ ($chartData['datasets'][0]['data'][$index] / $maxValue) * 100 }}%; background-color: {{ $chartData['datasets'][0]['color'] }};"></div>
                            </div>
                            <div class="bar-value">{{ $chartData['datasets'][0]['data'][$index] }}</div>
                        </div>
                    @endforeach
                </div>
            @elseif($chartData['type'] === 'line')
                @php
                    $maxValue = 0;
                    foreach($chartData['datasets'] as $dataset) {
                        $maxValue = max($maxValue, max($dataset['data']));
                    }
                    $maxValue = $maxValue > 0 ? $maxValue : 1;
                @endphp
                @foreach($chartData['datasets'] as $datasetIndex => $dataset)
                    @if($datasetIndex > 0)
                        <div style="margin-top: 15px;"></div>
                    @endif
                    <div style="margin-bottom: 8px; font-weight: bold; color: {{ $dataset['color'] }}; font-size: 12px;">{{ $dataset['label'] }}</div>
                    <div class="bar-chart">
                        @foreach($chartData['labels'] as $index => $label)
                            <div class="bar-item">
                                <div class="bar-label">{{ $label }}</div>
                                <div class="bar-wrapper">
                                    <div class="bar" style="width: {{ ($dataset['data'][$index] / $maxValue) * 100 }}%; background-color: {{ $dataset['color'] }};"></div>
                                </div>
                                <div class="bar-value">{{ $dataset['data'][$index] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @elseif($chartData['type'] === 'pie')
                @php
                    $total = array_sum($chartData['data']);
                    $maxValue = max($chartData['data']);
                    $maxValue = $maxValue > 0 ? $maxValue : 1;
                @endphp
                <div class="bar-chart">
                    @foreach($chartData['labels'] as $index => $label)
                        @php
                            $percentage = $total > 0 ? ($chartData['data'][$index] / $total) * 100 : 0;
                        @endphp
                        <div class="bar-item">
                            <div class="bar-label">{{ $label }}</div>
                            <div class="bar-wrapper">
                                <div class="bar" style="width: {{ ($chartData['data'][$index] / $maxValue) * 100 }}%; background-color: {{ $chartData['colors'][$index] }};"></div>
                            </div>
                            <div class="bar-value">{{ $chartData['data'][$index] }} ({{ number_format($percentage, 1) }}%)</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($type === 'overall-stats')
        <div class="stats-grid">
            @foreach($data as $key => $value)
                <div class="stat-box">
                    <h3>{{ ucwords(str_replace('_', ' ', $key)) }}</h3>
                    <div class="value">{{ number_format($value) }}</div>
                </div>
            @endforeach
        </div>
    @elseif($type === 'year-comparison')
        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>This Year</th>
                    <th>Last Year</th>
                    <th>Growth (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row['metric'] }}</td>
                        <td>{{ number_format($row['this_year']) }}</td>
                        <td>{{ number_format($row['last_year']) }}</td>
                        <td>{{ $row['growth'] >= 0 ? '+' : '' }}{{ number_format($row['growth'], 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    @if(!empty($data))
                        @foreach(array_keys($data[0]) as $header)
                            <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach($row as $value)
                            <td>{{ is_numeric($value) && strpos($value, '%') === false ? number_format($value) : $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>OnShelf GTDL - Library Management System</p>
    </div>
</body>
</html>

