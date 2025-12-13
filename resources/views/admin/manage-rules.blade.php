@extends('layout.admin.app')

@php
    $pageTitle = 'Manage Rules & Regulations';
    $labelClass = 'block text-xs font-semibold text-[#7c4c63] mb-1';
    $inputClass = 'w-full rounded-[10px] border border-[#f3cbe0] bg-[#fff7fb] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]';
@endphp

@section('title', $pageTitle . ' | OnShelf GTDL')
@section('page_title', $pageTitle)

@section('content')
    <div
        x-data="{
            showAddModal: {{ $errors->any() && old('_token') ? 'true' : 'false' }},
            showEditModal: {{ isset($editingRule) && $editingRule ? 'true' : 'false' }},
            editingRuleId: {{ isset($editingRule) && $editingRule ? $editingRule->id : 'null' }},
            editingRule: {{ isset($editingRule) && $editingRule ? json_encode($editingRule) : 'null' }},
            showConfirmModal: false,
            confirmAction: { formId: null, message: '' },
            isSaving: false,
            formErrors: {},
            successMessage: {{ json_encode(session('success') ?? '') }},
            showSuccessMessage: {{ session('success') ? 'true' : 'false' }},
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
                        this.successMessage = data.message || 'Rule has been deleted successfully!';
                        this.showSuccessMessage = true;
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.location.reload();
                }
            },
            openEditModal(ruleData) {
                if (typeof ruleData === 'number') {
                    const ruleElement = document.querySelector(`[data-rule-id='${ruleData}']`);
                    if (ruleElement) {
                        ruleData = JSON.parse(ruleElement.dataset.ruleData);
                    }
                }
                if (ruleData) {
                    this.editingRule = ruleData;
                    this.editingRuleId = ruleData.id;
                    this.formErrors = {};
                    this.showEditModal = true;
                    if (window.lucide) setTimeout(() => lucide.createIcons(), 100);
                }
            },
            closeEditModal() {
                this.showEditModal = false;
                this.editingRule = null;
                this.editingRuleId = null;
                this.formErrors = {};
                window.history.replaceState({}, '', '{{ route('admin.manage-rules') }}');
            },
            openAddModal() {
                this.showAddModal = true;
                this.formErrors = {};
                if (window.lucide) setTimeout(() => lucide.createIcons(), 100);
            },
            closeAddModal() {
                this.showAddModal = false;
                this.formErrors = {};
            },
            async toggleStatus(ruleId) {
                try {
                    const response = await fetch(`{{ route('admin.manage-rules') }}/${ruleId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }"
        x-init="
            if (window.lucide) { lucide.createIcons(); }
            if (showSuccessMessage && successMessage) {
                setTimeout(() => { showSuccessMessage = false; }, 5000);
            }
        "
        class="space-y-6"
    >
        {{-- Success Message --}}
        <div
            x-show="showSuccessMessage"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            class="rounded-[14px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 flex items-center justify-between"
        >
            <span x-text="successMessage"></span>
            <button @click="showSuccessMessage = false" class="text-green-600 hover:text-green-800">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#4b2036]">Rules & Regulations</h1>
                <p class="text-sm text-[#7c4c63] mt-1">Manage library rules and regulations</p>
            </div>
            <button
                @click="openAddModal()"
                class="rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition flex items-center gap-2"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add New Rule</span>
            </button>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.manage-rules') }}" class="flex gap-3">
            <div class="flex-1 relative">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search rules by title or description..."
                    class="w-full rounded-[10px] border border-[#f3cbe0] bg-[#fff7fb] px-4 py-2.5 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-[#d96a9f]"
                >
                <i data-lucide="search" class="w-4 h-4 text-[#7c4c63] absolute left-3 top-1/2 -translate-y-1/2"></i>
            </div>
            <button
                type="submit"
                class="rounded-[10px] bg-[#a03464] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#661d44] transition"
            >
                Search
            </button>
            @if(request('search'))
                <a
                    href="{{ route('admin.manage-rules') }}"
                    class="rounded-[10px] border border-[#f3cbe0] bg-white px-6 py-2.5 text-sm font-semibold text-[#a03464] hover:bg-[#fff7fb] transition"
                >
                    Clear
                </a>
            @endif
        </form>

        {{-- Rules List --}}
        <div class="rounded-[24px] border border-[#f3cbe0] bg-white shadow-sm overflow-hidden">
            @if($rules->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-[#4b2036]">
                        <thead class="bg-[#fde7f0] text-xs uppercase tracking-wider text-[#a03464]">
                            <tr>
                                <th class="px-6 py-3 whitespace-nowrap">Order</th>
                                <th class="px-6 py-3 whitespace-nowrap">Title</th>
                                <th class="px-6 py-3 whitespace-nowrap">Description</th>
                                <th class="px-6 py-3 whitespace-nowrap">Status</th>
                                <th class="px-6 py-3 whitespace-nowrap text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f7d6e6]">
                            @foreach($rules as $rule)
                                <tr
                                    @class([$loop->odd ? 'bg-[#fff7fb]' : 'bg-white'])
                                    data-rule-id="{{ $rule->id }}"
                                    data-rule-data="{{ json_encode([
                                        'id' => $rule->id,
                                        'title' => $rule->title,
                                        'description' => $rule->description,
                                        'order' => $rule->order,
                                        'is_active' => $rule->is_active,
                                    ]) }}"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold">{{ $rule->order }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold">{{ html_entity_decode($rule->title) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-md truncate text-[#7c4c63]">
                                            {{ html_entity_decode($rule->description) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            @click="toggleStatus({{ $rule->id }})"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold transition {{ $rule->is_active ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' }}"
                                        >
                                            <i data-lucide="{{ $rule->is_active ? 'check-circle' : 'x-circle' }}" class="w-3 h-3"></i>
                                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                @click="openEditModal({{ $rule->id }})"
                                                class="rounded-lg border border-[#f3cbe0] bg-white px-3 py-1.5 text-xs font-semibold text-[#a03464] hover:bg-[#fff7fb] transition"
                                            >
                                                <i data-lucide="edit" class="w-3 h-3"></i>
                                            </button>
                                            <button
                                                @click="openConfirm('delete-rule-{{ $rule->id }}', 'Are you sure you want to delete this rule?')"
                                                class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition"
                                            >
                                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            </button>
                                            <form
                                                id="delete-rule-{{ $rule->id }}"
                                                action="{{ route('admin.manage-rules.destroy', $rule) }}"
                                                method="POST"
                                                data-ajax="true"
                                                style="display: none;"
                                            >
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-[#f3cbe0]">
                    {{ $rules->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <i data-lucide="shield-check" class="w-16 h-16 text-[#a03464]/40 mx-auto mb-4"></i>
                    <h3 class="text-lg font-semibold text-[#4b2036] mb-2">No Rules Found</h3>
                    <p class="text-sm text-[#7c4c63] mb-4">Get started by adding your first rule.</p>
                    <button
                        @click="openAddModal()"
                        class="rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition inline-flex items-center gap-2"
                    >
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add New Rule</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Add Rule Modal --}}
        <div
            x-show="showAddModal"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="closeAddModal()"
        >
            <div
                @click.stop
                class="bg-white rounded-[24px] shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
            >
                <div class="p-6 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-[#4b2036]">Add New Rule</h2>
                        <button @click="closeAddModal()" class="text-[#7c4c63] hover:text-[#4b2036]">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                <form action="{{ route('admin.manage-rules.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="add-title" class="{{ $labelClass }}">Title <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="add-title"
                            name="title"
                            value="{{ old('title') }}"
                            required
                            class="{{ $inputClass }}"
                            placeholder="Enter rule title"
                        >
                        @error('title')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="add-description" class="{{ $labelClass }}">Description <span class="text-rose-500">*</span></label>
                        <textarea
                            id="add-description"
                            name="description"
                            rows="5"
                            required
                            class="{{ $inputClass }}"
                            placeholder="Enter rule description"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="add-order" class="{{ $labelClass }}">Order</label>
                            <input
                                type="number"
                                id="add-order"
                                name="order"
                                value="{{ old('order', 0) }}"
                                min="0"
                                class="{{ $inputClass }}"
                                placeholder="0"
                            >
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-[#f3cbe0] text-[#a03464] focus:ring-[#d96a9f]"
                                >
                                <span class="text-sm font-semibold text-[#7c4c63]">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#f3cbe0]">
                        <button
                            type="button"
                            @click="closeAddModal()"
                            class="rounded-[10px] border border-[#f3cbe0] bg-white px-6 py-2.5 text-sm font-semibold text-[#a03464] hover:bg-[#fff7fb] transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                        >
                            Add Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Rule Modal --}}
        <div
            x-show="showEditModal"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="closeEditModal()"
        >
            <div
                @click.stop
                class="bg-white rounded-[24px] shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
            >
                <div class="p-6 border-b border-[#f3cbe0]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-[#4b2036]">Edit Rule</h2>
                        <button @click="closeEditModal()" class="text-[#7c4c63] hover:text-[#4b2036]">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                <template x-if="editingRule">
                    <form :action="`{{ route('admin.manage-rules') }}/${editingRule.id}`" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="edit-title" class="{{ $labelClass }}">Title <span class="text-rose-500">*</span></label>
                            <input
                                type="text"
                                id="edit-title"
                                name="title"
                                x-model="editingRule.title"
                                required
                                class="{{ $inputClass }}"
                                placeholder="Enter rule title"
                            >
                        </div>
                        <div>
                            <label for="edit-description" class="{{ $labelClass }}">Description <span class="text-rose-500">*</span></label>
                            <textarea
                                id="edit-description"
                                name="description"
                                rows="5"
                                x-model="editingRule.description"
                                required
                                class="{{ $inputClass }}"
                                placeholder="Enter rule description"
                            ></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="edit-order" class="{{ $labelClass }}">Order</label>
                                <input
                                    type="number"
                                    id="edit-order"
                                    name="order"
                                    x-model="editingRule.order"
                                    min="0"
                                    class="{{ $inputClass }}"
                                    placeholder="0"
                                >
                            </div>
                            <div class="flex items-end">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        :checked="editingRule.is_active"
                                        class="w-4 h-4 rounded border-[#f3cbe0] text-[#a03464] focus:ring-[#d96a9f]"
                                    >
                                    <span class="text-sm font-semibold text-[#7c4c63]">Active</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#f3cbe0]">
                            <button
                                type="button"
                                @click="closeEditModal()"
                                class="rounded-[10px] border border-[#f3cbe0] bg-white px-6 py-2.5 text-sm font-semibold text-[#a03464] hover:bg-[#fff7fb] transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="rounded-[10px] bg-gradient-to-r from-[#e07aac] to-[#a03464] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#e07aac]/30 hover:opacity-95 transition"
                            >
                                Update Rule
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        {{-- Confirm Delete Modal --}}
        <div
            x-show="showConfirmModal"
            x-cloak
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="closeConfirm()"
        >
            <div
                @click.stop
                class="bg-white rounded-[24px] shadow-xl max-w-md w-full p-6"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
            >
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="alert-triangle" class="w-8 h-8 text-rose-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#4b2036] mb-2">Confirm Deletion</h3>
                    <p class="text-sm text-[#7c4c63]" x-text="confirmAction.message"></p>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button
                        @click="closeConfirm()"
                        class="rounded-[10px] border border-[#f3cbe0] bg-white px-6 py-2.5 text-sm font-semibold text-[#a03464] hover:bg-[#fff7fb] transition"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitConfirm()"
                        class="rounded-[10px] bg-rose-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-rose-700 transition"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

