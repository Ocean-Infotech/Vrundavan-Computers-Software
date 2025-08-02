<div class="form-group col-md-12">
    <label for="formGroupExampleInput" class="d-block">Select Role base user permission</label>

    @php
        $groupedPermissions = collect($permissions)->groupBy('group');
        $checkedPermissionIds = [];
        if (isset($userDefualtPermissions)) {
            $checkedPermissionIds = collect($userDefualtPermissions)->pluck('id')->toArray();
        }
    @endphp

    @foreach ($groupedPermissions as $group => $perms)
        <div class="border rounded p-3 mb-2">
            <div class="custom-control custom-switch">
                {{-- <input type="checkbox" class="custom-control-input group-checkbox" id="group-{{ Str::slug($group) }}"> --}}
                <label class="custom-control-label font-weight-bold"
                    for="group-{{ Str::slug($group) }}">{{ ucfirst($group) }}</label>
            </div>
            <div class="row mt-2">
                @foreach ($perms as $i => $perm)
                    @php
                        $permId = is_array($perm) ? $perm['id'] ?? $i : $perm->id ?? $i;
                        $permName = is_array($perm) ? $perm['name'] ?? '' : $perm->name ?? '';
                    @endphp
                    {{-- {{ dd('L-13', $permissions, $checkedPermissionIds, $perms, $group, $perm) }} --}}
                    <div class="col-md-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input permission-checkbox"
                                id="perm-{{ $permId }}" name="directPermission[]" value="{{ $permName }}"
                                {{ in_array($permId, $checkedPermissionIds) ? 'checked' : '' }}>

                            <label class="custom-control-label"
                                for="perm-{{ $permId }}">{{ $permName }}</label>
                            {{-- <input type="checkbox" class="custom-control-input permission-checkbox"
                                id="perm-{{ $perm?->id ?? $i }}" name="directPermission[]"
                                value="{{ $perm?->name ?? '' }}"
                                {{ isset($checkedPermissionIds) && in_array($perm?->id, $checkedPermissionIds) ? 'checked' : '' }}>
                            <label class="custom-control-label"
                                for="perm-{{ $perm?->id ?? $i }}">{{ $perm?->name ?? '-' }}</label> --}}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @error('directPermission')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Handle group checkbox click
        document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
            groupCheckbox.addEventListener('change', function() {
                let groupId = this?.id?.replace('group-', '');
                let checkboxes = document.querySelectorAll(`.permission-checkbox[id^="perm-"]`);

                checkboxes.forEach(checkbox => {
                    if (checkbox.closest('.border').querySelector(
                            `#group-${groupId}`)) {
                        checkbox.checked = this.checked;
                    }
                });
            });
        });

        // Handle individual permission checkbox click
        document.querySelectorAll('.permission-checkbox').forEach(permissionCheckbox => {
            permissionCheckbox.addEventListener('change', function() {
                let groupElement = this.closest('.border').querySelector('.group-checkbox');
                let groupPermissions = this.closest('.border').querySelectorAll(
                    '.permission-checkbox');
                let allChecked = [...groupPermissions].every(checkbox => checkbox.checked);
                let anyChecked = [...groupPermissions].some(checkbox => checkbox.checked);

                groupElement.checked = allChecked;
                groupElement.indeterminate = !allChecked && anyChecked;
            });
        });

        // Initialize group checkboxes based on existing selections
        document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
            let groupPermissions = groupCheckbox.closest('.border').querySelectorAll(
                '.permission-checkbox');
            let allChecked = [...groupPermissions].every(checkbox => checkbox.checked);
            let anyChecked = [...groupPermissions].some(checkbox => checkbox.checked);

            groupCheckbox.checked = allChecked;
            groupCheckbox.indeterminate = !allChecked && anyChecked;
        });
    });
</script>
