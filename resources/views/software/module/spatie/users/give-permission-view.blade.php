<div class="col-md-12 mb-3">
    @php
        $groupedPermissions = collect($permissions)->groupBy('group');
        $checkedPermissionIds = [];
        if (isset($userDefualtPermissions)) {
            $checkedPermissionIds = collect($userDefualtPermissions)->pluck('id')->toArray();
        }
    @endphp

    <!-- Modules Grid -->
    <div class="permission-modules-container">
        @foreach ($groupedPermissions as $group => $perms)
            @php
                $groupSlug = Str::slug($group ?: 'other');
                $totalGroupPerms = count($perms);
            @endphp
            <div class="card border mb-3 shadow-none permission-group-card" style="border-radius: 10px; overflow: hidden;">
                <!-- Module Header with Group Select All Toggle -->
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e9ecef;">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0 fw-bold text-dark text-capitalize">{{ str_replace(['_', '-'], ' ', $group ?: 'General') }}</h6>
                        <span class="badge bg-label-secondary ms-2 group-count-badge" style="font-size: 11px;">0 / {{ $totalGroupPerms }}</span>
                    </div>

                    <div class="form-check mb-0 d-flex align-items-center">
                        <input type="checkbox" class="form-check-input group-checkbox cursor-pointer custom-lg-checkbox me-2" 
                               id="group-{{ $groupSlug }}" 
                               data-group="{{ $groupSlug }}">
                        <label class="form-check-label fw-bold text-primary cursor-pointer" for="group-{{ $groupSlug }}" style="font-size: 0.9rem;">
                            Select All Module
                        </label>
                    </div>
                </div>

                <!-- Module Permissions List -->
                <div class="card-body p-3">
                    <div class="row g-2">
                        @foreach ($perms as $i => $perm)
                            @php
                                $permId = is_array($perm) ? $perm['id'] ?? $i : $perm->id ?? $i;
                                $permName = is_array($perm) ? $perm['name'] ?? '' : $perm->name ?? '';
                                $isChecked = in_array($permId, $checkedPermissionIds);
                            @endphp
                            <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                                <div class="permission-item-box p-2 rounded border d-flex align-items-center h-100 {{ $isChecked ? 'border-primary bg-light-primary' : 'bg-white' }}"
                                     style="transition: all 0.2s ease; cursor: pointer;">
                                    <div class="form-check mb-0 w-100 d-flex align-items-center">
                                        <input type="checkbox" 
                                               class="form-check-input permission-checkbox me-2 cursor-pointer custom-lg-checkbox"
                                               id="perm-{{ $permId }}" 
                                               name="directPermission[]" 
                                               value="{{ $permName }}"
                                               data-group="{{ $groupSlug }}"
                                               {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label cursor-pointer text-dark fw-medium text-truncate w-100" 
                                               for="perm-{{ $permId }}"
                                               style="font-size: 0.88rem;"
                                               title="{{ $permName }}">
                                            {{ $permName }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @error('directPermission')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

<style>
    .custom-lg-checkbox {
        width: 19px !important;
        height: 19px !important;
        min-width: 19px !important;
        cursor: pointer;
        border-radius: 4px !important;
        border: 2px solid #8e85f3 !important;
    }
    .custom-lg-checkbox:checked {
        background-color: #7367f0 !important;
        border-color: #7367f0 !important;
    }
    .permission-item-box:hover {
        border-color: #7367f0 !important;
        background-color: #f8faff !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(115, 103, 240, 0.1);
    }
    .bg-light-primary {
        background-color: rgba(115, 103, 240, 0.07) !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    (function () {
        function initPermissionControls() {
            const container = document.querySelector('.permission-modules-container');
            if (!container) return;

            function updateAllCounters() {
                const allPerms = document.querySelectorAll('.permission-checkbox');
                const totalCount = allPerms.length;
                let checkedCount = 0;

                allPerms.forEach(chk => {
                    const box = chk.closest('.permission-item-box');
                    if (chk.checked) {
                        checkedCount++;
                        if (box) {
                            box.classList.add('border-primary', 'bg-light-primary');
                        }
                    } else {
                        if (box) {
                            box.classList.remove('border-primary', 'bg-light-primary');
                        }
                    }
                });

                // Update total stats
                const countElem = document.getElementById('selected-permission-count');
                const totalElem = document.getElementById('total-permission-count');
                if (countElem) countElem.textContent = checkedCount;
                if (totalElem) totalElem.textContent = totalCount;

                // Update group cards
                document.querySelectorAll('.permission-group-card').forEach(card => {
                    const groupChk = card.querySelector('.group-checkbox');
                    const perms = card.querySelectorAll('.permission-checkbox');
                    const badge = card.querySelector('.group-count-badge');
                    
                    const groupTotal = perms.length;
                    const groupChecked = [...perms].filter(p => p.checked).length;

                    if (badge) {
                        badge.textContent = `${groupChecked} / ${groupTotal}`;
                        if (groupChecked === groupTotal && groupTotal > 0) {
                            badge.className = 'badge bg-success ms-2 group-count-badge';
                        } else if (groupChecked > 0) {
                            badge.className = 'badge bg-primary ms-2 group-count-badge';
                        } else {
                            badge.className = 'badge bg-label-secondary ms-2 group-count-badge';
                        }
                    }

                    if (groupChk) {
                        groupChk.checked = (groupChecked === groupTotal && groupTotal > 0);
                        groupChk.indeterminate = (groupChecked > 0 && groupChecked < groupTotal);
                    }
                });
            }

            // Global Select All Button
            const btnSelectAll = document.getElementById('btn-select-all-global');
            if (btnSelectAll) {
                btnSelectAll.onclick = function () {
                    document.querySelectorAll('.permission-checkbox').forEach(chk => chk.checked = true);
                    updateAllCounters();
                };
            }

            // Global Deselect All Button
            const btnDeselectAll = document.getElementById('btn-deselect-all-global');
            if (btnDeselectAll) {
                btnDeselectAll.onclick = function () {
                    document.querySelectorAll('.permission-checkbox').forEach(chk => chk.checked = false);
                    updateAllCounters();
                };
            }

            // Group-level switch change
            document.querySelectorAll('.group-checkbox').forEach(groupChk => {
                groupChk.onchange = function () {
                    const card = this.closest('.permission-group-card');
                    if (card) {
                        card.querySelectorAll('.permission-checkbox').forEach(chk => {
                            chk.checked = groupChk.checked;
                        });
                        updateAllCounters();
                    }
                };
            });

            // Individual permission checkbox change
            document.querySelectorAll('.permission-checkbox').forEach(permChk => {
                permChk.onchange = function () {
                    updateAllCounters();
                };
            });

            // Initial counter computation
            updateAllCounters();
        }

        // Execute immediately & when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPermissionControls);
        } else {
            initPermissionControls();
        }
    })();
</script>

