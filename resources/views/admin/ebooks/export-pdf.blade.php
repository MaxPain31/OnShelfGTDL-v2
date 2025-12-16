<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Books List Export</title>
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
        .info {
            margin-bottom: 15px;
            font-size: 11px;
            color: #7c4c63;
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
            font-size: 11px;
        }
        tbody tr:nth-child(even) {
            background-color: #fff7fb;
        }
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #f3cbe0;
            text-align: center;
            font-size: 10px;
            color: #7c4c63;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>E-Books List</h1>
        <p>OnShelf GTDL Library Management System</p>
    </div>

    <div class="info">
        @if($categoryFilter)
            <p><strong>Category:</strong> {{ $categoryFilter }}</p>
        @endif
        @if($searchQuery)
            <p><strong>Search:</strong> {{ $searchQuery }}</p>
        @endif
        <p><strong>Total Records:</strong> {{ $ebooks->count() }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Authors</th>
                <th>Category</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ebooks as $index => $ebook)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $ebook->title ?? '—' }}</td>
                    <td>{{ $ebook->authors ?? '—' }}</td>
                    <td>{{ $ebook->category ?? '—' }}</td>
                    <td>{{ $ebook->description ? strip_tags(substr($ebook->description, 0, 150)) . '...' : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; color: #7c4c63;">
                        No e-books found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This document was generated on {{ $generatedAt }} (Philippine Standard Time)</p>
    </div>
</body>
</html>

