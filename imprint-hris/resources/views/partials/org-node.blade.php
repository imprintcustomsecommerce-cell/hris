@php $kids = $children[$node->id] ?? collect(); @endphp
<li>
    <a href="/employees/{{ $node->id }}" class="org-card">
        @if($node->photo)
            <img src="{{ \App\Support\Blob::url($node->photo) }}" alt="" class="org-photo">
        @else
            <span class="org-photo org-initial">{{ strtoupper(substr($node->name ?? 'E', 0, 1)) }}</span>
        @endif
        <span class="org-name">{{ $node->name }}</span>
        <span class="org-pos">{{ $node->position }}</span>
        <span class="org-dept">{{ $node->department }}</span>
    </a>
    @if($kids->count())
        <ul>
            @foreach($kids as $child)
                @include('partials.org-node', ['node' => $child, 'children' => $children])
            @endforeach
        </ul>
    @endif
</li>
