<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List Export</title>
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
        <h1>Students List</h1>
        <p>OnShelf GTDL Library Management System</p>
    </div>

    <div class="info">
        @if($searchQuery)
            <p><strong>Search:</strong> {{ $searchQuery }}</p>
        @endif
        <p><strong>Total Records:</strong> {{ $students->count() }}</p>
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>LRN</th>
                <th>Grade Level</th>
                <th>Section</th>
                <th>Mobile</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->userInfo->full_name ?? '—' }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $student->userInfo->lrn ?? '—' }}</td>
                    <td>{{ $student->userInfo->grade ?? '—' }}</td>
                    <td>{{ $student->userInfo->section ?? '—' }}</td>
                    <td>{{ $student->userInfo->mobile ?? '—' }}</td>
                    <td>{{ $student->deactivated ? 'Inactive' : 'Active' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #7c4c63;">
                        No students found.
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

