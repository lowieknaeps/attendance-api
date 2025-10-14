<x-filament::page>
    {{ $this->form }}
    @if($rows)
        <x-filament::section heading="Preview">
            <x-filament::table>
                <x-slot name="content">
                    <table class="w-full">
                        <thead>
                        <tr>
                            <th>Student</th><th>Course</th><th>Present</th><th>Occurred at</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rows as $r)
                            <tr>
                                <td>{{ $r['student_id'] }}</td>
                                <td>{{ $r['course_id'] }}</td>
                                <td>{{ $r['present'] ? '✓' : '×' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($r['occurred_at'])->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </x-slot>
            </x-filament::table>
        </x-filament::section>
    @endif
</x-filament::page>
