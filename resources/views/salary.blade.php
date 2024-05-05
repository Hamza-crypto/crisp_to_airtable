<!DOCTYPE html>
<html>

<head>
    <title>Salary</title>
    <!-- <meta http-equiv="refresh" content="3;"> -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
</head>

<body>

    {{-- <div class="chart-container" style="position: relative; height:400px; width:1200px">
        <canvas id="myChart"></canvas>
    </div> --}}

    <form action="{{ route('salary.store') }}" method="POST">
        @csrf()
        @method('POST')
        <label for="amount">Amount</label>
        <input type="number" name="amount">

        <input type="submit" value="Add">
    </form>



    <h1>Recent Entries</h1>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($salaries as $entry)
                <tr>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d') }}</td>
                    <td>
                        <form action="{{ route('delete.salary', $entry->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
