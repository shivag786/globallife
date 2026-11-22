<ul class="nav nav-pills small mb-3 gap-1">
    @foreach ($tabs as $tab)
        <li class="nav-item">
            <a href="{{ route($route, array_merge(request()->except('page', 'status'), ['status' => $tab])) }}"
               class="nav-link py-1 px-3 {{ $current === $tab ? 'active' : 'border' }}">{{ ucfirst($tab) }}</a>
        </li>
    @endforeach
</ul>
