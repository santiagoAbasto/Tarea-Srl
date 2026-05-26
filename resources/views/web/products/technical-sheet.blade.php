<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
@php
    $accentColor = $product->subfamily?->accent_color ?: $product->family?->accent_color ?: '#093E66';
    $accentHex = ltrim($accentColor, '#');
    $accentRgb = strlen($accentHex) === 6
        ? implode(', ', [hexdec(substr($accentHex, 0, 2)), hexdec(substr($accentHex, 2, 2)), hexdec(substr($accentHex, 4, 2))])
        : '9, 62, 102';
    $accentSoft = 'rgba('.$accentRgb.', 0.10)';
@endphp
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@page {
    margin: 0;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10.8px;
    color: #172033;
    background: #FFF;
}

.sheet {
    padding: 24px 36px 18px;
    background: #FFF;
}

.pagenum:before { content: counter(page); }

.hd {
    display: table;
    width: 100%;
    padding-bottom: 8px;
    margin-bottom: 9px;
    border-bottom: 1px solid #DDE4EC;
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

.doc-title {
    display: inline-block;
    padding: 7px 11px;
    color: #FFF;
    background: {{ $accentColor }};
    font-size: 15px;
    font-weight: bold;
    letter-spacing: 1.3px;
    text-transform: uppercase;
}

.doc-subtitle {
    margin-top: 5px;
    color: #6B7280;
    font-size: 7.8px;
    font-weight: bold;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.hero {
    display: table;
    width: 100%;
    margin-bottom: 7px;
    border-collapse: separate;
    border-spacing: 0;
}

.hero__l {
    display: table-cell;
    vertical-align: top;
    width: 56%;
    padding: 9px 16px 9px 0;
}

.hero__r {
    display: table-cell;
    vertical-align: top;
    width: 44%;
    padding: 8px 0 8px 16px;
    border-left: 3px solid {{ $accentColor }};
    text-align: left;
}

.hero__brand-label {
    font-size: 8.2px;
    font-weight: bold;
    color: {{ $accentColor }};
    letter-spacing: 1.8px;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.hero__code {
    font-size: 20px;
    font-weight: bold;
    color: {{ $accentColor }};
    letter-spacing: 0.2px;
    line-height: 1.12;
}

.hero__name {
    font-size: 9px;
    color: #5C6677;
    margin-top: 6px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
}

.hero__divider {
    border: none;
    border-top: 2px solid {{ $accentColor }};
    margin-bottom: 8px;
}

.hero-meta {
    font-size: 9px;
    text-align: left;
}

.hero-meta__item {
    padding: 0 0 4px;
    margin-bottom: 4px;
    border-bottom: 1px solid #E7ECF2;
}

.hero-meta__label {
    margin-bottom: 1px;
    color: {{ $accentColor }};
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 7.8px;
}

.hero-meta__value {
    color: #172033;
    line-height: 1.28;
}

.overview-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

.overview-table td {
    vertical-align: top;
}

.overview__content {
    width: 49%;
}

.overview__media {
    width: 49%;
}

.overview__gap {
    width: 2%;
}

.overview__media,
.map-image,
.catalog,
.footer-flow {
    page-break-inside: avoid;
}

.img-main {
    text-align: center;
    margin-bottom: 0;
    padding: 7px 8px;
    border: 1px solid #DDE4EC;
    border-top: 3px solid {{ $accentColor }};
    background: #FFF;
    height: 100px;
    line-height: 86px;
}

.img-main img {
    max-width: 100%;
    max-height: 80px;
    width: auto;
    height: auto;
    display: inline-block;
    vertical-align: middle;
    margin: 0 auto;
}

.sec {
    font-size: 8.2px;
    font-weight: bold;
    color: {{ $accentColor }};
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding-bottom: 3px;
    border-bottom: 1px solid #DDE4EC;
    margin-bottom: 6px;
    margin-top: 0;
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
    height: 100px;
    padding: 7px 9px;
    border: 1px solid #E5EAF0;
    border-top: 3px solid {{ $accentColor }};
    background: #FAFBFC;
    font-size: 9.2px;
    color: #344054;
    line-height: 1.35;
}

.map-image {
    width: auto;
    margin-top: 8px;
    padding: 8px 12px 10px;
    border: 1px solid #D7DEE6;
    border-left: 5px solid {{ $accentColor }};
    background: #F8FAFC;
    text-align: center;
}

.map-image__label {
    margin-bottom: 5px;
    color: {{ $accentColor }};
    font-size: 8.2px;
    font-weight: bold;
    letter-spacing: 1.6px;
    text-align: left;
    text-transform: uppercase;
}

.map-image img {
    display: block;
    width: auto;
    max-width: 100%;
    max-height: 332px;
    height: auto;
    margin: 0 auto;
}

.catalog {
    margin-top: 7px;
    width: 100%;
}

.catalog__table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7.6px;
}

.catalog__table th,
.catalog__table td {
    border: 1px solid #DDE4EC;
    padding: 3.5px 3px;
    text-align: center;
    vertical-align: middle;
}

.catalog__table thead {
    background: {{ $accentColor }};
}

.catalog__table th {
    color: #FFF;
    font-size: 7.6px;
    font-weight: bold;
    line-height: 1.15;
    text-transform: uppercase;
}

.catalog__table th small {
    display: block;
    margin-top: 1px;
    font-size: 6.6px;
    font-weight: 400;
    color: rgba(255, 255, 255, 0.82);
}

.catalog__table td {
    color: #263243;
    font-size: 7.7px;
    line-height: 1.2;
}

.catalog__table tbody tr:nth-child(even) {
    background: #F8FAFC;
}

.footer-flow {
    margin-top: 10px;
    border-top: 1px solid #DDE4EC;
    padding-top: 5px;
    font-size: 7px;
    color: #8A94A6;
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
@endphp

<div class="sheet">
<div class="hd">
    <div class="hd__l">
        @if($logoPath)
            <img src="{{ $logoPath }}" alt="Tarea" style="max-height:40px; max-width:170px;">
        @else
            <div style="font-size:22px; font-weight:900; color:#093E66; letter-spacing:-0.5px;">TAREA</div>
            <div style="font-size:6.5px; font-weight:bold; color:#9CA3AF; letter-spacing:3px; text-transform:uppercase; margin-top:2px;">Soluciones eléctricas e industriales</div>
        @endif
    </div>
    <div class="hd__r">
        <div class="doc-title">Ficha técnica</div>
        <div class="doc-subtitle">Especificación de producto</div>
    </div>
</div>

<div class="hero">
    <div class="hero__l">
        <div class="hero__brand-label">{{ $subfamilyName ?: ($familyName ?: 'Producto') }}</div>
        <div class="hero__code">{{ $productCode }}</div>
        @if($productName)
            <div class="hero__name">{{ mb_strtoupper($productName) }}</div>
        @endif
    </div>
    <div class="hero__r">
        <div class="hero__brand-label" style="color:{{ $accentColor }};">{{ $familyName ?: 'Producto' }}</div>
        <div class="hero-meta">
            @if($subfamilyName)
                <div class="hero-meta__item">
                    <div class="hero-meta__label">Subcategoría</div>
                    <div class="hero-meta__value">{{ $subfamilyName }}</div>
                </div>
            @endif
            @if($product->material)
                <div class="hero-meta__item">
                    <div class="hero-meta__label">Material</div>
                    <div class="hero-meta__value">{{ $product->material }}</div>
                </div>
            @endif
            @if($product->treatment)
                <div class="hero-meta__item">
                    <div class="hero-meta__label">Tratamiento</div>
                    <div class="hero-meta__value">{{ $product->treatment }}</div>
                </div>
            @endif
            @if($product->observations)
                <div class="hero-meta__item">
                    <div class="hero-meta__label">Observaciones</div>
                    <div class="hero-meta__value">{{ $product->observations }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
<hr class="hero__divider">

<table class="overview-table">
    <tr>
        <td class="overview__content">
            @if($product->description)
                <div class="sec">Descripción</div>
                <div class="desc">{!! nl2br(e($product->description)) !!}</div>
            @endif
        </td>
        <td class="overview__gap"></td>
        <td class="overview__media">
            <div class="sec">Producto</div>
            <div class="img-main">
                @if($mainImageDataUri)
                    <img src="{{ $mainImageDataUri }}" alt="{{ $productName }}">
                @endif
            </div>
        </td>
    </tr>
</table>

@if($mapImageDataUri)
    <div class="map-image">
        <div class="map-image__label">Plano técnico / dimensiones</div>
        <img src="{{ $mapImageDataUri }}" alt="{{ $productName }} plano técnico">
    </div>
@endif

@if($specTable && $columns->isNotEmpty() && $specTable->rows->isNotEmpty())
    <div class="catalog">
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
