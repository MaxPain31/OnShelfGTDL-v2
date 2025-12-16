<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List Export</title>
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
        <h1>Books List</h1>
        <p>OnShelf GTDL Library Management System</p>
    </div>

    <div class="info">
        @if($categoryFilter)
            <p><strong>Category:</strong> {{ $categoryFilter }}</p>
        @endif
        @if($searchQuery)
            <p><strong>Search:</strong> {{ $searchQuery }}</p>
        @endif
        <p><strong>Total Records:</strong> {{ $books->count() }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ISBN</th>
                <th>Book Name</th>
                <th>Authors</th>
                <th>Category</th>
                <th>Book Shelf</th>
                <th>Copyright</th>
                <th>Stock Quantity</th>
                <th>Publication</th>
            </tr>
        </thead>
        <tbody>
            @forelse($books as $index => $book)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $book->isbn ?? '—' }}</td>
                    <td>{{ $book->book_name ?? '—' }}</td>
                    <td>{{ $book->authors_name ?? '—' }}</td>
                    <td>{{ $book->category ?? '—' }}</td>
                    <td>{{ $book->book_shelf ?? '—' }}</td>
                    <td>{{ $book->copyright ?? '—' }}</td>
                    <td>{{ $book->stock_quantity ?? 0 }}</td>
                    <td>{{ $book->publication_name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #7c4c63;">
                        No books found.
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

