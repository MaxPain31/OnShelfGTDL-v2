@extends('layout.student.app')

@section('title', 'Rules & Regulation | OnShelf GTDL')
@section('page_title', 'Rules & Regulation')

@section('content')
    <div
        x-data="{}"
        x-init="if (window.lucide) { lucide.createIcons(); }"
        class="space-y-6"
    >
        @if($rules->count() > 0)
            <div class="space-y-4">
                @foreach($rules as $index => $rule)
                    <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-6 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-[#e07aac] to-[#a03464] flex items-center justify-center text-white font-bold text-lg">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-[#4b2036] mb-2">
                                    {{ html_entity_decode($rule->title) }}
                                </h3>
                                <p class="text-sm text-[#7c4c63] leading-relaxed whitespace-pre-line">
                                    {{ html_entity_decode($rule->description) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-[24px] border border-[#f3cbe0] bg-white p-12 text-center">
                <i data-lucide="shield-check" class="w-16 h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                <h2 class="text-xl font-semibold text-[#4b2036] mb-2">No Rules Available</h2>
                <p class="text-sm text-[#7c4c63]">Library rules and regulations will be displayed here once they are added by the administrator.</p>
            </div>
        @endif
    </div>
@endsection
