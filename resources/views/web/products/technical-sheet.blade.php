<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    margin: 0;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #1A181C;
    background: #FFF;
}

.sheet {
    padding: 56px 56px 48px 56px;
}

.pagenum:before { content: counter(page); }

.hd {
    display: table;
    width: 100%;
    padding-bottom: 10px;
    margin-bottom: 16px;
    border-bottom: 2px solid #093E66;
}

.hd__l {
    display: table-cell;
    vertical-align: middle;
    width: 55%;
}

.hd__r {
    display: table-cell;
    vertical-align: middle;
    width: 45%;
    text-align: right;
}

.hero {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}

.hero__l {
    display: table-cell;
    vertical-align: middle;
    width: 65%;
}

.hero__r {
    display: table-cell;
    vertical-align: middle;
    width: 35%;
    text-align: right;
}

.hero__brand-label {
    font-size: 7.5px;
    font-weight: bold;
    color: #9CA3AF;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.hero__code {
    font-size: 22px;
    font-weight: bold;
    color: #093E66;
    letter-spacing: 0.5px;
    line-height: 1;
}

.hero__name {
    font-size: 9px;
    color: #6B7280;
    margin-top: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hero__divider {
    border: none;
    border-top: 1px solid #E5E7EB;
    margin-bottom: 16px;
}

.body {
    display: table;
    width: 100%;
}

.body__left {
    display: table-cell;
    width: 36%;
    vertical-align: top;
    padding-right: 20px;
}

.body__right {
    display: table-cell;
    width: 64%;
    vertical-align: top;
}

.img-main {
    text-align: center;
    margin-bottom: 15px;
}

.img-main img {
    max-width: 100%;
    max-height: 220px;
    width: auto;
    height: auto;
    display: block;
    margin: 0 auto;
}

.img-extra {
    text-align: center;
    margin-bottom: 15px;
}

.img-extra img {
    max-width: 100%;
    max-height: 180px;
    width: auto;
    height: auto;
    display: block;
    margin: 0 auto;
}

.sec {
    font-size: 7px;
    font-weight: bold;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #E5E7EB;
    margin-bottom: 7px;
    margin-top: 16px;
}

.sec:first-child {
    margin-top: 0;
}

.dt {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5px;
}

.dt tr {
    border-bottom: 1px solid #F3F4F6;
}

.dt tr:last-child {
    border-bottom: none;
}

.dt td {
    padding: 5px 4px;
    vertical-align: top;
    line-height: 1.45;
}

.dt td:first-child {
    width: 34%;
    color: #9CA3AF;
    font-weight: bold;
    padding-right: 8px;
}

.dt td:last-child {
    color: #111827;
}

.desc {
    font-size: 8.5px;
    color: #374151;
    line-height: 1.7;
}

.gallery {
    margin-top: 16px;
}

.gallery img {
    width: 72px;
    height: 72px;
    object-fit: contain;
    border: 1px solid #DBDADA;
    margin-right: 8px;
    margin-bottom: 8px;
    display: inline-block;
}

.catalog {
    margin-top: 22px;
    page-break-inside: auto;
}

.catalog__title {
    font-size: 7px;
    font-weight: bold;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #E5E7EB;
    margin-bottom: 10px;
}

.catalog__table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7.8px;
}

.catalog__table th,
.catalog__table td {
    border: 1px solid #E5E7EB;
    padding: 6px 5px;
    text-align: center;
    vertical-align: middle;
}

.catalog__table thead {
    background: rgba(9, 62, 102, 0.08);
}

.catalog__table th {
    color: #111827;
    font-size: 8px;
    font-weight: bold;
    line-height: 1.25;
}

.catalog__table th small {
    display: block;
    margin-top: 2px;
    font-size: 7px;
    font-weight: 400;
    color: #6B7280;
}

.catalog__table td {
    color: #374151;
    font-size: 8px;
    line-height: 1.35;
}

.footer-flow {
    margin-top: 80px;
    border-top: 1px solid #E5E7EB;
    padding-top: 10px;
    font-size: 8px;
    color: #9CA3AF;
    text-align: center;
}
</style>
</head>
<body>

@php
    $generatedAt = now('America/Argentina/Buenos_Aires')->format('d/m/Y H:i');
    $logoPng = storage_path('app/public/uploads/branding/logosection2.png');
    $logoPath = file_exists($logoPng) ? $logoPng : null;
    $productCode = mb_strtoupper($product->name ?: ($product->sku ?: $product->slug));
    $productName = $product->name;
    $familyName = $product->family?->name;
    $subfamilyName = $product->subfamily?->name;
    $detailRows = collect([
        ['label' => 'Familia', 'value' => $familyName],
        ['label' => 'Subcategoría', 'value' => $subfamilyName],
        ['label' => 'Material', 'value' => $product->material],
        ['label' => 'Tratamiento', 'value' => $product->treatment],
        ['label' => 'Observaciones', 'value' => $product->observations],
    ])->filter(fn ($row) => filled($row['value']))->values();

    $columns = $specTable?->columns?->values() ?? collect();
    $isGroupedSpecsHeader =
        $columns->count() >= 11
        && $columns->get(0)?->label === 'CÓDIGO'
        && str_starts_with($columns->get(1)?->label ?? '', 'RANGO ')
        && str_starts_with($columns->get(2)?->label ?? '', 'RANGO ')
        && str_starts_with($columns->get(3)?->label ?? '', 'øCABLE ')
        && str_starts_with($columns->get(4)?->label ?? '', 'øCABLE ');

    $extraImages = $gallery
        ->filter(fn ($media) => !$product->mainMedia || $media->id !== $product->mainMedia->id)
        ->filter(fn ($media) => filled($galleryDataUris[$media->id] ?? null))
        ->values();
