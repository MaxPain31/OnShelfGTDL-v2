@extends('layout.student.app')

@section('title', 'E-Books | OnShelf GTDL')
@section('page_title', 'E-Books')

@section('content')
    <div
        x-data='{
            selectedCategory: @json($selectedCategory ?? ''),
            searchQuery: @json($searchQuery ?? ''),
            showEbookModal: false,
            selectedEbook: null,
            isLoadingEbook: false,
            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const ebookId = urlParams.get("ebook");
                if (ebookId) {
                    this.openEbookModal(ebookId);
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            },
            filterEbooks() {
                const form = document.getElementById("filter-form");
                if (form) {
                    form.submit();
                }
            },
            async openEbookModal(ebookId) {
                this.isLoadingEbook = true;
                this.showEbookModal = true;
                try {
                    const response = await fetch("{{ route('student.ebooks.show', ':id') }}".replace(":id", ebookId), {
                        method: "GET",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.selectedEbook = data.ebook;
                        this.$nextTick(() => {
                            if (window.lucide) {
                                lucide.createIcons();
                                setTimeout(() => {
                                    this.updateFavoriteIcon();
                                }, 150);
                            }
                        });
                    }
                } catch (error) {
                    console.error("Error fetching ebook details:", error);
                } finally {
                    this.isLoadingEbook = false;
                }
            },
            closeEbookModal() {
                this.showEbookModal = false;
                this.selectedEbook = null;
            },
            async favoriteEbook() {
                if (!this.selectedEbook) return;

                const previousState = this.selectedEbook.is_favorited;
                this.selectedEbook.is_favorited = !this.selectedEbook.is_favorited;

                // Update icon immediately
                this.updateFavoriteIcon();

                try {
                    const csrfToken = document.querySelector("meta[name=csrf-token]")?.getAttribute("content");
                    const response = await fetch("{{ route('student.ebooks.favorite', ':id') }}".replace(":id", this.selectedEbook.id), {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.selectedEbook.is_favorited = data.is_favorited;
                        this.selectedEbook.favorite_count = data.favorite_count;
                        // Update icon to match server response
                        this.$nextTick(() => this.updateFavoriteIcon());
                    } else {
                        this.selectedEbook.is_favorited = previousState;
                        this.$nextTick(() => this.updateFavoriteIcon());
                    }
                } catch (error) {
                    console.error("Error toggling favorite:", error);
                    this.selectedEbook.is_favorited = previousState;
                    this.$nextTick(() => this.updateFavoriteIcon());
                }
            },
            updateFavoriteIcon() {
                if (!this.selectedEbook) return;

                // Find the favorite button
                const buttons = Array.from(document.querySelectorAll("button"));
                const favoriteBtn = buttons.find(btn => {
                    const text = btn.textContent.trim();
                    return text.includes("Favorite") || text.includes("Favorited");
                });

                if (!favoriteBtn) return;

                // Remove existing icon if any
                const existingIcon = favoriteBtn.querySelector("[data-lucide=heart]");
                if (existingIcon) {
                    existingIcon.remove();
                }

                // Create new icon element
                const icon = document.createElement("i");
                icon.setAttribute("data-lucide", "heart");
                icon.className = "w-4 h-4";

                if (this.selectedEbook.is_favorited) {
                    icon.classList.add("fill-current", "text-[#a03464]");
                } else {
                    icon.classList.add("text-[#7c4c63]");
                }

                // Insert icon before the text span
                const textSpan = favoriteBtn.querySelector("span");
                if (textSpan) {
                    favoriteBtn.insertBefore(icon, textSpan);
                } else {
                    favoriteBtn.appendChild(icon);
                }

                // Initialize Lucide icon
                if (window.lucide) {
                    lucide.createIcons();

                    // Update SVG fill/stroke based on favorite state
                    setTimeout(() => {
                        const svg = icon.querySelector("svg");
                        if (svg) {
                            const paths = svg.querySelectorAll("path");
                            if (this.selectedEbook.is_favorited) {
                                svg.setAttribute("fill", "currentColor");
                                svg.style.fill = "currentColor";
                                paths.forEach(path => {
                                    path.setAttribute("fill", "currentColor");
                                    path.style.fill = "currentColor";
                                    path.removeAttribute("stroke");
                                    path.style.stroke = "none";
                                });
                            } else {
                                svg.removeAttribute("fill");
                                svg.style.fill = "none";
                                paths.forEach(path => {
                                    path.removeAttribute("fill");
                                    path.style.fill = "none";
                                    path.setAttribute("stroke", "currentColor");
                                    path.setAttribute("stroke-width", "2");
                                    path.style.stroke = "currentColor";
                                    path.style.strokeWidth = "2";
                                });
                            }
                        }
                    }, 50);
                }
            },
        }'
        class="space-y-6"
        @keydown.escape.window="closeEbookModal()"
    >
        {{-- Search and Filter Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form id="filter-form" method="GET" action="{{ route('student.ebooks') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:max-w-2xl">
                {{-- Search Input --}}
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Search e-books by title, author, category, or description..."
                        value="{{ request('search') }}"
                        class="w-full rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2.5 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                        @keyup.enter="filterEbooks()"
                    />
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#a03464]/60"></i>
                </div>

                {{-- Category Filter --}}
                <div class="relative sm:w-48">
                    <select
                        name="category"
                        x-model="selectedCategory"
                        @change="filterEbooks()"
                        class="w-full rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2.5 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f] appearance-none cursor-pointer"
                    >
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#a03464]/60 pointer-events-none"></i>
                </div>

                {{-- Search Button --}}
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                >
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Search</span>
                </button>

                {{-- Clear Filters --}}
                @if (request('search') || request('category'))
                    <a
                        href="{{ route('student.ebooks') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2.5 text-sm font-medium text-[#7c4c63] hover:bg-[#fff7fb] transition"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                        <span>Clear</span>
                    </a>
                @endif
            </form>
        </div>

        {{-- E-Books by Category --}}
        @if ($ebooksByCategory->count() > 0)
            <div class="space-y-8">
                @foreach ($ebooksByCategory as $category => $ebooks)
                    <div class="space-y-4">
                        {{-- Category Header --}}
                        <div class="flex items-center gap-3 pb-2 border-b-2 border-[#f3cbe0]">
                            <h2 class="text-xl font-bold text-[#4b2036]">{{ $category ?? 'Uncategorized' }}</h2>
                            <span class="text-sm text-[#7c4c63] bg-[#fde7f0] px-3 py-1 rounded-full">
                                {{ $ebooks->count() }} {{ Str::plural('e-book', $ebooks->count()) }}
                            </span>
                        </div>

                        {{-- Gallery View --}}
                        <div class="grid gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                            @foreach ($ebooks as $ebook)
                                <article class="group relative flex flex-col gap-2 items-center text-center">
                                    <div
                                        @click="openEbookModal({{ $ebook->id }})"
                                        class="relative rounded-[10px] bg-[#fde7f0] border border-[#f3cbe0] overflow-hidden w-full max-w-[180px] mx-auto group-hover:scale-105 transition-transform duration-200 ease-out cursor-pointer"
                                        style="aspect-ratio: 2 / 3;"
                                    >
                                        @if ($ebook->ebook_image_path)
                                            <img
                                                src="{{ asset('storage/' . $ebook->ebook_image_path) }}"
                                                alt="{{ $ebook->title }}"
                                                class="w-full h-full object-cover"
                                            >
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i data-lucide="file-text" class="w-12 h-12 text-[#a03464]/60"></i>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200 flex items-center justify-center">
                                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <div class="bg-white/90 rounded-full p-3 shadow-lg">
                                                    <i data-lucide="eye" class="w-6 h-6 text-[#a03464]"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tooltip on Hover --}}
                                    <div class="pointer-events-none absolute -top-2 left-1/2 -translate-x-1/2 -translate-y-full opacity-0 group-hover:opacity-100 transition-opacity duration-150 ease-out z-10">
                                        <div class="relative rounded-md bg-white text-[#4b2036] text-xs shadow-lg border border-[#f3cbe0] px-3 py-2 min-w-[200px] max-w-[300px] text-left">
                                            <p class="leading-tight mb-1"><span class="font-semibold">Author:</span> {{ $ebook->authors ?? 'Unknown' }}</p>
                                            @if (!empty($ebook->description))
                                                <p class="leading-tight text-[#7c4c63] line-clamp-3 mt-2">{{ Str::limit($ebook->description, 100) }}</p>
                                            @endif
                                            @if ($ebook->ebook_file_path)
                                                <p class="leading-tight mt-2 text-green-600">
                                                    <i data-lucide="check-circle" class="w-3 h-3 inline"></i>
                                                    <span class="ml-1">Available to read</span>
                                                </p>
                                            @endif
                                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-0 translate-y-full">
                                                <div class="w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-white"></div>
                                                <div class="absolute left-1/2 -translate-x-1/2 top-0 w-0 h-0 border-l-[7px] border-l-transparent border-r-[7px] border-r-transparent border-t-[7px] border-t-[#f3cbe0] -z-10"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full max-w-[180px]">
                                        <h3 class="text-sm font-semibold text-[#4b2036] line-clamp-2 mb-1">{{ $ebook->title }}</h3>
                                        <p class="text-xs text-[#7c4c63] line-clamp-1">{{ $ebook->authors ?? 'Unknown Author' }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div class="text-center py-12">
                <i data-lucide="file-text" class="w-16 h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                <h2 class="text-xl font-semibold text-[#4b2036] mb-2">No e-books found</h2>
                <p class="text-sm text-[#7c4c63] mb-4">
                    @if (request('search') || request('category'))
                        Try adjusting your search or filter criteria.
                    @else
                        There are no e-books available at the moment.
                    @endif
                </p>
                @if (request('search') || request('category'))
                    <a
                        href="{{ route('student.ebooks') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                    >
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>View All E-Books</span>
                    </a>
                @endif
            </div>
        @endif

        {{-- E-Book Details Modal --}}
        <div
            x-show="showEbookModal"
            x-cloak
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="closeEbookModal()"
        >
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

            {{-- Modal --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    x-show="showEbookModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-6xl bg-white rounded-[24px] shadow-2xl overflow-hidden"
                    @click.stop
                >
                    {{-- Close Button --}}
                    <button
                        @click="closeEbookModal()"
                        class="absolute top-4 right-4 z-10 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 text-[#7c4c63] hover:bg-white hover:text-[#a03464] shadow-lg transition"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>

                    {{-- Loading State --}}
                    <div x-show="isLoadingEbook" class="flex items-center justify-center min-h-[400px]">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#a03464] mb-4"></div>
                            <p class="text-sm text-[#7c4c63]">Loading e-book details...</p>
                        </div>
                    </div>

                    {{-- E-Book Details Content --}}
                    <div x-show="!isLoadingEbook && selectedEbook">
                        <div class="grid md:grid-cols-2 gap-0">
                            {{-- Left Side: E-Book Image --}}
                            <div class="bg-gradient-to-br from-[#fde7f0] to-[#fff7fb] p-8 flex items-center justify-center">
                                <div class="w-full max-w-sm">
                                    <div class="relative rounded-[16px] bg-white shadow-xl overflow-hidden" style="aspect-ratio: 2 / 3;">
                                        <template x-if="selectedEbook && selectedEbook.ebook_image_path">
                                            <img
                                                :src="selectedEbook.ebook_image_path"
                                                :alt="selectedEbook.title"
                                                class="w-full h-full object-cover"
                                            >
                                        </template>
                                        <template x-if="selectedEbook && !selectedEbook.ebook_image_path">
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i data-lucide="file-text" class="w-24 h-24 text-[#a03464]/60"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Side: E-Book Information --}}
                            <div class="p-8 overflow-y-auto max-h-[90vh]">
                                <div class="space-y-6">
                                    {{-- Title and Category --}}
                                    <div>
                                        <h2 class="text-3xl font-bold text-[#4b2036] mb-2" x-text="selectedEbook ? selectedEbook.title : ''"></h2>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="inline-flex items-center gap-1 text-sm text-[#a03464] bg-[#fde7f0] px-3 py-1 rounded-full"
                                                x-text="selectedEbook ? (selectedEbook.category || 'Uncategorized') : ''"
                                            ></span>
                                            <span
                                                class="inline-flex items-center gap-1 text-xs text-[#7c4c63] bg-[#f3cbe0] px-2 py-1 rounded-full"
                                                x-show="selectedEbook && selectedEbook.view_count !== undefined"
                                            >
                                                <i data-lucide="eye" class="w-3 h-3"></i>
                                                <span x-text="selectedEbook ? selectedEbook.view_count : 0"></span> views
                                            </span>
                                            <span
                                                class="inline-flex items-center gap-1 text-xs text-[#7c4c63] bg-[#f3cbe0] px-2 py-1 rounded-full"
                                                x-show="selectedEbook && selectedEbook.favorite_count !== undefined"
                                            >
                                                <i data-lucide="heart" class="w-3 h-3"></i>
                                                <span x-text="selectedEbook ? selectedEbook.favorite_count : 0"></span> favorites
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Author --}}
                                    <div>
                                        <p class="text-sm font-semibold text-[#7c4c63] mb-1">Author</p>
                                        <p class="text-lg text-[#4b2036]" x-text="selectedEbook ? (selectedEbook.authors || 'Unknown') : ''"></p>
                                    </div>

                                    {{-- Description --}}
                                    <div x-show="selectedEbook && selectedEbook.description" class="pt-4 border-t border-[#f3cbe0]">
                                        <p class="text-sm font-semibold text-[#7c4c63] mb-2">Description</p>
                                        <p class="text-sm text-[#4b2036] leading-relaxed whitespace-pre-wrap" x-text="selectedEbook ? selectedEbook.description : ''"></p>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="pt-6 border-t border-[#f3cbe0] space-y-3">
                                        <template x-if="selectedEbook && selectedEbook.ebook_file_path">
                                            <a
                                                :href="'{{ route('student.ebooks.read', ':id') }}'.replace(':id', selectedEbook.id)"
                                                class="w-full flex items-center justify-center gap-2 rounded-[12px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                                            >
                                                <i data-lucide="book-open" class="w-5 h-5"></i>
                                                <span>Read this book</span>
                                            </a>
                                        </template>
                                        <template x-if="selectedEbook && !selectedEbook.ebook_file_path">
                                            <div class="w-full flex items-center justify-center gap-2 rounded-[12px] bg-gray-100 px-6 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed">
                                                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                                                <span>File not available</span>
                                            </div>
                                        </template>
                                        <button
                                            @click="favoriteEbook()"
                                            class="w-full flex items-center justify-center gap-2 rounded-[12px] border-2 border-[#f3cbe0] bg-white px-4 py-3 text-sm font-semibold text-[#7c4c63] hover:bg-[#fff7fb] transition"
                                            x-show="selectedEbook"
                                        >
                                            <span x-text="selectedEbook ? (selectedEbook.is_favorited ? 'Favorited' : 'Favorite') : 'Favorite'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
@endsection
