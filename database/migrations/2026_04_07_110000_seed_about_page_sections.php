<?php

use App\Models\MediaAsset;
use App\Models\SectionItem;
use App\Models\SiteSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $aboutImage = MediaAsset::query()->where('path', 'uploads/home/about/nosotros.png')->first();

        SiteSection::query()->updateOrCreate(
            ['page_key' => 'nosotros', 'section_key' => 'hero'],
            [
                'title' => 'NOSOTROS',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        );

        $intro = SiteSection::query()->updateOrCreate(
            ['page_key' => 'nosotros', 'section_key' => 'intro'],
            [
                'title' => 'Quienes somos',
                'description' => "En TAREA, nos dedicamos con pasión, profesionalismo e innovación a brindar soluciones integrales en el mercado electro energético y petrolero.\n\nSomos un fabricante líder de morsetería y herrajes para líneas de baja, media y alta tensión, diseñamos y producimos llaves seccionadoras y tableros eléctricos. Además, distribuimos una amplia gama de productos complementarios, incluyendo aisladores de porcelana y poliméricos, seccionadores, descargadores, cables de acero, preformados, productos para línea de red compacta y accesorios afines.\n\nNuestra amplia experiencia en el sector nos permite conocer a fondo las problemáticas y desafíos de nuestros clientes. Adaptamos nuestra propuesta comercial y de servicio a las necesidades específicas de cada uno, garantizando siempre la máxima calidad.\n\nDesde el diseño y desarrollo hasta la planificación de la producción, realizamos ensayos rigurosos en todas las etapas de nuestra cadena productiva, asegurando la trazabilidad completa de nuestros productos. Contamos con un laboratorio propio donde se llevan a cabo las pruebas químicas, mecánicas y eléctricas que garantizan que cada producto cumpla con los más altos estándares de calidad, y todos ellos pueden ser entregados con su certificado correspondiente.",
                'media_id' => $aboutImage?->id,
                'sort_order' => 'AA',
                'is_active' => true,
            ]
        );

        $missionVision = SiteSection::query()->updateOrCreate(
            ['page_key' => 'nosotros', 'section_key' => 'mission_vision'],
            [
                'sort_order' => 'AB',
                'is_active' => true,
            ]
        );

        $values = SiteSection::query()->updateOrCreate(
            ['page_key' => 'nosotros', 'section_key' => 'values'],
            [
                'title' => 'Nuestros valores',
                'sort_order' => 'AC',
                'is_active' => true,
            ]
        );

        $memberships = SiteSection::query()->updateOrCreate(
            ['page_key' => 'nosotros', 'section_key' => 'memberships'],
            [
                'title' => 'Pertenecemos a',
                'sort_order' => 'AD',
                'is_active' => true,
            ]
        );

        $this->syncItems($missionVision, [
            [
                'item_key' => 'mission',
                'title' => 'Misión',
                'description' => "Nuestra misión consiste en aplicar el talento colectivo y la pasión técnica en cada etapa del diseño, fabricación y comercialización de equipos destinados al transporte y distribución de energía eléctrica en baja, media y alta tensión. Nos enfocamos en la evolución constante de nuestros procesos industriales, impulsando una mejora diaria que garantice la máxima fiabilidad.\n\nBuscamos acompañar integralmente el éxito de nuestros clientes, asumiendo un rol de socios estratégicos en sus proyectos, brindando soluciones tecnológicas de vanguardia que aseguren la eficiencia energética y el desarrollo sostenible de la infraestructura eléctrica regional.",
                'sort_order' => 'A',
            ],
            [
                'item_key' => 'vision',
                'title' => 'Visión',
                'description' => "Nuestra visión es consolidarnos como referentes del sector eléctrico, comprendiendo profundamente las necesidades cambiantes de nuestros clientes y acompañándolos en sus desafíos más complejos. Nos proyectamos como una organización comprometida con la excelencia y la calidad total, contribuyendo de manera directa al éxito y la rentabilidad de sus negocios mediante la innovación constante.\n\nAspiramos a forjar vínculos de confianza duraderos, fundamentados en la solidez técnica y en una capacidad de respuesta ágil que nos permita liderar la transición hacia sistemas de distribución de energía más seguros, robustos y altamente eficientes.",
                'sort_order' => 'AA',
            ],
        ]);

        $this->syncItems($values, [
            ['item_key' => 'entrepreneurial-spirit', 'title' => "Espíritu\nEmprendedor", 'sort_order' => 'A'],
            ['item_key' => 'flexibility', 'title' => 'Flexibilidad', 'sort_order' => 'AA'],
            ['item_key' => 'commitment', 'title' => 'Compromiso', 'sort_order' => 'AB'],
            ['item_key' => 'teamwork', 'title' => "Trabajo en\nequipo", 'sort_order' => 'AC'],
            ['item_key' => 'human-value', 'title' => "Valor\nHumano", 'sort_order' => 'AD'],
        ]);

        $this->syncItems($memberships, [
            ['item_key' => 'cadieel', 'title' => 'CADIEEL', 'sort_order' => 'A'],
            ['item_key' => 'gapp', 'title' => 'GAPP', 'sort_order' => 'AA'],
        ]);

        unset($intro);
    }

    public function down(): void
    {
        $sections = SiteSection::query()->where('page_key', 'nosotros')->get();

        foreach ($sections as $section) {
            SectionItem::query()->where('site_section_id', $section->id)->delete();
            $section->delete();
        }
    }

    private function syncItems(SiteSection $section, array $items): void
    {
        $kept = [];

        foreach ($items as $item) {
            $model = SectionItem::query()->updateOrCreate(
                ['site_section_id' => $section->id, 'item_key' => $item['item_key']],
                [
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );

            $kept[] = $model->id;
        }

        SectionItem::query()
            ->where('site_section_id', $section->id)
            ->whereNotIn('id', $kept)
            ->delete();
    }
};
