@php
    $languages = config('languages.supported', []);
    $currentLocale = app()->getLocale();
    $flagIcons = [
        'en' => 'en.svg',
        'vi' => 'vi.svg',
        'fi' => 'fi.svg',
        'ge' => 'ge.svg',
        'po' => 'po.svg',
        'sw' => 'sw.svg',
    ];
    $currentLabel = $languages[$currentLocale] ?? strtoupper($currentLocale);
    $currentFlag = $flagIcons[$currentLocale] ?? null;
@endphp

<div class="language-switcher" x-data="{ open: false }">
    <button @click="open = !open" class="btn-language" type="button">
        @if ($currentFlag)
            <img src="{{ asset('langding/imgs/' . $currentFlag) }}" alt="{{ $currentLabel }}">
        @endif
        <span>{{ strtoupper($currentLocale) }}</span>
        <svg class="dropdown-icon" :class="{ 'rotate': open }" width="12" height="12"
            viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 9L1 4L11 4L6 9Z" fill="currentColor" />
        </svg>
    </button>

    <div x-show="open" @click.away="open = false" class="language-dropdown" x-transition
        style="display: none;">
        @foreach ($languages as $code => $label)
            @php
                $flag = $flagIcons[$code] ?? null;
                $isActive = $currentLocale === $code;
            @endphp
            <a href="{{ url('/language/' . $code) }}"
                class="language-option {{ $isActive ? 'active' : '' }}">
                @if ($flag)
                    <img src="{{ asset('langding/imgs/' . $flag) }}" alt="{{ $label }}">
                @endif
                <span>{{ $label }}</span>
                @if ($isActive)
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 12L2 8L3.5 6.5L6 9L12.5 2.5L14 4L6 12Z" fill="#4CAF50" />
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>
