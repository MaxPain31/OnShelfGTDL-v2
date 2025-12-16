@extends('layout.teacher.app')

@section('title', 'Books | OnShelf GTDL')
@section('page_title', 'Books')

@section('content')
    <div
        x-data='{
            selectedCategory: @json($selectedCategory ?? ''),
            searchQuery: @json($searchQuery ?? ''),
            showBookModal: false,
            showBorrowModal: false,
            showReserveModal: false,
            selectedBook: null,
            isLoadingBook: false,
            isBorrowing: false,
            isReserving: false,
            borrowMessage: "",
            borrowMessageType: "success",
            reserveMessage: "",
            reserveMessageType: "success",
            reserveDate: "",
            dueDate: "",
            calculateDueDate() {
                if (!this.reserveDate) {
                    this.dueDate = "";
                    return;
                }

                const reserveDateObj = new Date(this.reserveDate);
                const dueDate = new Date(reserveDateObj);
                let daysAdded = 0;
                let businessDays = 0;

                // Add 3 business days (excluding weekends)
                while (businessDays < 3) {
                    daysAdded++;
                    dueDate.setDate(reserveDateObj.getDate() + daysAdded);
                    const dayOfWeek = dueDate.getDay();
                    // Skip Saturday (6) and Sunday (0)
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        businessDays++;
                    }
                }

                this.dueDate = dueDate.toISOString().split("T")[0];
            },
            getBorrowDate() {
                const today = new Date();
                return today.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
            },
            getDueDate() {
                const today = new Date();
                const dueDate = new Date(today);
                let daysAdded = 0;
                let businessDays = 0;

                // Add 3 business days (excluding weekends)
                while (businessDays < 3) {
                    daysAdded++;
                    dueDate.setDate(today.getDate() + daysAdded);
                    const dayOfWeek = dueDate.getDay();
                    // Skip Saturday (6) and Sunday (0)
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        businessDays++;
                    }
                }

                return dueDate.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
            },
            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const bookId = urlParams.get("book");
                if (bookId) {
                    this.openBookModal(bookId);
                    window.history.replaceState({}, document.title, window.location.pathname);
                }

            },
            filterBooks() {
                const form = document.getElementById("filter-form");
                if (form) {
                    form.submit();
                }
            },
            async openBookModal(bookId) {
                this.isLoadingBook = true;
                this.showBookModal = true;
                try {
                    const response = await fetch("{{ route('teacher.books.show', ':id') }}".replace(":id", bookId), {
                        method: "GET",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.selectedBook = data.book;
                        // Initialize icon state after book data is loaded
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
                    console.error("Error fetching book details:", error);
                } finally {
                    this.isLoadingBook = false;
                }
            },
            closeBookModal() {
                this.showBookModal = false;
                this.selectedBook = null;
            },
            borrowBook() {
                if (!this.selectedBook) return;

                if (this.selectedBook.stock_quantity === 0) {
                    this.borrowMessage = "This book is not available for borrowing.";
                    this.borrowMessageType = "error";
                    this.showBorrowModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    });
                    return;
                }

                this.borrowMessage = "";
                this.showBorrowModal = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },
            closeBorrowModal() {
                this.showBorrowModal = false;
                this.borrowMessage = "";
            },
            async confirmBorrow() {
                if (!this.selectedBook) return;

                this.isBorrowing = true;
                this.borrowMessage = "";

                try {
                    const csrfToken = document.querySelector("meta[name=csrf-token]")?.getAttribute("content");
                    const response = await fetch("{{ route('teacher.books.borrow', ':id') }}".replace(":id", this.selectedBook.id), {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.borrowMessage = data.message || "Book borrowed successfully!";
                        this.borrowMessageType = "success";
                        setTimeout(() => {
                            this.closeBorrowModal();
                            this.closeBookModal();
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.borrowMessage = data.message || "Failed to borrow book. Please try again.";
                        this.borrowMessageType = "error";
                    }
                } catch (error) {
                    console.error("Error borrowing book:", error);
                    this.borrowMessage = "An error occurred while borrowing the book. Please try again.";
                    this.borrowMessageType = "error";
                } finally {
                    this.isBorrowing = false;
                }
            },
            reserveBook() {
                if (!this.selectedBook) return;


                if (this.selectedBook.stock_quantity === 0) {
                    this.reserveMessage = "This book is not available for reservation.";
                    this.reserveMessageType = "error";
                    this.showReserveModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            lucide.createIcons();
                        }
                    });
                    return;
                }

                // Clear dates so user can choose their own
                this.reserveDate = "";
                this.dueDate = "";
                this.reserveMessage = "";
                this.showReserveModal = true;
                this.$nextTick(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                });
            },
            closeReserveModal() {
                this.showReserveModal = false;
                this.reserveMessage = "";
                this.reserveDate = "";
                this.dueDate = "";
            },
            async confirmReserve() {
                if (!this.selectedBook) return;

                // Validate reserve date
                if (!this.reserveDate) {
                    this.reserveMessage = "Please select a reserve date.";
                    this.reserveMessageType = "error";
                    return;
                }

                // Calculate due date
                this.calculateDueDate();

                if (!this.dueDate) {
                    this.reserveMessage = "Failed to calculate due date. Please try again.";
                    this.reserveMessageType = "error";
                    return;
                }

                const reserveDateObj = new Date(this.reserveDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (reserveDateObj < today) {
                    this.reserveMessage = "Reserve date must be today or a future date.";
                    this.reserveMessageType = "error";
                    return;
                }

                this.isReserving = true;
                this.reserveMessage = "";

                try {
                    const csrfToken = document.querySelector("meta[name=csrf-token]")?.getAttribute("content");
                    const response = await fetch("{{ route('teacher.books.reserve', ':id') }}".replace(":id", this.selectedBook.id), {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": csrfToken,
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            reserve_date: this.reserveDate,
                            due_date: this.dueDate,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.reserveMessage = data.message || "Book reserved successfully!";
                        this.reserveMessageType = "success";
                        setTimeout(() => {
                            this.closeReserveModal();
                            this.closeBookModal();
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.reserveMessage = data.message || "Failed to reserve book. Please try again.";
                        this.reserveMessageType = "error";
                    }
                } catch (error) {
                    console.error("Error reserving book:", error);
                    this.reserveMessage = "An error occurred while reserving the book. Please try again.";
                    this.reserveMessageType = "error";
                } finally {
                    this.isReserving = false;
                }
            },
            async favoriteBook() {
                if (!this.selectedBook) return;

                const previousState = this.selectedBook.is_favorited;
                this.selectedBook.is_favorited = !this.selectedBook.is_favorited;

                // Update icon immediately
                this.updateFavoriteIcon();

                try {
                    const csrfToken = document.querySelector("meta[name=csrf-token]")?.getAttribute("content");
                    const response = await fetch("{{ route('teacher.books.favorite', ':id') }}".replace(":id", this.selectedBook.id), {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                    });
                    const data = await response.json();

                    if (data.success) {
                        this.selectedBook.is_favorited = data.is_favorited;
                        this.selectedBook.favorite_count = data.favorite_count;
                        // Update icon to match server response
                        this.$nextTick(() => this.updateFavoriteIcon());
                    } else {
                        this.selectedBook.is_favorited = previousState;
                        this.$nextTick(() => this.updateFavoriteIcon());
                    }
                } catch (error) {
                    console.error("Error toggling favorite:", error);
                    this.selectedBook.is_favorited = previousState;
                    this.$nextTick(() => this.updateFavoriteIcon());
                }
            },
            updateFavoriteIcon() {
                if (!this.selectedBook) return;

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

                if (this.selectedBook.is_favorited) {
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
                            if (this.selectedBook.is_favorited) {
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
            }
        }'
        class="space-y-6"
        @keydown.escape.window="closeBookModal()"
    >
        {{-- Search and Filter Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form id="filter-form" method="GET" action="{{ route('teacher.books') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-1 sm:max-w-2xl">
                {{-- Search Input --}}
                <div class="flex-1 relative">
                    <input
                        type="text"
                        name="search"
                        x-model="searchQuery"
                        placeholder="Search books by title, author, ISBN, or category..."
                        value="{{ request('search') }}"
                        class="w-full rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2.5 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                        @keyup.enter="filterBooks()"
                    />
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#a03464]/60"></i>
                </div>

                {{-- Category Filter --}}
                <div class="relative sm:w-48">
                    <select
                        name="category"
                        x-model="selectedCategory"
                        @change="filterBooks()"
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
                        href="{{ route('teacher.books') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2.5 text-sm font-medium text-[#7c4c63] hover:bg-[#fff7fb] transition"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                        <span>Clear</span>
                    </a>
                @endif
            </form>
        </div>

        {{-- Books by Category --}}
        @if ($booksByCategory->count() > 0)
            <div class="space-y-8">
                @foreach ($booksByCategory as $category => $books)
                    <div class="space-y-4">
                        {{-- Category Header --}}
                        <div class="flex items-center gap-3 pb-2 border-b-2 border-[#f3cbe0]">
                            <h2 class="text-xl font-bold text-[#4b2036]">{{ $category ?? 'Uncategorized' }}</h2>
                            <span class="text-sm text-[#7c4c63] bg-[#fde7f0] px-3 py-1 rounded-full">
                                {{ $books->count() }} {{ Str::plural('book', $books->count()) }}
                            </span>
                        </div>

                        {{-- Gallery View --}}
                        <div class="grid gap-6 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                            @foreach ($books as $book)
                                <article class="group relative flex flex-col gap-2 items-center text-center">
                                    <div
                                        class="relative rounded-[10px] bg-[#fde7f0] border border-[#f3cbe0] overflow-hidden w-full max-w-[180px] mx-auto group-hover:scale-105 transition-transform duration-200 ease-out cursor-pointer"
                                        style="aspect-ratio: 2 / 3;"
                                        @click="openBookModal({{ $book->id }})"
                                    >
                                        @if ($book->image_path)
                                            <img
                                                src="{{ asset('storage/' . $book->image_path) }}"
                                                alt="{{ $book->book_name }}"
                                                class="w-full h-full object-cover"
                                            >
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i data-lucide="book-open" class="w-12 h-12 text-[#a03464]/60"></i>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-200"></div>
                                    </div>

                                    {{-- Tooltip on Hover --}}
                                    <div class="pointer-events-none absolute -top-2 left-1/2 -translate-x-1/2 -translate-y-full opacity-0 group-hover:opacity-100 transition-opacity duration-150 ease-out z-10">
                                        <div class="relative rounded-md bg-white text-[#4b2036] text-xs shadow-lg border border-[#f3cbe0] px-3 py-2 min-w-[200px] text-left">
                                            <p class="leading-tight mb-1"><span class="font-semibold">ISBN:</span> {{ $book->isbn }}</p>
                                            <p class="leading-tight mb-1"><span class="font-semibold">Author:</span> {{ $book->authors_name ?? 'Unknown' }}</p>
                                            @if (!empty($book->book_shelf))
                                                <p class="leading-tight mb-1"><span class="font-semibold">Shelf:</span> {{ $book->book_shelf }}</p>
                                            @endif
                                            @if (isset($book->stock_quantity))
                                                <p class="leading-tight mb-1"><span class="font-semibold">Stock:</span> {{ $book->stock_quantity }}</p>
                                            @endif
                                            @if (!empty($book->publication_name))
                                                <p class="leading-tight mb-1"><span class="font-semibold">Publisher:</span> {{ $book->publication_name }}</p>
                                            @endif
                                            @if (!empty($book->copyright))
                                                <p class="leading-tight"><span class="font-semibold">Copyright:</span> {{ $book->copyright }}</p>
                                            @endif
                                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-0 translate-y-full">
                                                <div class="w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-white"></div>
                                                <div class="absolute left-1/2 -translate-x-1/2 top-0 w-0 h-0 border-l-[7px] border-l-transparent border-r-[7px] border-r-transparent border-t-[7px] border-t-[#f3cbe0] -z-10"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full max-w-[180px]">
                                        <h3
                                            class="text-sm font-semibold text-[#4b2036] line-clamp-2 mb-1 cursor-pointer hover:text-[#a03464] transition"
                                            @click="openBookModal({{ $book->id }})"
                                        >{{ $book->book_name }}</h3>
                                        <p class="text-xs text-[#7c4c63] line-clamp-1">{{ $book->authors_name ?? 'Unknown Author' }}</p>
                                        @if (isset($book->stock_quantity))
                                            <div class="mt-2 flex items-center justify-center gap-1">
                                                @if ($book->stock_quantity > 0)
                                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                                        Available ({{ $book->stock_quantity }})
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs text-rose-600 bg-rose-50 px-2 py-1 rounded-full">
                                                        <i data-lucide="x-circle" class="w-3 h-3"></i>
                                                        Out of Stock
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
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
                <i data-lucide="book-open" class="w-16 h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                <h2 class="text-xl font-semibold text-[#4b2036] mb-2">No books found</h2>
                <p class="text-sm text-[#7c4c63] mb-4">
                    @if (request('search') || request('category'))
                        Try adjusting your search or filter criteria.
                    @else
                        There are no books available at the moment.
                    @endif
                </p>
                @if (request('search') || request('category'))
                    <a
                        href="{{ route('teacher.books') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                    >
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>View All Books</span>
                    </a>
                @endif
            </div>
        @endif

        {{-- Book Details Modal --}}
        <div
            x-show="showBookModal"
            x-cloak
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="closeBookModal()"
        >
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

            {{-- Modal --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    x-show="showBookModal"
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
                        @click="closeBookModal()"
                        class="absolute top-4 right-4 z-10 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 text-[#7c4c63] hover:bg-white hover:text-[#a03464] shadow-lg transition"
                    >
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>

                    {{-- Loading State --}}
                    <div x-show="isLoadingBook" class="flex items-center justify-center min-h-[400px]">
                        <div class="text-center">
                            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#a03464] mb-4"></div>
                            <p class="text-sm text-[#7c4c63]">Loading book details...</p>
                        </div>
                    </div>

                    {{-- Book Details Content --}}
                    <div x-show="!isLoadingBook && selectedBook">
                        <div class="grid md:grid-cols-2 gap-0">
                            {{-- Left Side: Book Image --}}
                            <div class="bg-gradient-to-br from-[#fde7f0] to-[#fff7fb] p-8 flex items-center justify-center">
                                <div class="w-full max-w-sm">
                                    <div class="relative rounded-[16px] bg-white shadow-xl overflow-hidden" style="aspect-ratio: 2 / 3;">
                                        <template x-if="selectedBook && selectedBook.image_path">
                                            <img
                                                :src="selectedBook.image_path"
                                                :alt="selectedBook.book_name"
                                                class="w-full h-full object-cover"
                                            >
                                        </template>
                                        <template x-if="selectedBook && !selectedBook.image_path">
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i data-lucide="book-open" class="w-24 h-24 text-[#a03464]/60"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Right Side: Book Information --}}
                            <div class="p-8 overflow-y-auto max-h-[90vh]">
                                <div class="space-y-6">
                                    {{-- Title and Category --}}
                                    <div>
                                        <h2 class="text-3xl font-bold text-[#4b2036] mb-2" x-text="selectedBook ? selectedBook.book_name : ''"></h2>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="inline-flex items-center gap-1 text-sm text-[#a03464] bg-[#fde7f0] px-3 py-1 rounded-full"
                                                x-text="selectedBook ? selectedBook.category || 'Uncategorized' : ''"
                                            ></span>
                                            <span
                                                class="inline-flex items-center gap-1 text-xs text-[#7c4c63] bg-[#f3cbe0] px-2 py-1 rounded-full"
                                                x-show="selectedBook && selectedBook.view_count !== undefined"
                                            >
                                                <i data-lucide="eye" class="w-3 h-3"></i>
                                                <span x-text="selectedBook ? selectedBook.view_count : 0"></span> views
                                            </span>
                                            <span
                                                class="inline-flex items-center gap-1 text-xs text-[#7c4c63] bg-[#f3cbe0] px-2 py-1 rounded-full"
                                                x-show="selectedBook && selectedBook.favorite_count !== undefined"
                                            >
                                                <i data-lucide="heart" class="w-3 h-3"></i>
                                                <span x-text="selectedBook ? selectedBook.favorite_count : 0"></span> favorites
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Author --}}
                                    <div>
                                        <p class="text-sm font-semibold text-[#7c4c63] mb-1">Author</p>
                                        <p class="text-lg text-[#4b2036]" x-text="selectedBook ? (selectedBook.authors_name || 'Unknown') : ''"></p>
                                    </div>

                                    {{-- Book Details Grid --}}
                                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-[#f3cbe0]">
                                        <div>
                                            <p class="text-sm font-semibold text-[#7c4c63] mb-1">ISBN</p>
                                            <p class="text-sm text-[#4b2036]" x-text="selectedBook ? selectedBook.isbn : ''"></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[#7c4c63] mb-1">Shelf</p>
                                            <p class="text-sm text-[#4b2036]" x-text="selectedBook ? (selectedBook.book_shelf || '—') : ''"></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[#7c4c63] mb-1">Publisher</p>
                                            <p class="text-sm text-[#4b2036]" x-text="selectedBook ? (selectedBook.publication_name || '—') : ''"></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-[#7c4c63] mb-1">Copyright</p>
                                            <p class="text-sm text-[#4b2036]" x-text="selectedBook ? (selectedBook.copyright || '—') : ''"></p>
                                        </div>
                                    </div>

                                    {{-- Stock Status --}}
                                    <div class="pt-4 border-t border-[#f3cbe0]">
                                        <template x-if="selectedBook && selectedBook.stock_quantity > 0">
                                            <div class="inline-flex items-center gap-2 text-green-700 bg-green-50 px-4 py-2 rounded-lg">
                                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                                <span class="font-semibold">Available</span>
                                                <span class="text-sm">(<span x-text="selectedBook.stock_quantity"></span> in stock)</span>
                                            </div>
                                        </template>
                                        <template x-if="selectedBook && selectedBook.stock_quantity === 0">
                                            <div class="inline-flex items-center gap-2 text-rose-700 bg-rose-50 px-4 py-2 rounded-lg">
                                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                                                <span class="font-semibold">Out of Stock</span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Description --}}
                                    <div x-show="selectedBook && selectedBook.description" class="pt-4 border-t border-[#f3cbe0]">
                                        <p class="text-sm font-semibold text-[#7c4c63] mb-2">Description</p>
                                        <p class="text-sm text-[#4b2036] leading-relaxed whitespace-pre-wrap" x-text="selectedBook ? selectedBook.description : ''"></p>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="pt-6 border-t border-[#f3cbe0] space-y-3">
                                        <button
                                            @click="borrowBook()"
                                            :disabled="selectedBook && selectedBook.stock_quantity === 0"
                                            class="w-full flex items-center justify-center gap-2 rounded-[12px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-3 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i data-lucide="book-check" class="w-5 h-5"></i>
                                            <span>Borrow Now</span>
                                        </button>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button
                                                @click="reserveBook()"
                                                :disabled="selectedBook && selectedBook.stock_quantity === 0"
                                                class="flex items-center justify-center gap-2 rounded-[12px] border-2 border-[#a03464] bg-white px-4 py-3 text-sm font-semibold text-[#a03464] hover:bg-[#fff7fb] transition disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <i data-lucide="bookmark" class="w-4 h-4"></i>
                                                <span>Reserve Now</span>
                                            </button>
                                            <button
                                                @click="favoriteBook()"
                                                class="flex items-center justify-center gap-2 rounded-[12px] border-2 border-[#f3cbe0] bg-white px-4 py-3 text-sm font-semibold text-[#7c4c63] hover:bg-[#fff7fb] transition"
                                                x-show="selectedBook"
                                            >
                                                <span x-text="selectedBook ? (selectedBook.is_favorited ? 'Favorited' : 'Favorite') : 'Favorite'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        {{-- Borrow Confirmation Modal --}}
        <div
            x-cloak
            x-show="showBorrowModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[1300] flex items-center justify-center bg-black/40 px-4 py-8"
            @keydown.escape.window="closeBorrowModal()"
            x-effect="if (showBorrowModal && window.lucide) { setTimeout(() => lucide.createIcons(), 100); }"
        >
            <div
                x-on:click.away="closeBorrowModal()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-8"
                class="w-full max-w-md rounded-[24px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0]"
            >
                <div class="px-6 py-5 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-[#4b2036]">Confirm Borrow</h2>
                        <button
                            type="button"
                            @click="closeBorrowModal()"
                            class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8]"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4" x-show="selectedBook">
                    {{-- Book Information --}}
                    <div class="flex items-start gap-4 p-4 bg-[#fff7fb] rounded-[12px] border border-[#f3cbe0]">
                        <template x-if="selectedBook && selectedBook.image_path">
                            <img :src="selectedBook.image_path" :alt="selectedBook.book_name" class="w-16 h-20 object-cover rounded">
                        </template>
                        <template x-if="selectedBook && !selectedBook.image_path">
                            <div class="w-16 h-20 bg-[#f3cbe0] rounded flex items-center justify-center">
                                <i data-lucide="book" class="w-8 h-8 text-[#a03464]"></i>
                            </div>
                        </template>
                        <div class="flex-1">
                            <h3 class="font-semibold text-[#4b2036] mb-1" x-text="selectedBook ? selectedBook.book_name : ''"></h3>
                            <p class="text-sm text-[#7c4c63]" x-text="selectedBook ? selectedBook.authors_name : ''"></p>
                            <p class="text-xs text-[#7c4c63] mt-1" x-text="selectedBook ? 'ISBN: ' + selectedBook.isbn : ''"></p>
                        </div>
                    </div>

                    {{-- Success/Error Message --}}
                    <div
                        x-show="borrowMessage"
                        x-cloak
                        x-transition
                        :class="borrowMessageType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-rose-50 border-rose-200 text-rose-800'"
                        class="p-3 rounded-[10px] border text-sm"
                    >
                        <p x-text="borrowMessage"></p>
                    </div>

                    {{-- Borrow Details --}}
                    <div class="space-y-3" x-show="!borrowMessage">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-[10px] border border-blue-100">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i>
                                <span class="text-sm font-semibold text-[#4b2036]">Borrow Date</span>
                            </div>
                            <span class="text-sm font-semibold text-blue-700" x-text="getBorrowDate()"></span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-rose-50 rounded-[10px] border border-rose-100">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar-clock" class="w-5 h-5 text-rose-600"></i>
                                <span class="text-sm font-semibold text-[#4b2036]">Due Date</span>
                            </div>
                            <span class="text-sm font-semibold text-rose-700" x-text="getDueDate()"></span>
                        </div>

                        <div class="p-3 bg-amber-50 rounded-[10px] border border-amber-100">
                            <div class="flex items-start gap-2">
                                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 mt-0.5"></i>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-amber-800 mb-1">Important Reminder</p>
                                    <p class="text-xs text-amber-700">Please return this book within 3 business days (weekends excluded). Failure to return on time will result in overdue status and you won't be able to borrow other books until returned.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5 border-t border-[#f3cbe0] flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeBorrowModal()"
                        :disabled="isBorrowing"
                        class="rounded-[12px] border border-[#f3cbe0] px-6 py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="borrowMessageType === 'success'">Close</span>
                        <span x-show="borrowMessageType !== 'success' || !borrowMessage">Cancel</span>
                    </button>
                    <button
                        x-show="!borrowMessage || borrowMessageType === 'error'"
                        type="button"
                        @click="confirmBorrow()"
                        :disabled="isBorrowing"
                        class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg
                            x-show="isBorrowing"
                            class="h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                            <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path>
                        </svg>
                        <span x-text="isBorrowing ? 'Borrowing...' : 'Confirm Borrow'"></span>
                    </button>
                </div>
            </div>
        </div>
        </div>

        {{-- Reserve Confirmation Modal --}}
        <div
            x-cloak
            x-show="showReserveModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[1300] flex items-center justify-center bg-black/40 px-4 py-8"
            @keydown.escape.window="closeReserveModal()"
            x-effect="if (showReserveModal && window.lucide) { setTimeout(() => lucide.createIcons(), 100); }"
        >
            <div
                x-on:click.away="closeReserveModal()"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-8"
                class="w-full max-w-md rounded-[24px] bg-white shadow-[0_30px_60px_rgba(0,0,0,0.18)] border border-[#f3cbe0]"
            >
                <div class="px-6 py-5 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-[#4b2036]">Reserve Book</h2>
                        <button
                            type="button"
                            @click="closeReserveModal()"
                            class="rounded-full border border-[#f3cbe0] p-2 text-[#a03464] hover:bg-[#fff2f8]"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4" x-show="selectedBook">
                    {{-- Book Information --}}
                    <div class="flex items-start gap-4 p-4 bg-[#fff7fb] rounded-[12px] border border-[#f3cbe0]">
                        <template x-if="selectedBook && selectedBook.image_path">
                            <img :src="selectedBook.image_path" :alt="selectedBook.book_name" class="w-16 h-20 object-cover rounded">
                        </template>
                        <template x-if="selectedBook && !selectedBook.image_path">
                            <div class="w-16 h-20 bg-[#f3cbe0] rounded flex items-center justify-center">
                                <i data-lucide="book" class="w-8 h-8 text-[#a03464]"></i>
                            </div>
                        </template>
                        <div class="flex-1">
                            <h3 class="font-semibold text-[#4b2036] mb-1" x-text="selectedBook ? selectedBook.book_name : ''"></h3>
                            <p class="text-sm text-[#7c4c63]" x-text="selectedBook ? selectedBook.authors_name : ''"></p>
                            <p class="text-xs text-[#7c4c63] mt-1" x-text="selectedBook ? 'ISBN: ' + selectedBook.isbn : ''"></p>
                        </div>
                    </div>

                    {{-- Success/Error Message --}}
                    <div
                        x-show="reserveMessage"
                        x-cloak
                        x-transition
                        :class="reserveMessageType === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-rose-50 border-rose-200 text-rose-800'"
                        class="p-3 rounded-[10px] border text-sm"
                    >
                        <p x-text="reserveMessage"></p>
                    </div>

                    {{-- Reserve Details --}}
                    <div class="space-y-3" x-show="!reserveMessage">
                        <div>
                            <label class="block text-sm font-semibold text-[#4b2036] mb-2">
                                Reserve Date <span class="text-rose-600">*</span>
                            </label>
                            <input
                                type="date"
                                x-model="reserveDate"
                                @change="calculateDueDate()"
                                :min="new Date().toISOString().split('T')[0]"
                                class="w-full rounded-[10px] border border-[#f3cbe0] bg-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                            >
                            <p class="text-xs text-[#7c4c63] mt-1">Select when you want to reserve this book</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#4b2036] mb-2">
                                Due Date <span class="text-rose-600">*</span>
                            </label>
                            <input
                                type="date"
                                x-model="dueDate"
                                readonly
                                disabled
                                class="w-full rounded-[10px] border border-[#f3cbe0] bg-gray-50 px-4 py-2.5 text-sm text-[#7c4c63] cursor-not-allowed"
                            >
                            <p class="text-xs text-[#7c4c63] mt-1">Automatically calculated as 3 business days from reserve date (weekends excluded)</p>
                        </div>

                        <div class="p-3 bg-amber-50 rounded-[10px] border border-amber-100">
                            <div class="flex items-start gap-2">
                                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 mt-0.5"></i>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-amber-800 mb-1">Important Reminder</p>
                                    <p class="text-xs text-amber-700">You have 3 days to claim this reservation. If not claimed within 3 days, the reservation will be automatically voided.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5 border-t border-[#f3cbe0] flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeReserveModal()"
                        :disabled="isReserving"
                        class="rounded-[12px] border border-[#f3cbe0] px-6 py-2 text-sm font-semibold text-[#a03464] hover:bg-[#fff2f8] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="reserveMessageType === 'success'">Close</span>
                        <span x-show="reserveMessageType !== 'success' || !reserveMessage">Cancel</span>
                    </button>
                    <button
                        x-show="!reserveMessage || reserveMessageType === 'error'"
                        type="button"
                        @click="confirmReserve()"
                        :disabled="isReserving"
                        class="inline-flex items-center justify-center gap-2 rounded-[12px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg
                            x-show="isReserving"
                            class="h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                            <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path>
                        </svg>
                        <span x-text="isReserving ? 'Reserving...' : 'Confirm Reserve'"></span>
                    </button>
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
