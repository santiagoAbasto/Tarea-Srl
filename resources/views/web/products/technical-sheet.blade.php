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

.overview-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.overview-table td {
    vertical-align: top;
}

.overview__content {
    width: 58%;
    padding-right: 20px;
}

.overview__media {
    width: 42%;
}

.overview__media,
.catalog,
.footer-flow {
    page-break-inside: avoid;
}

.img-main {
    text-align: center;
    margin-bottom: 0;
    padding: 10px 10px 0;
    border: 1px solid #E5E7EB;
    background: #FFF;
}

.img-main img {
    max-width: 100%;
    max-height: 210px;
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

.gallery-shell {
    margin-top: 14px;
    page-break-inside: avoid;
}

.gallery {
    margin-top: 0;
}

.gallery-table {
    width: auto;
    border-collapse: separate;
    border-spacing: 0 6px;
    margin-left: 0;
}

.gallery-table td {
    padding: 0 6px 0 0;
    vertical-align: top;
}

.gallery-table td:last-child {
    padding-right: 0;
}

.gallery-thumb {
    width: 58px;
    height: 58px;
    border: 1px solid #DBDADA;
    background: #FFF;
    text-align: center;
    vertical-align: middle;
}

.gallery-thumb img {
    max-width: 50px;
    max-height: 50px;
    width: auto;
    height: auto;
    display: block;
    margin: 4px auto;
}

.catalog {
    margin-top: 18px;
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
    margin-top: 40px;
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
    $specLabelKey = function ($label) {
        $label = \Illuminate\Support\Str::ascii((string) $label);
        $label = str_replace(['ø', 'Ø'], 'o', $label);
        $label = mb_strtolower(trim($label));
        $label = preg_replace('/[^a-z0-9]+/u', ' ', $label);

        return trim(preg_replace('/\s+/u', ' ', $label));
    };
    $stripSpecGroupPrefix = function ($label) {
        $label = trim((string) $label);
        $label = preg_replace('/^\s*rango\s+/iu', '', $label);
        $label = preg_replace('/^\s*[øØo]?\s*c?\s*cable\s*/iu', '', $label);
        $labelKey = mb_strtolower(\Illuminate\Support\Str::ascii(trim($label)));

        if ($labelKey === 'max') {
            return 'máx';
        }

        if (in_array($labelKey, ['cu', 'cobre', 'al ac', 'al acsr', 'alac'], true)) {
            return 'Cu';
        }

        if (in_array($labelKey, ['ai', 'al', 'aluminio'], true)) {
            return 'Al';
        }

        return $label;
    };
    $isRangeColumn = fn ($column) => preg_match('/^rango\b/u', $specLabelKey($column?->label)) === 1;
    $isCableColumn = fn ($column) => preg_match('/^(?:o\s*c?\s*)?cable\b/u', $specLabelKey($column?->label)) === 1;
    $rangeStart = null;
    $cableStart = null;

    for ($index = 0; $index < $columns->count() - 1; $index++) {
        if ($rangeStart === null && $isRangeColumn($columns->get($index)) && $isRangeColumn($columns->get($index + 1))) {
            $rangeStart = $index;
        }

        if ($cableStart === null && $isCableColumn($columns->get($index)) && $isCableColumn($columns->get($index + 1))) {
            $cableStart = $index;
        }
    }

    $isGroupedSpecsHeader = $rangeStart !== null && $cableStart !== null && $rangeStart < $cableStart;
    $rangeUnit = $isGroupedSpecsHeader ? ($columns->get($rangeStart)?->unit ?: $columns->get($rangeStart + 1)?->unit) : null;
    $cableUnit = $isGroupedSpecsHeader ? ($columns->get($cableStart)?->unit ?: $columns->get($cableStart + 1)?->unit) : null;
    $groupedSubheaderIndexes = $isGroupedSpecsHeader
        ? [$rangeStart, $rangeStart + 1, $cableStart, $cableStart + 1]
        : [];
    $groupedTopHeaderCells = [];

    if ($isGroupedSpecsHeader) {
        for ($columnIndex = 0; $columnIndex < $columns->count(); $columnIndex++) {
            $column = $columns->get($columnIndex);

            if ($columnIndex === $rangeStart) {
                $groupedTopHeaderCells[] = [
                    'label' => 'RANGO',
                    'unit' => $rangeUnit,
                    'colspan' => 2,
                    'rowspan' => 1,
                ];
                $columnIndex++;
                continue;
            }

            if ($columnIndex === $cableStart) {
                $groupedTopHeaderCells[] = [
                    'label' => 'øC Cable',
                    'unit' => $cableUnit,
                    'colspan' => 2,
                    'rowspan' => 1,
                ];
                $columnIndex++;
                continue;
            }

            $groupedTopHeaderCells[] = [
                'label' => mb_strtoupper($column?->label ?? ''),
                'unit' => $column?->unit,
                'colspan' => 1,
                'rowspan' => 2,
            ];
        }
    }
    $rows = $specTable?->rows?->values() ?? collect();
    $rowValues = $rows->map(function ($row) use ($columns) {
        $valuesByColumn = $row->values->keyBy('product_spec_column_id');

        return $columns->map(fn ($column) => $valuesByColumn->get($column->id)?->value ?? '')->all();
    })->all();
    $mergeColumnKeys = ['pendulo'];
    $rowspans = [];
    $skipCells = [];

    foreach ($columns as $columnIndex => $column) {
        if (! in_array($specLabelKey($column->label), $mergeColumnKeys, true)) {
            continue;
        }

        for ($rowIndex = 0; $rowIndex < count($rowValues); $rowIndex++) {
            if (($skipCells[$rowIndex][$columnIndex] ?? false) === true) {
                continue;
            }

            $value = trim((string) ($rowValues[$rowIndex][$columnIndex] ?? ''));
            $span = 1;

            if ($value !== '') {
                for ($nextRowIndex = $rowIndex + 1; $nextRowIndex < count($rowValues); $nextRowIndex++) {
                    if (trim((string) ($rowValues[$nextRowIndex][$columnIndex] ?? '')) !== $value) {
                        break;
                    }

                    $span++;
                    $skipCells[$nextRowIndex][$columnIndex] = true;
                }
            }

            $rowspans[$rowIndex][$columnIndex] = $span;
        }
    }

    $galleryImages = $gallery
        ->filter(fn ($media) => filled($galleryDataUris[$media->id] ?? null))
        ->values();
    $galleryChunks = $galleryImages->chunk(5);
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

<table class="overview-table">
    <tr>
        <td class="overview__content">
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
        </td>
        <td class="overview__media">
            <div class="img-main">
                @if($mainImageDataUri)
                    <img src="{{ $mainImageDataUri }}" alt="{{ $productName }}">
                @endif
            </div>
        </td>
    </tr>
</table>

@if($galleryImages->count() > 1)
    <div class="gallery-shell">
        <div class="sec">Galería</div>
        <div class="gallery">
            <table class="gallery-table">
                @foreach($galleryChunks as $chunk)
                    <tr>
                        @foreach($chunk as $media)
                            <td>
                                <div class="gallery-thumb">
                                    <img src="{{ $galleryDataUris[$media->id] }}" alt="{{ $media->alt_text ?: $productName }}">
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endif

@if($specTable && $columns->isNotEmpty() && $specTable->rows->isNotEmpty())
    <div class="catalog">
        <div class="catalog__title">Tabla técnica</div>
        <table class="catalog__table">
            <thead>
                @if($isGroupedSpecsHeader)
                    <tr>
                        @foreach($groupedTopHeaderCells as $cell)
                            <th @if($cell['colspan'] > 1) colspan="{{ $cell['colspan'] }}" @endif @if($cell['rowspan'] > 1) rowspan="{{ $cell['rowspan'] }}" @endif>
                                <span>{{ $cell['label'] }}</span>
                                @if($cell['unit'])
                                    <small>[{{ $cell['unit'] }}]</small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($groupedSubheaderIndexes as $columnIndex)
                            <th>{{ $stripSpecGroupPrefix($columns->get($columnIndex)?->label) }}</th>
                        @endforeach
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
                @foreach($rows as $rowIndex => $row)
                    <tr>
                        @foreach($columns as $columnIndex => $column)
                            @if(! ($skipCells[$rowIndex][$columnIndex] ?? false))
                                <td @if(($rowspans[$rowIndex][$columnIndex] ?? 1) > 1) rowspan="{{ $rowspans[$rowIndex][$columnIndex] }}" @endif>
                                    {{ $rowValues[$rowIndex][$columnIndex] ?? '' }}
                                </td>
                            @endif
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