@endphp

<div class="sheet">
<div class="hd">
    <div class="hd__l">
        @if($logoPath)
            <img src="{{ $logoPath }}" alt="Tarea" style="max-height:48px; max-width:180px;">
        @else
            <div style="font-size:22px; font-weight:900; color:#093E66; letter-spacing:-0.5px;">TAREA</div>
            <div style="font-size:6.5px; font-weight:bold; color:#9CA3AF; letter-spacing:3px; text-transform:uppercase; margin-top:2px;">Soluciones eléctricas e industriales</div>
        @endif
    </div>
    <div class="hd__r">
        <div style="font-size:16px; font-weight:bold; color:#093E66; letter-spacing:1px; text-transform:uppercase;">Ficha técnica</div>
        <div style="font-size:7px; font-weight:bold; color:#9CA3AF; text-transform:uppercase; letter-spacing:2px; margin-top:3px;">Información de producto</div>
    </div>
</div>

<div class="hero">
    <div class="hero__l">
        <div class="hero__brand-label">{{ $familyName ?: 'Producto' }}</div>
        <div class="hero__code">{{ $productCode }}</div>
        @if($productName)
            <div class="hero__name">{{ mb_strtoupper($productName) }}</div>
        @endif
    </div>
    <div class="hero__r">
        @if($subfamilyName)
            <span style="font-size:11px; font-weight:bold; color:#6B7280; text-transform:uppercase; letter-spacing:1px;">{{ $subfamilyName }}</span>
        @endif
    </div>
</div>
<hr class="hero__divider">

<div class="body">
    <div class="body__left">
        <div class="img-main">
            @if($mainImageDataUri)
                <img src="{{ $mainImageDataUri }}" alt="{{ $productName }}">
            @endif
        </div>

        @if($extraImages->isNotEmpty())
            @foreach($extraImages as $media)
                <div class="img-extra">
                    <img src="{{ $galleryDataUris[$media->id] }}" alt="{{ $media->alt_text ?: $productName }}">
                </div>
            @endforeach
        @endif
    </div>

    <div class="body__right">
        @if($detailRows->isNotEmpty())
            <div class="sec">Especificaciones</div>
            <table class="dt">
                @foreach($detailRows as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if($product->description)
            <div class="sec">Descripción</div>
            <div class="desc">{!! nl2br(e($product->description)) !!}</div>
        @endif

        @if($gallery->count() > 1)
            <div class="sec">Galería</div>
            <div class="gallery">
                @foreach($gallery as $media)
                    @if(filled($galleryDataUris[$media->id] ?? null))
                        <img src="{{ $galleryDataUris[$media->id] }}" alt="{{ $media->alt_text ?: $productName }}">
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>

@if($specTable && $columns->isNotEmpty() && $specTable->rows->isNotEmpty())
    <div class="catalog">
        <div class="catalog__title">Tabla técnica</div>
        <table class="catalog__table">
            <thead>
                @if($isGroupedSpecsHeader)
                    <tr>
                        <th rowspan="2">{{ $columns[0]->label }}</th>
                        <th colspan="2">RANGO [{{ $columns[1]->unit }}]</th>
                        <th colspan="2">øCABLE [{{ $columns[3]->unit }}]</th>
                        @foreach($columns->slice(5) as $column)
                            <th rowspan="2">
                                <span>{{ $column->label }}</span>
                                @if($column->unit)
                                    <small>[{{ $column->unit }}]</small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        <th>{{ str_replace('RANGO ', '', $columns[1]->label) }}</th>
                        <th>{{ str_replace('RANGO ', '', $columns[2]->label) }}</th>
                        <th>{{ str_replace('øCABLE ', '', $columns[3]->label) }}</th>
                        <th>{{ str_replace('øCABLE ', '', $columns[4]->label) }}</th>
                    </tr>
                @else
                    <tr>
                        @foreach($columns as $column)
                            <th>
                                <span>{{ $column->label }}</span>
                                @if($column->unit)
                                    <small>[{{ $column->unit }}]</small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                @endif
            </thead>
            <tbody>
                @foreach($specTable->rows as $row)
                    @php
                        $valuesByColumn = $row->values->keyBy('product_spec_column_id');
                    @endphp
                    <tr>
                        @foreach($columns as $column)
                            <td>{{ $valuesByColumn->get($column->id)?->value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="footer-flow">
    <strong style="color:#6B7280;">Tarea S.R.L.</strong> · Generado el {{ $generatedAt }}
</div>

</div>

</body>
</html>
