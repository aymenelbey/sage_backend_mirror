<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Sage Engineer')
            <img src="{{ asset('Sage.png') }}" class="logo" alt="Sage Engineers Logo">
            @else
            {{ $slot }}
            @endif
        </a>
    </td>
</tr>