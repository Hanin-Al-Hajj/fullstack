@php
    $s = $seo ?? [];
       $siteName = config('store.name', config('app.name'));
    $title = isset($s['title']) ? $s['title'].' | '.$siteName : $siteName;
    $desc = \Illuminate\Support\Str::limit(strip_tags($s['description'] ?? config('store.description', '')), 160);
    $image = $s['image'] ?? null;
    $url = $s['url'] ?? url()->current();
    $type = $s['type'] ?? 'website';
@endphp
<title>{{ $title }}</title>
@if($desc)<meta name="description" content="{{ $desc }}">@endif
<link rel="canonical" href="{{ $url }}">
@if(!empty($s['noindex']))<meta name="robots" content="noindex,follow">@endif
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
@if($desc)<meta property="og:description" content="{{ $desc }}">@endif
<meta property="og:url" content="{{ $url }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US' }}">
@if($image)<meta property="og:image" content="{{ $image }}">@endif
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
@if(!empty($s['jsonld']))
<script type="application/ld+json">{!! json_encode($s['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif