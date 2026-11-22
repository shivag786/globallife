<x-layouts.app title="Departments & Designations" heading="Departments & Designations">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Departments</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.hr.departments.store') }}" class="row g-2 mb-3">
                        @csrf
                        <div class="col-5"><input name="name" class="form-control form-control-sm" placeholder="Name, e.g. Sales" required></div>
                        <div class="col-5"><input name="description" class="form-control form-control-sm" placeholder="Description (optional)"></div>
                        <div class="col-2"><button class="btn btn-brand btn-sm w-100">Add</button></div>
                    </form>

                    @forelse ($departments as $d)
                        <div class="border-top py-2">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <strong>{{ $d->name }}</strong>
                                    @unless ($d->is_active) <span class="badge text-bg-secondary">Inactive</span> @endunless
                                    <div class="fs-xs text-secondary">{{ $d->employees_count }} employees &middot; {{ $d->designations_count }} designations {{ $d->description ? '· '.$d->description : '' }}</div>
                                </div>
                                <div class="text-nowrap">
                                    <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#dept-{{ $d->id }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.hr.departments.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Delete this department?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0 ms-2">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.hr.departments.update', $d) }}" class="collapse row g-2 mt-1" id="dept-{{ $d->id }}">
                                @csrf @method('PUT')
                                <div class="col-4"><input name="name" value="{{ $d->name }}" class="form-control form-control-sm" required></div>
                                <div class="col-4"><input name="description" value="{{ $d->description }}" class="form-control form-control-sm"></div>
                                <div class="col-2 d-flex align-items-center">
                                    <label class="form-check small mb-0"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($d->is_active)> Active</label>
                                </div>
                                <div class="col-2"><button class="btn btn-sm btn-outline-secondary w-100">Save</button></div>
                            </form>
                        </div>
                    @empty
                        <p class="small text-secondary mb-0">No departments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Designations</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.hr.designations.store') }}" class="row g-2 mb-3">
                        @csrf
                        <div class="col-5"><input name="name" class="form-control form-control-sm" placeholder="e.g. Sales Executive" required></div>
                        <div class="col-5">
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">Any department</option>
                                @foreach ($departments as $d) <option value="{{ $d->id }}">{{ $d->name }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-2"><button class="btn btn-brand btn-sm w-100">Add</button></div>
                    </form>

                    @forelse ($designations as $g)
                        <div class="border-top py-2">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <div>
                                    <strong>{{ $g->name }}</strong>
                                    @unless ($g->is_active) <span class="badge text-bg-secondary">Inactive</span> @endunless
                                    <div class="fs-xs text-secondary">{{ $g->department?->name ?? 'Any department' }} &middot; {{ $g->employees_count }} employees</div>
                                </div>
                                <div class="text-nowrap">
                                    <button class="btn btn-sm btn-link p-0" data-bs-toggle="collapse" data-bs-target="#desig-{{ $g->id }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.hr.designations.destroy', $g) }}" class="d-inline" onsubmit="return confirm('Delete this designation?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-link text-danger p-0 ms-2">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('admin.hr.designations.update', $g) }}" class="collapse row g-2 mt-1" id="desig-{{ $g->id }}">
                                @csrf @method('PUT')
                                <div class="col-4"><input name="name" value="{{ $g->name }}" class="form-control form-control-sm" required></div>
                                <div class="col-4">
                                    <select name="department_id" class="form-select form-select-sm">
                                        <option value="">Any department</option>
                                        @foreach ($departments as $d) <option value="{{ $d->id }}" @selected($g->department_id === $d->id)>{{ $d->name }}</option> @endforeach
                                    </select>
                                </div>
                                <div class="col-2 d-flex align-items-center">
                                    <label class="form-check small mb-0"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($g->is_active)> Active</label>
                                </div>
                                <div class="col-2"><button class="btn btn-sm btn-outline-secondary w-100">Save</button></div>
                            </form>
                        </div>
                    @empty
                        <p class="small text-secondary mb-0">No designations yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
