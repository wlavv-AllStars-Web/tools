@php($icon = $icon ?? 'grid')
<svg class="nt-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($icon)
        @case('calculator')<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8M8 11h2m4 0h2m-8 4h2m4 0h2m-8 3h2m4 0h2"/>@break
        @case('cart')<path d="M3 4h2l2.1 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L20 9H7"/><circle cx="10" cy="20" r="1"/><circle cx="17" cy="20" r="1"/>@break
        @case('box')<path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="M3 7v10l9 4 9-4V7M12 11v10"/>@break
        @case('sales')<path d="M4 19V5M4 19h16"/><path d="m7 15 4-4 3 2 5-6"/><path d="M16 7h3v3"/>@break
        @case('support')<path d="M4 13v-1a8 8 0 0 1 16 0v1"/><path d="M4 13h3v5H5a1 1 0 0 1-1-1v-4Zm16 0h-3v5h2a1 1 0 0 0 1-1v-4Z"/><path d="M17 18c0 2-2 3-5 3"/>@break
        @case('studio')<path d="m12 3 .8 3.2L16 7l-3.2.8L12 11l-.8-3.2L8 7l3.2-.8L12 3Z"/><path d="m18 13 .5 2 2 .5-2 .5-.5 2-.5-2-2-.5 2-.5.5-2ZM6 14l.6 2.4L9 17l-2.4.6L6 20l-.6-2.4L3 17l2.4-.6L6 14Z"/>@break
        @case('design')<path d="m4 20 4.3-1.1L19 8.2a2.1 2.1 0 0 0-3-3L5.3 15.9 4 20Z"/><path d="m13.5 7.7 2.8 2.8M7 17l2 2"/>@break
        @case('code')<path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 5l-4 14"/>@break
        @case('warehouse')<path d="m3 10 9-6 9 6v10H3V10Z"/><path d="M7 20v-6h10v6M7 10h.01M12 10h.01M17 10h.01"/>@break
        @case('truck')<path d="M3 5h11v11H3zM14 9h4l3 3v4h-7V9Z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>@break
        @case('map')<path d="M12 21s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z"/><circle cx="12" cy="9" r="2.2"/>@break
        @case('folder')<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/><path d="M3 10h18"/>@break
        @default<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 12h8M12 8v8"/>
    @endswitch
</svg>
