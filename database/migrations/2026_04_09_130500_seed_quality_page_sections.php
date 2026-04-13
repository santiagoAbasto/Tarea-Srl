<?php

use App\Models\SectionFieldValue;
use App\Models\SiteSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $hero = SiteSection::query()->updateOrCreate(
            ['page_key' => 'calidad', 'section_key' => 'quality_banner'],
            [
                'title' => 'CALIDAD',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        );

        $policy = SiteSection::query()->updateOrCreate(
            ['page_key' => 'calidad', 'section_key' => 'quality_policy'],
            [
                'title' => 'Políticas de calidad',
                'description' => "En Tarea S.R.L., nuestra identidad corporativa se fundamenta en la búsqueda incansable de la excelencia operativa.\n\nEste camino está respaldado y validado internacionalmente por nuestras certificaciones ISO 9001:2015 (Sistema de Gestión de Calidad) e ISO 45001:2018 (Sistema de Gestión de Seguridad y Salud en el Trabajo).\n\nEntendemos que la calidad no es un destino estático, sino un proceso dinámico de evolución constante. Estas normas internacionales nos proporcionan el marco estructural necesario para estandarizar procesos, mitigar riesgos y potenciar la eficiencia en cada una de nuestras áreas.\n\nNo se trata solo de cumplir con requisitos técnicos, sino de honrar un compromiso ético y profesional con nuestros clientes, proveedores y, fundamentalmente, con nuestro capital humano.\n\nEste enfoque integral nos permite consolidar a Tarea S.R.L. como un referente de confianza, donde la calidad de servicio y la seguridad de las personas avanzan siempre en una misma dirección.",
                'sort_order' => 'B',
                'is_active' => true,
            ]
        );

        $logos = SiteSection::query()->updateOrCreate(
            ['page_key' => 'calidad', 'section_key' => 'quality_logos'],
            [
                'title' => 'Logos de calidad',
                'sort_order' => 'C',
                'is_active' => true,
            ]
        );

        SectionFieldValue::query()->updateOrCreate(
            ['site_section_id' => $hero->id, 'field_key' => 'page_slug'],
            ['field_value' => 'calidad', 'sort_order' => 'A']
        );

        SectionFieldValue::query()->updateOrCreate(
            ['site_section_id' => $policy->id, 'field_key' => 'layout'],
            ['field_value' => 'policy-with-media', 'sort_order' => 'A']
        );

        SectionFieldValue::query()->updateOrCreate(
            ['site_section_id' => $logos->id, 'field_key' => 'layout'],
            ['field_value' => 'logo-grid', 'sort_order' => 'A']
        );
    }

    public function down(): void
    {
        SiteSection::query()
            ->where('page_key', 'calidad')
            ->whereIn('section_key', ['quality_banner', 'quality_policy', 'quality_logos'])
            ->delete();
    }
};
