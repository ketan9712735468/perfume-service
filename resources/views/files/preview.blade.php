@if(!empty($data))
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                @foreach($data[0] as $header)
                    <th class="py-2 px-4 border-b border-gray-200">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($data, 1) as $row)
                <tr>
                    @foreach($row as $cell)
                        <td class="py-2 px-4 border-b border-gray-200">{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No data available.</p>
@endif