@extends('layout.teacher.reader')

@section('title', html_entity_decode($ebook->title, ENT_QUOTES, 'UTF-8') . ' | OnShelf GTDL')
@section('page_title', 'Reading: ' . html_entity_decode($ebook->title, ENT_QUOTES, 'UTF-8'))

@section('content')
    <div id="ebook-reader" class="fixed inset-0 bg-[#f6e5ef] text-[#4b2036]">
        {{-- Top Bar with Book Info --}}
        <div
            id="top-controls"
            class="absolute top-0 left-0 right-0 z-50 bg-transparent transition-opacity duration-200"
        >
            <div class="flex items-center justify-between px-3 sm:px-6 py-3 sm:py-4 gap-2 sm:gap-4">
                <div class="flex items-center gap-2 sm:gap-4 flex-1 min-w-0">
                    <a
                        href="{{ route('teacher.ebooks') }}"
                        class="inline-flex items-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 rounded-lg bg-white/90 backdrop-blur-sm hover:bg-white border border-[#f3cbe0]/50 transition text-xs sm:text-sm font-medium text-[#4b2036] shadow-sm active:scale-95"
                    >
                        <i data-lucide="arrow-left" class="w-4 h-4 flex-shrink-0"></i>
                        <span class="hidden sm:inline">Back to Library</span>
                        <span class="sm:hidden">Back</span>
                    </a>
                    <div class="hidden md:block px-3 sm:px-4 py-2 rounded-lg bg-white/90 backdrop-blur-sm border border-[#f3cbe0]/50 shadow-sm flex-1 min-w-0">
                        <h1 class="text-base sm:text-lg font-bold text-[#4b2036] truncate">{{ html_entity_decode($ebook->title, ENT_QUOTES, 'UTF-8') }}</h1>
                        <p class="text-xs sm:text-sm text-[#7c4c63] truncate">{{ html_entity_decode($ebook->authors ?? 'Unknown Author', ENT_QUOTES, 'UTF-8') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button
                        id="fullscreen-btn"
                        class="p-2 rounded-lg bg-white/90 backdrop-blur-sm hover:bg-white border border-[#f3cbe0]/50 transition text-[#4b2036] shadow-sm active:scale-95"
                        title="Toggle Fullscreen"
                    >
                        <i data-lucide="maximize" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="flex items-center justify-center min-h-screen pt-14 sm:pt-16 pb-24 sm:pb-20 px-0 sm:px-4">
            @if($fileExtension === 'pdf')
                <div class="w-full sm:max-w-6xl">
                    <div id="pdf-container" class="bg-transparent rounded-lg overflow-auto max-h-[calc(100vh-7rem)] sm:max-h-[calc(100vh-8rem)] p-0 sm:p-4">
                        <div class="flex items-center justify-center min-h-[300px] sm:min-h-[400px]">
                            <div class="text-center">
                                <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-b-2 border-[#a03464] mb-4"></div>
                                <p class="text-xs sm:text-sm text-[#7c4c63]">Initializing PDF viewer...</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- For non-PDF files, show iframe or download link --}}
                <div class="w-full sm:max-w-6xl bg-white rounded-lg shadow-2xl p-4 sm:p-8 mx-4 sm:mx-0">
                    <div class="text-center">
                        <i data-lucide="file-text" class="w-12 h-12 sm:w-16 sm:h-16 text-[#a03464] mx-auto mb-4"></i>
                        <h2 class="text-lg sm:text-2xl font-bold text-[#4b2036] mb-2 px-2">{{ html_entity_decode($ebook->title, ENT_QUOTES, 'UTF-8') }}</h2>
                        <p class="text-xs sm:text-sm text-[#7c4c63] mb-4 sm:mb-6">This file format is not supported for inline viewing.</p>
                        <a
                            href="{{ $ebookFileUrl }}"
                            download
                            class="inline-flex items-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg bg-gradient-to-r from-[#e07aac] to-[#a03464] text-white text-sm sm:text-base font-semibold hover:opacity-95 transition active:scale-95"
                        >
                            <i data-lucide="download" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            <span>Download to Read</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Bottom Controls --}}
        @if($fileExtension === 'pdf')
        <div
            id="bottom-controls"
            class="absolute bottom-0 left-0 right-0 z-50 bg-transparent transition-opacity duration-200"
        >
            <div class="px-3 sm:px-6 py-3 sm:py-4">
                <div class="w-full sm:max-w-6xl sm:mx-auto">
                    {{-- Page Navigation --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4 mb-3 sm:mb-4">
                        <div class="flex items-center justify-center gap-2">
                            <button
                                id="prev-page-btn"
                                class="p-2 rounded-lg bg-white/90 backdrop-blur-sm hover:bg-white border border-[#f3cbe0]/50 transition disabled:opacity-50 disabled:cursor-not-allowed text-[#4b2036] shadow-sm active:scale-95"
                                title="Previous Page"
                            >
                                <i data-lucide="chevron-left" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                            <div class="flex items-center gap-1.5 sm:gap-2 px-2 sm:px-4 py-2 bg-white/90 backdrop-blur-sm border border-[#f3cbe0]/50 rounded-lg shadow-sm">
                                <input
                                    type="number"
                                    id="page-input"
                                    min="1"
                                    class="w-12 sm:w-16 px-1.5 sm:px-2 py-1 bg-white/80 border border-[#f3cbe0]/50 rounded text-center text-xs sm:text-sm text-[#4b2036] focus:outline-none focus:ring-2 focus:ring-[#a03464]/50"
                                />
                                <span class="text-xs sm:text-sm text-[#7c4c63]">/</span>
                                <span class="text-xs sm:text-sm font-medium text-[#4b2036] whitespace-nowrap">
                                    <span class="hidden sm:inline">Page </span><span id="current-page">1</span> <span class="hidden sm:inline">of</span> <span class="sm:hidden">/</span> <span id="total-pages">1</span>
                                </span>
                            </div>
                            <button
                                id="next-page-btn"
                                class="p-2 rounded-lg bg-white/90 backdrop-blur-sm hover:bg-white border border-[#f3cbe0]/50 transition disabled:opacity-50 disabled:cursor-not-allowed text-[#4b2036] shadow-sm active:scale-95"
                                title="Next Page"
                            >
                                <i data-lucide="chevron-right" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                        </div>

                        {{-- Zoom Controls --}}
                        <div class="flex items-center justify-center gap-1.5 sm:gap-2">
                            <button
                                id="zoom-out-btn"
                                class="p-2 rounded-lg bg-white/90 backdrop-blur-sm hover:bg-white border border-[#f3cbe0]/50 transition disabled:opacity-50 disabled:cursor-not-allowed text-[#4b2036] shadow-sm active:scale-95"
                                title="Zoom Out"
                            >
                                <i data-lucide="zoom-out" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                            <span class="px-2 sm:px-3 py-1.5 sm:py-2 bg-white/90 backdrop-blur-sm border border-[#f3cbe0]/50 rounded-lg text-xs sm:text-sm font-medium min-w-[3rem] sm:min-w-[4rem] text-center text-[#4b2036] shadow-sm">
                                <span id="zoom-level">100</span>%
                            </span>
                            <button
                                id="zoom-in-btn"
                                class="p-2 rounded-lg bg-white/90 backdrop-blur-sm hover:bg-white border border-[#f3cbe0]/50 transition disabled:opacity-50 disabled:cursor-not-allowed text-[#4b2036] shadow-sm active:scale-95"
                                title="Zoom In"
                            >
                                <i data-lucide="zoom-in" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                            <button
                                id="reset-zoom-btn"
                                class="p-2 rounded-lg bg-white/90 backdrop-blur-sm hover:bg-white border border-[#f3cbe0]/50 transition text-[#4b2036] shadow-sm active:scale-95"
                                title="Reset Zoom"
                            >
                                <i data-lucide="maximize-2" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-white/90 backdrop-blur-sm border border-[#f3cbe0]/50 rounded-full h-1.5 shadow-sm">
                        <div
                            id="progress-bar"
                            class="bg-gradient-to-r from-[#e07aac] to-[#a03464] h-1.5 rounded-full transition-all duration-300"
                            style="width: 0%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Keyboard Shortcuts Hint --}}
        <div
            id="shortcuts-hint"
            class="hidden sm:block absolute bottom-24 right-6 z-40 bg-white/95 backdrop-blur-sm border border-[#f3cbe0] rounded-lg p-3 text-xs text-[#7c4c63] shadow-lg transition-opacity duration-200"
        >
            <p class="mb-1 font-semibold">Keyboard Shortcuts:</p>
            <p>← Previous Page</p>
            <p>→ Next Page</p>
            <p>ESC Exit Fullscreen</p>
        </div>
    </div>

    {{-- PDF.js Library --}}
    @if($fileExtension === 'pdf')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    @endif

    <script>
        (function() {
            // PDF Viewer State
            const state = {
                currentPage: 1,
                totalPages: 1,
                zoomLevel: 1,
                isFullscreen: false,
                showControls: true,
                controlsTimeout: null,
                pdfDoc: null,
                pageRendering: false,
                pageNumPending: null,
                scale: 1.0
            };

            // DOM Elements
            const elements = {
                topControls: document.getElementById('top-controls'),
                bottomControls: document.getElementById('bottom-controls'),
                shortcutsHint: document.getElementById('shortcuts-hint'),
                pdfContainer: document.getElementById('pdf-container'),
                prevPageBtn: document.getElementById('prev-page-btn'),
                nextPageBtn: document.getElementById('next-page-btn'),
                pageInput: document.getElementById('page-input'),
                currentPageSpan: document.getElementById('current-page'),
                totalPagesSpan: document.getElementById('total-pages'),
                zoomInBtn: document.getElementById('zoom-in-btn'),
                zoomOutBtn: document.getElementById('zoom-out-btn'),
                resetZoomBtn: document.getElementById('reset-zoom-btn'),
                zoomLevelSpan: document.getElementById('zoom-level'),
                progressBar: document.getElementById('progress-bar'),
                fullscreenBtn: document.getElementById('fullscreen-btn'),
                reader: document.getElementById('ebook-reader')
            };

            // Wait for PDF.js to load
            async function waitForPDFJS() {
                let attempts = 0;
                const maxAttempts = 50;
                while (!window.pdfjsLib || !window.pdfjsLib.getDocument) {
                    if (attempts >= maxAttempts) {
                        console.error("PDF.js failed to load");
                        return false;
                    }
                    await new Promise(resolve => setTimeout(resolve, 100));
                    attempts++;
                }
                return true;
            }

            // Controls visibility
            function setupControlsTimeout() {
                clearTimeout(state.controlsTimeout);
                state.controlsTimeout = setTimeout(() => {
                    state.showControls = false;
                    updateControlsVisibility();
                }, 3000);
            }

            function showControlsTemporarily() {
                state.showControls = true;
                updateControlsVisibility();
                setupControlsTimeout();
            }

            function updateControlsVisibility() {
                if (elements.topControls) {
                    if (state.showControls) {
                        elements.topControls.classList.remove('opacity-0', 'pointer-events-none');
                        elements.topControls.classList.add('opacity-100', 'pointer-events-auto');
                    } else {
                        elements.topControls.classList.remove('opacity-100', 'pointer-events-auto');
                        elements.topControls.classList.add('opacity-0', 'pointer-events-none');
                    }
                }
                if (elements.bottomControls) {
                    if (state.showControls) {
                        elements.bottomControls.classList.remove('opacity-0', 'pointer-events-none');
                        elements.bottomControls.classList.add('opacity-100', 'pointer-events-auto');
                    } else {
                        elements.bottomControls.classList.remove('opacity-100', 'pointer-events-auto');
                        elements.bottomControls.classList.add('opacity-0', 'pointer-events-none');
                    }
                }
                if (elements.shortcutsHint) {
                    if (state.showControls) {
                        elements.shortcutsHint.classList.remove('opacity-0', 'pointer-events-none');
                        elements.shortcutsHint.classList.add('opacity-100', 'pointer-events-auto');
                    } else {
                        elements.shortcutsHint.classList.remove('opacity-100', 'pointer-events-auto');
                        elements.shortcutsHint.classList.add('opacity-0', 'pointer-events-none');
                    }
                }
            }

            // Load PDF
            async function loadPDF() {
                try {
                    if (!window.pdfjsLib) {
                        showError("PDF.js library failed to load. Please refresh the page.");
                        return;
                    }

                    const pdfjsLib = window.pdfjsLib;
                    if (!pdfjsLib.GlobalWorkerOptions.workerSrc) {
                        pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
                    }

                    const pdfUrl = @json($ebookFileUrl);
                    console.log("Loading PDF from:", pdfUrl);

                    if (elements.pdfContainer) {
                        elements.pdfContainer.innerHTML = `
                            <div class="flex items-center justify-center min-h-[400px]">
                                <div class="text-center">
                                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#a03464] mb-4"></div>
                                    <p class="text-sm text-[#7c4c63]">Loading PDF...</p>
                                </div>
                            </div>
                        `;
                    }

                    const loadingTask = pdfjsLib.getDocument({
                        url: pdfUrl,
                        cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                        cMapPacked: true,
                    });

                    state.pdfDoc = await loadingTask.promise;
                    state.totalPages = state.pdfDoc.numPages;
                    state.currentPage = 1;

                    if (elements.pdfContainer) {
                        elements.pdfContainer.innerHTML = '<canvas id="pdf-canvas" class="mx-auto block"></canvas>';
                    }

                    await new Promise(resolve => setTimeout(resolve, 100));
                    renderPage(1);
                    updatePageInfo();
                } catch (error) {
                    console.error("Error loading PDF:", error);
                    showError("Unable to load the PDF file: " + (error.message || "Unknown error"));
                }
            }

            function showError(message) {
                if (elements.pdfContainer) {
                    elements.pdfContainer.innerHTML = `
                        <div class="text-center py-12 px-4">
                            <i data-lucide="alert-circle" class="w-16 h-16 text-red-500 mx-auto mb-4"></i>
                            <h2 class="text-xl font-semibold text-[#4b2036] mb-2">Error Loading PDF</h2>
                            <p class="text-sm text-[#7c4c63] mb-4">${message}</p>
                            <button
                                onclick="window.location.reload()"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-[#e07aac] to-[#a03464] text-white text-sm font-semibold hover:opacity-95 transition"
                            >
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                <span>Retry</span>
                            </button>
                        </div>
                    `;
                    if (window.lucide) lucide.createIcons();
                }
            }

            // Render PDF page
            async function renderPage(num) {
                if (!state.pdfDoc) {
                    console.error("PDF document not loaded");
                    return;
                }

                state.pageRendering = true;

                try {
                    const page = await state.pdfDoc.getPage(num);
                    const viewport = page.getViewport({ scale: state.scale * state.zoomLevel });
                    const canvas = document.getElementById("pdf-canvas");

                    if (!canvas) {
                        console.error("Canvas element not found");
                        state.pageRendering = false;
                        return;
                    }

                    const context = canvas.getContext("2d");
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    context.clearRect(0, 0, canvas.width, canvas.height);

                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };

                    await page.render(renderContext).promise;
                    state.pageRendering = false;

                    if (state.pageNumPending !== null) {
                        renderPage(state.pageNumPending);
                        state.pageNumPending = null;
                    }
                } catch (error) {
                    console.error("Error rendering page:", error);
                    state.pageRendering = false;
                    showError("Error rendering page: " + (error.message || "Unknown error"));
                }
            }

            function queueRenderPage(num) {
                if (state.pageRendering) {
                    state.pageNumPending = num;
                } else {
                    renderPage(num);
                }
            }

            // Page navigation
            function prevPage() {
                if (state.currentPage <= 1) return;
                state.currentPage--;
                queueRenderPage(state.currentPage);
                updatePageInfo();
            }

            function nextPage() {
                if (state.currentPage >= state.totalPages) return;
                state.currentPage++;
                queueRenderPage(state.currentPage);
                updatePageInfo();
            }

            function goToPage(pageNum) {
                const num = parseInt(pageNum);
                if (num < 1 || num > state.totalPages) {
                    elements.pageInput.value = state.currentPage;
                    return;
                }
                state.currentPage = num;
                queueRenderPage(state.currentPage);
                updatePageInfo();
            }

            function updatePageInfo() {
                if (elements.currentPageSpan) {
                    elements.currentPageSpan.textContent = state.currentPage;
                }
                if (elements.totalPagesSpan) {
                    elements.totalPagesSpan.textContent = state.totalPages;
                }
                if (elements.pageInput) {
                    elements.pageInput.value = state.currentPage;
                    elements.pageInput.max = state.totalPages;
                }
                if (elements.progressBar) {
                    const progress = (state.currentPage / state.totalPages) * 100;
                    elements.progressBar.style.width = progress + '%';
                }
                updateButtonStates();
            }

            function updateButtonStates() {
                if (elements.prevPageBtn) {
                    elements.prevPageBtn.disabled = state.currentPage <= 1;
                }
                if (elements.nextPageBtn) {
                    elements.nextPageBtn.disabled = state.currentPage >= state.totalPages;
                }
                if (elements.zoomOutBtn) {
                    elements.zoomOutBtn.disabled = state.zoomLevel <= 0.5;
                }
                if (elements.zoomInBtn) {
                    elements.zoomInBtn.disabled = state.zoomLevel >= 3;
                }
                if (elements.zoomLevelSpan) {
                    elements.zoomLevelSpan.textContent = Math.round(state.zoomLevel * 100);
                }
            }

            // Zoom controls
            function zoomIn() {
                if (state.zoomLevel >= 3) return;
                state.zoomLevel += 0.25;
                queueRenderPage(state.currentPage);
                updateButtonStates();
            }

            function zoomOut() {
                if (state.zoomLevel <= 0.5) return;
                state.zoomLevel -= 0.25;
                queueRenderPage(state.currentPage);
                updateButtonStates();
            }

            function resetZoom() {
                state.zoomLevel = 1;
                queueRenderPage(state.currentPage);
                updateButtonStates();
            }

            // Fullscreen
            function toggleFullscreen() {
                if (!state.isFullscreen) {
                    enterFullscreen();
                } else {
                    exitFullscreen();
                }
            }

            function enterFullscreen() {
                const elem = document.documentElement;
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) {
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) {
                    elem.msRequestFullscreen();
                }
                state.isFullscreen = true;
            }

            function exitFullscreen() {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                state.isFullscreen = false;
            }

            // Protection functions
            function setupProtection() {
                // Disable right-click context menu
                document.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    return false;
                });

                // Disable text selection
                document.addEventListener('selectstart', function(e) {
                    e.preventDefault();
                    return false;
                });

                // Disable drag and drop
                document.addEventListener('dragstart', function(e) {
                    e.preventDefault();
                    return false;
                });

                // Disable copy
                document.addEventListener('copy', function(e) {
                    e.clipboardData.setData('text/plain', '');
                    e.preventDefault();
                    return false;
                });

                // Disable cut
                document.addEventListener('cut', function(e) {
                    e.clipboardData.setData('text/plain', '');
                    e.preventDefault();
                    return false;
                });

                // Prevent image saving
                document.addEventListener('dragstart', function(e) {
                    if (e.target.tagName === 'IMG' || e.target.tagName === 'CANVAS') {
                        e.preventDefault();
                        return false;
                    }
                });

                // Disable print screen (limited effectiveness)
                window.addEventListener('keyup', function(e) {
                    if (e.key === 'PrintScreen' || e.keyCode === 44) {
                        navigator.clipboard.writeText('').catch(() => {});
                        alert('Screenshots are not allowed on this protected content.');
                    }
                });

                // Aggressive blur on window focus loss (prevents screenshot tools)
                let isBlurred = false;
                let blurTimeout;
                window.addEventListener('blur', function() {
                    clearTimeout(blurTimeout);
                    blurTimeout = setTimeout(function() {
                        if (!isBlurred) {
                            document.body.style.filter = 'blur(10px)';
                            document.body.style.opacity = '0.3';
                            isBlurred = true;
                        }
                    }, 100);
                });
                window.addEventListener('focus', function() {
                    clearTimeout(blurTimeout);
                    if (isBlurred) {
                        document.body.style.filter = 'none';
                        document.body.style.opacity = '1';
                        isBlurred = false;
                    }
                });

                // Detect window resize (might indicate screenshot tool)
                let resizeTimer;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(function() {
                        if (window.outerWidth !== window.innerWidth || window.outerHeight !== window.innerHeight) {
                            document.body.style.filter = 'blur(8px)';
                            setTimeout(function() {
                                document.body.style.filter = 'none';
                            }, 500);
                        }
                    }, 100);
                });

                // Detect when window is minimized or hidden
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        document.body.style.filter = 'blur(10px)';
                        document.body.style.opacity = '0.2';
                    } else {
                        document.body.style.filter = 'none';
                        document.body.style.opacity = '1';
                    }
                });

                // Disable developer tools detection
                let devtools = {open: false};
                setInterval(function() {
                    if (window.outerHeight - window.innerHeight > 200 || window.outerWidth - window.innerWidth > 200) {
                        if (!devtools.open) {
                            devtools.open = true;
                            document.body.style.filter = 'blur(5px)';
                            alert('Developer tools detected. Please close them to continue viewing.');
                        }
                    } else {
                        if (devtools.open) {
                            devtools.open = false;
                            document.body.style.filter = 'none';
                        }
                    }
                }, 500);
            }

            // Event listeners
            function setupEventListeners() {
                // Mouse movement to show controls
                if (elements.reader) {
                    elements.reader.addEventListener('mousemove', showControlsTemporarily);
                    elements.reader.addEventListener('click', showControlsTemporarily);
                }

                // Keyboard shortcuts (merged with protection)
                document.addEventListener('keydown', (e) => {
                    // Allow navigation keys
                    if (e.key === 'ArrowLeft') {
                        prevPage();
                        return;
                    } else if (e.key === 'ArrowRight') {
                        nextPage();
                        return;
                    } else if (e.key === 'Escape' && state.isFullscreen) {
                        exitFullscreen();
                        return;
                    }

                    // Protection: Disable screenshot and developer tools shortcuts
                    if (e.key === 'PrintScreen' || e.keyCode === 44) {
                        e.preventDefault();
                        return false;
                    }
                    if (e.key === 'F12' || e.keyCode === 123) {
                        e.preventDefault();
                        return false;
                    }
                    if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) {
                        e.preventDefault();
                        return false;
                    }
                    if (e.ctrlKey && (e.key === 'u' || e.key === 's' || e.key === 'p')) {
                        e.preventDefault();
                        return false;
                    }
                });

                // Fullscreen changes
                document.addEventListener('fullscreenchange', () => {
                    state.isFullscreen = !!document.fullscreenElement;
                });
                document.addEventListener('webkitfullscreenchange', () => {
                    state.isFullscreen = !!document.webkitFullscreenElement;
                });
                document.addEventListener('msfullscreenchange', () => {
                    state.isFullscreen = !!document.msFullscreenElement;
                });

                // Button events
                if (elements.prevPageBtn) {
                    elements.prevPageBtn.addEventListener('click', prevPage);
                }
                if (elements.nextPageBtn) {
                    elements.nextPageBtn.addEventListener('click', nextPage);
                }
                if (elements.pageInput) {
                    elements.pageInput.addEventListener('change', (e) => {
                        goToPage(e.target.value);
                    });
                }
                if (elements.zoomInBtn) {
                    elements.zoomInBtn.addEventListener('click', zoomIn);
                }
                if (elements.zoomOutBtn) {
                    elements.zoomOutBtn.addEventListener('click', zoomOut);
                }
                if (elements.resetZoomBtn) {
                    elements.resetZoomBtn.addEventListener('click', resetZoom);
                }
                if (elements.fullscreenBtn) {
                    elements.fullscreenBtn.addEventListener('click', toggleFullscreen);
                }
            }

            // Initialize
            document.addEventListener('DOMContentLoaded', async function() {
                if (window.lucide) {
                    lucide.createIcons();
                }

                // Setup protection first
                setupProtection();

                setupEventListeners();
                updateControlsVisibility();
                setupControlsTimeout();

                @if($fileExtension === 'pdf')
                    if (await waitForPDFJS()) {
                        loadPDF();
                    } else {
                        showError("PDF.js library failed to load. Please refresh the page.");
                    }
                @endif
            });
        })();
    </script>
@endsection

