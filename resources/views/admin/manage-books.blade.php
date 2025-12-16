@extends('layout.admin.app')

@php
    $pageTitle = 'Manage Books';
    $labelClass = 'block text-xs font-semibold text-[#7c4c63] mb-1';
    $inputClass = 'w-full rounded-[10px] border border-[#f3cbe0] bg-[#fff7fb] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]';
@endphp

@section('title', $pageTitle . ' | OnShelf GTDL')
@section('page_title', $pageTitle)

@section('content')
    <div
        x-data="{
            showAddModal: {{ $errors->any() && old('_token') ? 'true' : 'false' }},
            showEditModal: {{ isset($editingBook) && $editingBook ? 'true' : 'false' }},
            editingBookId: {{ isset($editingBook) && $editingBook ? $editingBook->id : 'null' }},
            editingBook: {{ isset($editingBook) && $editingBook ? json_encode($editingBook) : 'null' }},
            viewMode: 'gallery',
            statusMessage: {{ json_encode(session('status') ?? '') }},
            showConfirmModal: false,
            confirmAction: { formId: null, message: '' },
            isSavingBook: false,
            isAddingBook: false,
            formErrors: {},
            successMessage: '',
            showSuccessMessage: false,
            openConfirm(formId, message) {
                this.confirmAction = { formId, message };
                this.showConfirmModal = true;
                if (window.lucide) setTimeout(() => lucide.createIcons(), 100);
            },
            closeConfirm() {
                this.confirmAction = { formId: null, message: '' };
                this.showConfirmModal = false;
            },
            submitConfirm() {
                if (this.confirmAction.formId) {
                    const form = document.getElementById(this.confirmAction.formId);
                    if (form) {
                        if (form.dataset.ajax === 'true') {
                            this.submitDeleteAjax(form);
                        } else {
                            form.submit();
                        }
                    }
                }
                this.closeConfirm();
            },
            async submitDeleteAjax(form) {
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': formData.get('_token'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        successMessage = data.message || 'Book has been deleted successfully!';
                        showSuccessMessage = true;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.location.reload();
                }
            },
            openEditModal(bookData) {
                if (typeof bookData === 'number') {
                    // If only ID is passed, fetch the book data
                    const bookElement = document.querySelector(`[data-book-id='${bookData}']`);
                    if (bookElement) {
                        bookData = JSON.parse(bookElement.dataset.bookData);
                    }
                }
                if (bookData) {
                    this.editingBook = bookData;
                    this.editingBookId = bookData.id;
                    this.formErrors = {};
                    this.showEditModal = true;
                    if (window.lucide) setTimeout(() => lucide.createIcons(), 100);

                    // Update image preview after modal is shown
                    setTimeout(() => {
                        const imageSection = document.getElementById('edit-book-image-section');
                        if (imageSection && bookData.image_path) {
                            const imageData = Alpine.$data(imageSection);
                            if (imageData) {
                                imageData.preview = '{{ asset('storage/') }}/' + bookData.image_path;
                            }
                        }
                    }, 300);
                }
            },
        }"
        x-effect="if ((showAddModal || showEditModal || showConfirmModal || viewMode) && window.lucide) { lucide.createIcons(); }"
        class="space-y-6"
    >
        @if (session('status'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        {{-- Success Message --}}
        <div
            x-cloak
            x-show="showSuccessMessage"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 right-4 z-[1300] rounded-2xl border border-green-200 bg-green-50 px-6 py-4 shadow-lg"
        >
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-green-800" x-text="successMessage"></p>
                <button
                    @click="showSuccessMessage = false"
                    class="ml-4 flex-shrink-0 text-green-600 hover:text-green-800"
                >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 border-b border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4 rounded-[24px] bg-white">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="viewMode = 'gallery'"
                    :class="viewMode === 'gallery' ? 'bg-[#a03464] text-white' : 'bg-white text-[#a03464] border border-[#f3cbe0]'"
                    class="rounded-full px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold transition inline-flex items-center gap-2"
                >
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Gallery</span>
                </button>
                <button
                    type="button"
                    @click="viewMode = 'table'"
                    :class="viewMode === 'table' ? 'bg-[#a03464] text-white' : 'bg-white text-[#a03464] border border-[#f3cbe0]'"
                    class="rounded-full px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold transition inline-flex items-center gap-2"
                >
                    <i data-lucide="table" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Table</span>
                </button>
            </div>
            <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2 w-full md:w-auto flex-wrap">
                <form method="GET" action="{{ route('admin.manage-books') }}" class="flex flex-col md:flex-row items-stretch md:items-center gap-2 w-full md:w-auto">
                    <select
                        name="category"
                        onchange="this.form.submit()"
                        class="w-full md:w-auto rounded-full border border-[#f3cbe0] bg-[#fff7fb] px-4 py-2.5 sm:py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f] text-[#4b2036] md:min-w-[150px]"
                    >
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                    <div class="relative w-full md:w-auto">
                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search books"
                            class="custom-search w-full md:w-auto rounded-full border border-[#f3cbe0] bg-[#fff7fb] pl-4 pr-10 py-2.5 sm:py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                        />
                        <button
                            type="submit"
                            class="absolute inset-y-0 right-3 flex items-center text-[#a03464]/60 hover:text-[#a03464]"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
                            </svg>
                        </button>
                    </div>
                </form>
                <div class="flex gap-2 flex-col md:flex-row">
                    <a
                        href="{{ route('admin.manage-books.export', ['format' => 'pdf', 'search' => request('search'), 'category' => request('category')]) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-red-600 px-4 py-2.5 sm:py-2 text-sm font-semibold text-white shadow-md hover:bg-red-700 w-full md:w-auto"
                        title="Export to PDF"
                    >
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>PDF</span>
                    </a>
                    <a
                        href="{{ route('admin.manage-books.export', ['format' => 'excel', 'search' => request('search'), 'category' => request('category')]) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-green-600 px-4 py-2.5 sm:py-2 text-sm font-semibold text-white shadow-md hover:bg-green-700 w-full md:w-auto"
                        title="Export to Excel"
                    >
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Excel</span>
                    </a>
                </div>
                <button
                    type="button"
                    @click="formErrors = {}; showAddModal = true"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-[#a03464] px-4 py-2.5 sm:py-2 text-sm font-semibold text-white shadow-md hover:bg-[#821a4f] w-full md:w-auto"
                >
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Book</span>
                </button>
            </div>
        </div>

        {{-- Gallery View --}}
        <div x-show="viewMode === 'gallery'" class="grid gap-4 sm:gap-6 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 min-h-[570px] px-3 sm:px-0">
            @forelse ($books as $book)
                <article class="group relative flex flex-col gap-2 items-center text-center" data-book-id="{{ $book->id }}" data-book-data="{{ json_encode($book) }}">
                    <div class="relative rounded-[10px] bg-[#fde7f0] border border-[#f3cbe0] overflow-hidden w-[150px] sm:w-[130px] lg:w-[180px] mx-auto group-hover:scale-105 transition-transform duration-200 ease-out cursor-pointer" style="aspect-ratio: 2 / 3;" @click="openEditModal(JSON.parse($el.closest('[data-book-data]').dataset.bookData))">
                        @if ($book->image_path)
                            <img src="{{ asset('storage/' . $book->image_path) }}" alt="{{ $book->book_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i data-lucide="image" class="w-8 h-8 text-[#a03464]/60"></i>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-200 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                            <button
                                type="button"
                                @click.stop="openEditModal(JSON.parse($el.closest('[data-book-data]').dataset.bookData))"
                                class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-white/90 text-[#a03464] hover:bg-white shadow-md"
                                title="Edit"
                            >
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </button>
                            <form
                                id="delete-form-gallery-{{ $book->id }}"
                                method="POST"
                                action="{{ route('admin.manage-books.destroy', $book) }}"
                                data-ajax="true"
                                @click.stop
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="button"
                                    @click="openConfirm('delete-form-gallery-{{ $book->id }}', 'Delete this book?')"
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-white/90 text-rose-600 hover:bg-white shadow-md"
                                    title="Delete"
                                >
                                    <i data-lucide="trash" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute -top-2 left-1/2 -translate-x-1/2 -translate-y-full opacity-0 group-hover:opacity-100 transition-opacity duration-150 ease-out z-10">
                        <div class="relative rounded-md bg-white text-[#4b2036] text-xs shadow-lg border border-[#f3cbe0] px-3 py-2 min-w-[190px] text-left">
                            <p class="leading-tight mb-1"><span class="font-semibold">ISBN:</span> {{ $book->isbn }}</p>
                            <p class="leading-tight mb-1"><span class="font-semibold"> Author:</span> {{ $book->authors_name ?? 'Unknown' }}</p>
                            <p class="leading-tight mb-1"><span class="font-semibold"> Category:</span> {{ $book->category ?? '—' }}</p>
                            @if (!empty($book->book_shelf))
                                <p class="leading-tight mb-1"><span class="font-semibold"> Shelf:</span> {{ $book->book_shelf }}</p>
                            @endif
                            @if (isset($book->stock_quantity))
                                <p class="leading-tight"><span class="font-semibold"> Stock:</span> {{ $book->stock_quantity }}</p>
                            @endif
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-0 translate-y-full">
                                <div class="w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-white"></div>
                                <div class="absolute left-1/2 -translate-x-1/2 top-0 w-0 h-0 border-l-[7px] border-l-transparent border-r-[7px] border-r-transparent border-t-[7px] border-t-[#f3cbe0] -z-10"></div>
                            </div>
                        </div>
                    </div>
                    <h3 class="text-sm font-semibold text-[#4b2036] line-clamp-2">{{ $book->book_name }}</h3>
                </article>
            @empty
                <div class="col-span-full text-center text-sm text-[#7c4c63] py-6">No books found.</div>
            @endforelse
        </div>

        {{-- Table View --}}
        <div x-show="viewMode === 'table'" class="rounded-[10px] border border-[#f3cbe0] bg-white overflow-hidden">
            {{-- Mobile & Tablet Card Layout --}}
            <div class="lg:hidden min-h-[570px] space-y-3 px-3 py-4">
                @forelse ($books as $book)
                    <div class="rounded-xl border border-[#f3cbe0] bg-white p-4 space-y-3" data-book-id="{{ $book->id }}" data-book-data="{{ json_encode($book) }}">
                        <div class="flex items-start gap-3">
                            @if($book->image_path)
                                <img src="{{ asset('storage/' . $book->image_path) }}" alt="{{ $book->book_name }}" class="w-16 h-20 object-cover rounded flex-shrink-0">
                            @else
                                <div class="w-16 h-20 bg-[#f3cbe0] rounded flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="book" class="w-6 h-6 text-[#a03464]"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-sm text-[#4b2036] truncate">{{ $book->book_name }}</h3>
                                <p class="text-xs text-[#7c4c63] mt-0.5">ISBN: {{ $book->isbn }}</p>
                                <p class="text-xs text-[#7c4c63] mt-1">Author: {{ $book->authors_name ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-xs pt-2 border-t border-[#f7d6e6]">
                            <div class="flex items-center justify-between">
                                <span class="text-[#7c4c63] font-medium">Category:</span>
                                <span class="text-[#4b2036]">{{ $book->category ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#7c4c63] font-medium">Shelf:</span>
                                <span class="text-[#4b2036]">{{ $book->book_shelf ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#7c4c63] font-medium">Stock:</span>
                                <span class="text-[#4b2036] font-semibold">{{ $book->stock_quantity }}</span>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-[#f7d6e6]">
                            <button
                                type="button"
                                @click="openEditModal(JSON.parse($el.closest('[data-book-data]').dataset.bookData))"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#f3cbe0] text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                title="Edit"
                            >
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                                <span class="sr-only">Edit</span>
                            </button>
                            <form
                                id="delete-form-table-mobile-{{ $book->id }}"
                                method="POST"
                                action="{{ route('admin.manage-books.destroy', $book) }}"
                                data-ajax="true"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="button"
                                    @click="openConfirm('delete-form-table-mobile-{{ $book->id }}', 'Delete this book?')"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50 active:scale-95 transition-transform"
                                    title="Delete"
                                >
                                    <i data-lucide="trash" class="h-4 w-4"></i>
                                    <span class="sr-only">Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-3 py-6 text-center text-sm text-[#7c4c63]">No books found.</div>
                @endforelse
            </div>

            {{-- Desktop Table Layout --}}
            <div class="hidden lg:block overflow-x-auto min-h-[570px]">
                <table class="min-w-full text-left text-sm text-[#4b2036]">
                    <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">ISBN</th>
                            <th class="px-4 py-3 whitespace-nowrap">Book Name</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Category</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">Author</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">Shelf</th>
                            <th class="px-4 py-3 whitespace-nowrap">Stock</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">Publication</th>
                            <th class="px-4 py-3 whitespace-nowrap hidden xl:table-cell">Copyright</th>
                            <th class="px-4 py-3 whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f7d6e6]">
                        @forelse ($books as $book)
                            <tr @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])" data-book-id="{{ $book->id }}" data-book-data="{{ json_encode($book) }}">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $book->isbn }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">{{ $book->book_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm hidden lg:table-cell">{{ $book->category ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm hidden lg:table-cell">{{ $book->authors_name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm hidden xl:table-cell">{{ $book->book_shelf ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold">{{ $book->stock_quantity }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm hidden xl:table-cell">{{ $book->publication_name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm hidden xl:table-cell">{{ $book->copyright ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="openEditModal(JSON.parse($el.closest('[data-book-data]').dataset.bookData))"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#f3cbe0] text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                            title="Edit"
                                        >
                                            <i data-lucide="pencil" class="h-4 w-4"></i>
                                            <span class="sr-only">Edit</span>
                                        </button>
                                        <form
                                            id="delete-form-table-{{ $book->id }}"
                                            method="POST"
                                            action="{{ route('admin.manage-books.destroy', $book) }}"
                                            data-ajax="true"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                @click="openConfirm('delete-form-table-{{ $book->id }}', 'Delete this book?')"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50 active:scale-95 transition-transform"
                                                title="Delete"
                                            >
                                                <i data-lucide="trash" class="h-4 w-4"></i>
                                                <span class="sr-only">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-sm text-[#7c4c63]">No books found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-[#f3cbe0] px-3 sm:px-6 py-3 sm:py-4 bg-white rounded-[18px]">
            @php
                $prevUrl = $books->previousPageUrl();
                $nextUrl = $books->nextPageUrl();
            @endphp
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-[#7c4c63]">
                <p class="leading-tight text-center sm:text-left">
                    @if ($books->total())
                        Showing {{ $books->firstItem() }} to {{ $books->lastItem() }} of {{ $books->total() }} books
                    @else
                        Showing 0 to 0 of 0 books
                    @endif
                </p>
                <div class="flex items-center justify-center gap-2">
                    <a
                        href="{{ $prevUrl ?: '#' }}"
                        class="rounded-full border border-[#f3cbe0] px-4 py-2 text-xs font-semibold text-[#a03464] {{ $prevUrl ? 'hover:bg-[#fff2f8] active:scale-95' : 'opacity-60 cursor-not-allowed' }} transition-transform"
                        @if(!$prevUrl) aria-disabled="true" @endif
                    >
                        Previous
                    </a>
                    <span class="text-[#a03464] font-semibold px-2">{{ $books->currentPage() }}</span>
                    <a
                        href="{{ $nextUrl ?: '#' }}"
                        class="rounded-full border border-[#f3cbe0] px-4 py-2 text-xs font-semibold text-[#a03464] {{ $nextUrl ? 'hover:bg-[#fff2f8] active:scale-95' : 'opacity-60 cursor-not-allowed' }} transition-transform"
                        @if(!$nextUrl) aria-disabled="true" @endif
                    >
                        Next
                    </a>
                </div>
            </div>
        </div>

        {{-- Add book modal --}}
        <template x-teleport="body">
            <div
                x-cloak
                x-show="showAddModal"
                class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/40 px-2 sm:px-4 py-4 sm:py-8"
            >
                <div
                    x-on:click.away="showAddModal = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-8"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-8"
                    class="w-full max-w-4xl max-h-[95vh] rounded-[10px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0] flex flex-col"
                >
                    <div class="flex items-center justify-between px-3 sm:px-6 py-3 sm:py-4 border-b border-[#f3cbe0] flex-shrink-0">
                        <div>
                            <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#4b2036] leading-tight">Add Book</h2>
                            <p class="text-xs sm:text-sm text-[#7c4c63] hidden sm:block">Fill in the details below to add a new book.</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                            @click="showAddModal = false"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                    <div class="px-3 sm:px-6 pb-3 sm:pb-6 overflow-y-auto flex-1">
                        <form method="POST" action="{{ route('admin.manage-books.store') }}" class="space-y-4" enctype="multipart/form-data" @submit.prevent="
                            const form = $el;
                            isAddingBook = true;
                            const formData = new FormData(form);
                            const submitBtn = form.querySelector('button[type=\'submit\']');
                            const originalText = submitBtn.innerHTML;
                            fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': formData.get('_token'),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: formData,
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => Promise.reject(err));
                                }
                                return response.json();
                            })
                            .then(data => {
                                isAddingBook = false;
                                if (data.success) {
                                    successMessage = data.message || 'Book has been added successfully!';
                                    showSuccessMessage = true;
                                    showAddModal = false;
                                    formErrors = {};
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                } else {
                                    console.error(data.message || 'Failed to add book');
                                    submitBtn.innerHTML = originalText;
                                }
                            })
                            .catch(error => {
                                isAddingBook = false;
                                if (error.errors) {
                                    formErrors = error.errors;
                                } else {
                                    console.error('Error:', error);
                                    console.error('An error occurred while adding the book:', error.message);
                                }
                                submitBtn.innerHTML = originalText;
                            });
                        ">
                            @csrf
                            <div class="grid gap-4 sm:gap-5 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">ISBN <span class="text-rose-500">*</span></label>
                                    <input type="text" name="isbn" value="{{ old('isbn') }}" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.isbn" x-text="formErrors.isbn ? formErrors.isbn[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('isbn') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Book Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="book_name" value="{{ old('book_name') }}" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.book_name" x-text="formErrors.book_name ? formErrors.book_name[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('book_name') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Category</label>
                                    <select name="category" class="{{ $inputClass }}">
                                        <option value="">Select Category</option>
                                        <option value="Science" {{ old('category') == 'Science' ? 'selected' : '' }}>Science</option>
                                        <option value="Social Science" {{ old('category') == 'Social Science' ? 'selected' : '' }}>Social Science</option>
                                        <option value="English" {{ old('category') == 'English' ? 'selected' : '' }}>English</option>
                                        <option value="Mathematics" {{ old('category') == 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                                        <option value="Fiction" {{ old('category') == 'Fiction' ? 'selected' : '' }}>Fiction</option>
                                        <option value="Mapeh" {{ old('category') == 'Mapeh' ? 'selected' : '' }}>Mapeh</option>
                                        <option value="General Education" {{ old('category') == 'General Education' ? 'selected' : '' }}>General Education</option>
                                        <option value="PRVE" {{ old('category') == 'PRVE' ? 'selected' : '' }}>PRVE</option>
                                        <option value="TLE" {{ old('category') == 'TLE' ? 'selected' : '' }}>TLE</option>
                                        <option value="Filipino" {{ old('category') == 'Filipino' ? 'selected' : '' }}>Filipino</option>
                                    </select>
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.category" x-text="formErrors.category ? formErrors.category[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('category') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Author's Name</label>
                                    <input type="text" name="authors_name" value="{{ old('authors_name') }}" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.authors_name" x-text="formErrors.authors_name ? formErrors.authors_name[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('authors_name') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Book Shelf</label>
                                    <select name="book_shelf" class="{{ $inputClass }}">
                                        <option value="">Select Shelf</option>
                                        <option value="1" {{ old('book_shelf') == '1' ? 'selected' : '' }}>1</option>
                                        <option value="2" {{ old('book_shelf') == '2' ? 'selected' : '' }}>2</option>
                                        <option value="3" {{ old('book_shelf') == '3' ? 'selected' : '' }}>3</option>
                                        <option value="4" {{ old('book_shelf') == '4' ? 'selected' : '' }}>4</option>
                                        <option value="5" {{ old('book_shelf') == '5' ? 'selected' : '' }}>5</option>
                                    </select>
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.book_shelf" x-text="formErrors.book_shelf ? formErrors.book_shelf[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('book_shelf') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Copyright</label>
                                    <input type="text" name="copyright" value="{{ old('copyright') }}" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.copyright" x-text="formErrors.copyright ? formErrors.copyright[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('copyright') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Stock Quantity <span class="text-rose-500">*</span></label>
                                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.stock_quantity" x-text="formErrors.stock_quantity ? formErrors.stock_quantity[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('stock_quantity') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Publication Name</label>
                                    <input type="text" name="publication_name" value="{{ old('publication_name') }}" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.publication_name" x-text="formErrors.publication_name ? formErrors.publication_name[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('publication_name') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2 md:col-span-2" x-data="{ preview: null, dragging: false }" x-init="
                                    const input = $refs.fileInput;
                                    input.addEventListener('change', () => {
                                        const file = input.files[0];
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => preview = e.target.result;
                                            reader.readAsDataURL(file);
                                        } else {
                                            preview = null;
                                        }
                                    });
                                ">
                                    <label class="{{ $labelClass }}">Image</label>
                                    <input
                                        type="file"
                                        name="image"
                                        accept="image/*"
                                        x-ref="fileInput"
                                        class="hidden"
                                    >
                                    <div
                                        class="rounded-[12px] border-2 border-dashed"
                                        :class="dragging ? 'border-[#a03464] bg-[#fde7f0]' : 'border-[#f3cbe0] bg-[#fff7fb]'"
                                        @click="$refs.fileInput.click()"
                                        @dragover.prevent="dragging = true"
                                        @dragleave.prevent="dragging = false"
                                        @drop.prevent="
                                            dragging = false;
                                            const file = $event.dataTransfer.files[0];
                                            if (file) {
                                                $refs.fileInput.files = $event.dataTransfer.files;
                                                const reader = new FileReader();
                                                reader.onload = (e) => preview = e.target.result;
                                                reader.readAsDataURL(file);
                                            }
                                        "
                                    >
                                        <div class="p-4 flex flex-col items-center gap-4">
                                            <div class="w-36 h-48 rounded-lg bg-white border border-[#f3cbe0] flex items-center justify-center overflow-hidden">
                                                <template x-if="preview">
                                                    <img :src="preview" alt="Preview" class="h-full w-full object-cover">
                                                </template>
                                                <template x-if="!preview">
                                                    <i data-lucide="image" class="w-6 h-6 text-[#a03464]/70"></i>
                                                </template>
                                            </div>
                                            <div class="flex-1 text-center">
                                                <p class="text-sm font-semibold text-[#4b2036]">Click or drag an image here</p>
                                                <p class="text-xs text-[#7c4c63]">PNG, JPG up to 2MB</p>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.image" x-text="formErrors.image ? formErrors.image[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('image') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2 md:col-span-2">
                                    <label class="{{ $labelClass }}">Description</label>
                                    <textarea name="description" rows="3" class="{{ $inputClass }}">{{ old('description') }}</textarea>
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.description" x-text="formErrors.description ? formErrors.description[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('description') {{ $message }} @enderror</p>
                                </div>
                            </div>
                            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-[#f3cbe0] mt-4">
                                <button
                                    type="button"
                                    class="rounded-[12px] border border-[#f3cbe0] px-6 py-2.5 sm:py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                    @click="showAddModal = false"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-[#a03464] px-6 py-2.5 sm:py-2 text-sm font-semibold text-white hover:bg-[#821a4f] active:scale-95 transition-transform"
                                    :disabled="isAddingBook"
                                >
                                    <template x-if="isAddingBook">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="isAddingBook ? 'Saving...' : 'Save'" :class="isAddingBook ? '' : ''">Save</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- Edit book modal --}}
        <template x-teleport="body">
            <div
                x-cloak
                x-show="showEditModal && editingBook"
                class="fixed inset-0 z-[1200] flex items-center justify-center bg-black/40 px-2 sm:px-4 py-4 sm:py-8"
            >
                <div
                    x-on:click.away="showEditModal = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-8"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-8"
                    class="w-full max-w-4xl max-h-[95vh] rounded-[10px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0] flex flex-col"
                >
                    <div class="flex items-center justify-between px-3 sm:px-6 py-3 sm:py-4 border-b border-[#f3cbe0] flex-shrink-0">
                        <div>
                            <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#4b2036] leading-tight">Edit Book</h2>
                            <p class="text-xs sm:text-sm text-[#7c4c63] hidden sm:block">Update the details below.</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                            @click="showEditModal = false"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                    <div class="px-3 sm:px-6 pb-3 sm:pb-6 overflow-y-auto flex-1" x-show="editingBook">
                        <form
                            id="edit-book-form"
                            method="POST"
                            :action="editingBook ? '{{ url('/admin/manage-books') }}/' + editingBook.id : ''"
                            class="space-y-4"
                            enctype="multipart/form-data"
                            @submit.prevent="
                                const form = $el;
                                if (!editingBook || !editingBook.id) {
                                    console.error('Error: Book data not loaded');
                                    return;
                                }
                                isSavingBook = true;
                                const formData = new FormData(form);
                                const actionUrl = '{{ url('/admin/manage-books') }}/' + editingBook.id;
                                const submitBtn = form.querySelector('button[type=\'submit\']');
                                const originalText = submitBtn.innerHTML;
                                fetch(actionUrl, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': formData.get('_token'),
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    body: formData,
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        return response.json().then(err => Promise.reject(err));
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    isSavingBook = false;
                                    if (data.success) {
                                        successMessage = data.message || 'Book has been updated successfully!';
                                        showSuccessMessage = true;
                                        showEditModal = false;
                                        formErrors = {};
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 1500);
                                    } else {
                                        console.error(data.message || 'Update failed');
                                        submitBtn.innerHTML = originalText;
                                    }
                                })
                                .catch(error => {
                                    isSavingBook = false;
                                    if (error.errors) {
                                        formErrors = error.errors;
                                    } else {
                                        console.error('Error:', error);
                                        console.error('An error occurred while updating the book:', error.message);
                                    }
                                    submitBtn.innerHTML = originalText;
                                });
                            "
                        >
                            @csrf
                            @method('POST')
                            <div class="grid gap-4 sm:gap-5 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <label class="{{ $labelClass }}">ISBN <span class="text-rose-500">*</span></label>
                                    <input type="text" name="isbn" :value="editingBook ? editingBook.isbn : ''" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.isbn" x-text="formErrors.isbn ? formErrors.isbn[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('isbn') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Book Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="book_name" :value="editingBook ? editingBook.book_name : ''" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.book_name" x-text="formErrors.book_name ? formErrors.book_name[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('book_name') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Category</label>
                                    <select name="category" class="{{ $inputClass }}" x-model="editingBook ? editingBook.category : ''">
                                        <option value="">Select Category</option>
                                        <option value="Science">Science</option>
                                        <option value="Social Science">Social Science</option>
                                        <option value="English">English</option>
                                        <option value="Mathematics">Mathematics</option>
                                        <option value="Fiction">Fiction</option>
                                        <option value="Mapeh">Mapeh</option>
                                        <option value="General Education">General Education</option>
                                        <option value="PRVE">PRVE</option>
                                        <option value="TLE">TLE</option>
                                        <option value="Filipino">Filipino</option>
                                    </select>
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.category" x-text="formErrors.category ? formErrors.category[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('category') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Author's Name</label>
                                    <input type="text" name="authors_name" :value="editingBook ? (editingBook.authors_name || '') : ''" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.authors_name" x-text="formErrors.authors_name ? formErrors.authors_name[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('authors_name') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Book Shelf</label>
                                    <select name="book_shelf" class="{{ $inputClass }}" x-model="editingBook ? editingBook.book_shelf : ''">
                                        <option value="">Select Shelf</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.book_shelf" x-text="formErrors.book_shelf ? formErrors.book_shelf[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('book_shelf') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Copyright</label>
                                    <input type="text" name="copyright" :value="editingBook ? (editingBook.copyright || '') : ''" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.copyright" x-text="formErrors.copyright ? formErrors.copyright[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('copyright') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Stock Quantity <span class="text-rose-500">*</span></label>
                                    <input type="number" name="stock_quantity" :value="editingBook ? editingBook.stock_quantity : 0" min="0" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.stock_quantity" x-text="formErrors.stock_quantity ? formErrors.stock_quantity[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('stock_quantity') {{ $message }} @enderror</p>
                                </div>
                                <div class="space-y-1.5 px-2">
                                    <label class="{{ $labelClass }}">Publication Name</label>
                                    <input type="text" name="publication_name" :value="editingBook ? (editingBook.publication_name || '') : ''" class="{{ $inputClass }}">
                                    <p class="text-xs text-[#a03464]/70" x-show="formErrors.publication_name" x-text="formErrors.publication_name ? formErrors.publication_name[0] : ''"></p>
                                    <p class="text-xs text-[#a03464]/70">@error('publication_name') {{ $message }} @enderror</p>
                                </div>
                                <div id="edit-book-image-section" class="space-y-1.5 px-2 md:col-span-2" x-data="{
                                    preview: null,
                                    dragging: false
                                }" x-init="
                                    const input = $refs.editFileInput;
                                    const self = $data;
                                    const root = $root;

                                    // Function to update preview
                                    function updatePreview() {
                                        const book = root.editingBook;
                                        if (book && book.image_path) {
                                            self.preview = '{{ asset('storage/') }}/' + book.image_path;
                                        } else {
                                            self.preview = null;
                                        }
                                    }

                                    // Watch for editingBook changes
                                    $watch('$root.editingBook', (book) => {
                                        updatePreview();
                                    });

                                    // Watch for modal opening
                                    $watch('$root.showEditModal', (isOpen) => {
                                        if (isOpen) {
                                            setTimeout(() => updatePreview(), 100);
                                        }
                                    });

                                    // Initial update
                                    updatePreview();

                                    input.addEventListener('change', () => {
                                        const file = input.files[0];
                                        if (file) {
                                            const reader = new FileReader();
                                            reader.onload = (e) => self.preview = e.target.result;
                                            reader.readAsDataURL(file);
                                        } else {
                                            // Reset to original image if no file selected
                                            updatePreview();
                                        }
                                    });
                                " x-effect="
                                    if ($root.showEditModal && $root.editingBook && $root.editingBook.image_path) {
                                        if (!preview || preview.indexOf($root.editingBook.image_path) === -1) {
                                            preview = '{{ asset('storage/') }}/' + $root.editingBook.image_path;
                                        }
                                    }
                                ">
                                    <label class="{{ $labelClass }}">Image</label>
                                    <input
                                        type="file"
                                        name="image"
                                        accept="image/*"
                                        x-ref="editFileInput"
                                        class="hidden"
                                    >
                                    <div
                                        class="rounded-[12px] border-2 border-dashed"
                                        :class="dragging ? 'border-[#a03464] bg-[#fde7f0]' : 'border-[#f3cbe0] bg-[#fff7fb]'"
                                        @click="$refs.editFileInput.click()"
                                        @dragover.prevent="dragging = true"
                                        @dragleave.prevent="dragging = false"
                                        @drop.prevent="
                                            dragging = false;
                                            const file = $event.dataTransfer.files[0];
                                            if (file) {
                                                $refs.editFileInput.files = $event.dataTransfer.files;
                                                const reader = new FileReader();
                                                reader.onload = (e) => preview = e.target.result;
                                                reader.readAsDataURL(file);
                                            }
                                        "
                                    >
                                        <div class="p-4 flex flex-col items-center gap-4">
                                            <div class="w-36 h-48 rounded-lg bg-white border border-[#f3cbe0] flex items-center justify-center overflow-hidden">
                                                <img x-show="preview" :src="preview" alt="Preview" class="h-full w-full object-cover">
                                                <i x-show="!preview" data-lucide="image" class="w-6 h-6 text-[#a03464]/70"></i>
                                            </div>
                                            <div class="flex-1 text-center">
                                                <p class="text-sm font-semibold text-[#4b2036]">Click or drag an image here</p>
                                                <p class="text-xs text-[#7c4c63]">PNG, JPG up to 2MB</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-1.5 px-2 md:col-span-2">
                                    <label class="{{ $labelClass }}">Description</label>
                                    <textarea name="description" rows="3" class="{{ $inputClass }}" x-bind:value="editingBook ? (editingBook.description || '') : ''"></textarea>
                                </div>
                            </div>
                            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-[#f3cbe0] mt-4">
                                <button
                                    type="button"
                                    @click="showEditModal = false"
                                    class="rounded-[12px] border border-[#f3cbe0] px-6 py-2.5 sm:py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-[#a03464] px-6 py-2.5 sm:py-2 text-sm font-semibold text-white hover:bg-[#821a4f] active:scale-95 transition-transform"
                                    :disabled="isSavingBook"
                                >
                                    <template x-if="isSavingBook">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="isSavingBook ? 'Updating...' : 'Update'">Update</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>

        {{-- Confirm action modal --}}
        <template x-teleport="body">
            <div
                x-cloak
                x-show="showConfirmModal"
                class="fixed inset-0 z-[1300] flex items-center justify-center bg-black/40 px-3 sm:px-4 py-4 sm:py-8"
            >
                <div
                    x-on:click.away="closeConfirm"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    class="w-full max-w-md rounded-[10px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0]"
                >
                    <div class="flex items-center justify-end px-4 sm:px-4 pt-3 sm:pt-4">
                        <button
                            type="button"
                            class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                            @click="closeConfirm"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                    <div class="py-3 sm:py-4 px-4 sm:px-5 text-center text-[#4b2036]">
                        <h3 class="text-base sm:text-lg font-semibold mb-2">Please Confirm</h3>
                        <p class="text-xs sm:text-sm" x-text="confirmAction.message"></p>
                    </div>
                    <div class="px-4 sm:px-5 pb-3 sm:pb-4 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button
                            type="button"
                            class="rounded-[10px] border border-[#f3cbe0] px-4 py-2.5 sm:py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] active:scale-95 transition-transform"
                            @click="closeConfirm"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="rounded-[10px] bg-[#a03464] px-4 py-2.5 sm:py-2 text-sm font-semibold text-white hover:bg-[#821a4f] active:scale-95 transition-transform"
                            @click="submitConfirm"
                        >
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection

