<div {{ $attributes }}>
    <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
            {{ $getName() }}
        </span>
    </dt>
    <div class="flex flex-col space-y-0.5 mt-2">
        @foreach($getLessons() as $index => $lesson)
            <div @class([
                    'rounded px-2',
                    'bg-primary-600 text-white' => $isActive($lesson),
                    'hover:bg-gray-100 hover:text-primary-600' => !$isActive($lesson)
                ])>
                <a href="{{ $getUrl($lesson) }}" class="flex flex-row">
                    <div class="w-5 mr-2 text-right shrink-0 font-mono">
                        {{ $index + 1 }}
                    </div>
                    <div>
                        {{ $lesson->title }}
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
