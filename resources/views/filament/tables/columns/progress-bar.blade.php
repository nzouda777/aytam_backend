@php
    $goal = $getRecord()->goal_amount;
    $current = $getRecord()->current_amount;
    $percentage = $goal > 0 ? min(100, round(($current / $goal) * 100)) : 0;
    $color = $percentage >= 100 ? '#10b981' : ($percentage >= 50 ? '#3b82f6' : '#f59e0b');
@endphp

<div class="flex items-center gap-2">
    <div class="w-24 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
        <div class="h-2.5 rounded-full" style="width: {{ $percentage }}%; background-color: {{ $color }}"></div>
    </div>
    <span class="text-xs font-medium" style="color: {{ $color }}">{{ $percentage }}%</span>
</div>
