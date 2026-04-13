<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\FooterSetting;
use App\Models\HomeHeroSlide;
use App\Models\MediaAsset;
use App\Models\Partner;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Models\ProductSubfamily;
use App\Models\SectionItem;
use App\Models\SiteSection;
use App\Models\SocialLink;
use App\Support\SortOrder;
use Illuminate\Database\Seeder;

class HomeSeeder extends Seeder
{
    public function run(): void
    {
        $media = $this->seedMedia();
        $this->seedHomeSections($media);
        $this->seedHeroSlides($media);
        $families = $this->seedCatalog($media);
        $this->seedPartners($media);
        $this->seedNews($media);
        $this->seedClients($media);
        $this->seedFooter($media);
    }

    protected function seedMedia(): array
    {
        $items = [
            'home_hero_video' => ['type' => 'video', 'path' => 'uploads/home/hero/tarea-home-hero.mp4', 'title' => 'Home hero video'],
            'about_image' => ['type' => 'image', 'path' => 'uploads/home/about/quienes-somos.jpg', 'title' => 'Quienes somos'],
            'subfamily_chain' => ['type' => 'image', 'path' => 'uploads/products/subfamilies/elementos-cadena.jpg', 'title' => 'Elementos de cadena'],
            'subfamily_pat' => ['type' => 'image', 'path' => 'uploads/products/subfamilies/hilo-guardia-pat.jpg', 'title' => 'Hilo guardia PAT'],
            'subfamily_mounts' => ['type' => 'image', 'path' => 'uploads/products/subfamilies/montajes-aisladores.jpg', 'title' => 'Montajes de aisladores'],
            'product_mr0' => ['type' => 'image', 'path' => 'uploads/products/mr-0.png', 'title' => 'Morsa de retención MR-0'],
            'product_pkr20' => ['type' => 'image', 'path' => 'uploads/products/pkr20.png', 'title' => 'Ménsula de retención PKR20'],
            'product_ha' => ['type' => 'image', 'path' => 'uploads/products/h-a.png', 'title' => 'Muerto de anclaje H°A°'],
            'product_brazo_l' => ['type' => 'image', 'path' => 'uploads/products/brazo-l-5kv-35kv.png', 'title' => 'Brazo L 5 KV / 35 KV'],
            'product_pernos' => ['type' => 'image', 'path' => 'uploads/products/pernos-mn-41c-41r.png', 'title' => 'Pernos MN 41C / MN 41R'],
            'product_antibalanceo' => ['type' => 'image', 'path' => 'uploads/products/soporte-antibalanceo.png', 'title' => 'Soporte para antibalanceo'],
            'product_rg3' => ['type' => 'image', 'path' => 'uploads/products/rg-3.png', 'title' => 'Morsa de retención RG-3'],
            'product_morseto' => ['type' => 'image', 'path' => 'uploads/products/morseto-peine.png', 'title' => 'Morseto peine'],
            'post_1' => ['type' => 'image', 'path' => 'uploads/posts/nos-movemos-por-vos.jpg', 'title' => 'Nos movemos por vos'],
            'post_2' => ['type' => 'image', 'path' => 'uploads/posts/reforzando-alianzas-eden.jpg', 'title' => 'Reforzando alianzas'],
            'post_3' => ['type' => 'image', 'path' => 'uploads/posts/capacitacion-iso-45001.jpg', 'title' => 'Capacitación ISO 45001'],
            'partner_maurizio' => ['type' => 'image', 'path' => 'uploads/partners/maurizio.png', 'title' => 'Maurizio'],
            'partner_cst' => ['type' => 'image', 'path' => 'uploads/partners/cst.png', 'title' => 'CST'],
            'partner_entec' => ['type' => 'image', 'path' => 'uploads/partners/entec.png', 'title' => 'ENTEC'],
            'partner_sao_jose' => ['type' => 'image', 'path' => 'uploads/partners/sao-jose.png', 'title' => 'São José'],
            'client_epe' => ['type' => 'image', 'path' => 'uploads/clients/epe.png', 'title' => 'EPE'],
            'client_energia_san_juan' => ['type' => 'image', 'path' => 'uploads/clients/energia-san-juan.png', 'title' => 'Energía San Juan'],
            'client_transener' => ['type' => 'image', 'path' => 'uploads/clients/transener.png', 'title' => 'Transener'],
            'client_edea' => ['type' => 'image', 'path' => 'uploads/clients/edea.png', 'title' => 'Edea'],
            'client_eden' => ['type' => 'image', 'path' => 'uploads/clients/eden.png', 'title' => 'EDEN'],
            'client_edenor' => ['type' => 'image', 'path' => 'uploads/clients/edenor.png', 'title' => 'Edenor'],
            'footer_logo' => ['type' => 'image', 'path' => 'uploads/branding/tarea-logo-footer.png', 'title' => 'Tarea Footer'],
        ];

        $media = [];

        foreach ($items as $key => $item) {
            $media[$key] = MediaAsset::query()->updateOrCreate(
                ['path' => $item['path']],
                array_merge([
                    'disk' => 'public',
                    'alt_text' => $item['title'],
                ], $item)
            );
        }

        return $media;
    }

    protected function seedHomeSections(array $media): void
    {
        SiteSection::query()->updateOrCreate(
            ['page_key' => 'home', 'section_key' => 'representantes'],
            [
                'title' => 'Representantes exclusivos',
                'sort_order' => SortOrder::fromPosition(1),
                'is_active' => true,
            ]
        );

        SiteSection::query()->updateOrCreate(
            ['page_key' => 'home', 'section_key' => 'productos_home'],
            [
                'title' => 'Productos',
                'button_text' => 'Ver todos',
                'button_url' => '/productos',
                'sort_order' => SortOrder::fromPosition(2),
                'is_active' => true,
            ]
        );

        SiteSection::query()->updateOrCreate(
            ['page_key' => 'home', 'section_key' => 'about_preview'],
            [
                'title' => 'Quienes somos',
                'description' => "En TAREA, nos dedicamos con pasión, profesionalismo e innovación a brindar soluciones integrales en el mercado electro energético y petrolero.\n\nSomos un fabricante líder de morsetería y herrajes para líneas de baja, media y alta tensión, diseñamos y producimos llaves seccionadoras y tableros eléctricos. Además, distribuimos una amplia gama de productos complementarios, incluyendo aisladores de porcelana y poliméricos, seccionadores, descargadores, cables de acero, prelacrionados, con constante innovación.",
                'media_id' => $media['about_image']->id,
                'button_text' => 'Mas info',
                'button_url' => '/nosotros',
                'sort_order' => SortOrder::fromPosition(3),
                'is_active' => true,
            ]
        );

        SiteSection::query()->updateOrCreate(
            ['page_key' => 'home', 'section_key' => 'featured_products'],
            [
                'title' => 'Productos destacados',
                'sort_order' => SortOrder::fromPosition(4),
                'is_active' => true,
            ]
        );

        SiteSection::query()->updateOrCreate(
            ['page_key' => 'home', 'section_key' => 'latest_news'],
            [
                'title' => 'Enterate de nuestras últimas novedades',
                'button_text' => 'Ver todas',
                'button_url' => '/novedades',
                'sort_order' => SortOrder::fromPosition(5),
                'is_active' => true,
            ]
        );

        SiteSection::query()->updateOrCreate(
            ['page_key' => 'home', 'section_key' => 'trusted_clients'],
            [
                'title' => 'Confían en nosotros',
                'button_text' => 'Ver todas',
                'button_url' => '/clientes',
                'sort_order' => SortOrder::fromPosition(6),
                'is_active' => true,
            ]
        );
    }

    protected function seedHeroSlides(array $media): void
    {
        HomeHeroSlide::query()->updateOrCreate(
            ['sort_order' => SortOrder::fromPosition(1)],
            [
                'title' => 'INNOVACIÓN Y CRECIMIENTO',
                'subtitle' => null,
                'description' => 'Diseñamos sistemas, procesos y productos para generar soluciones innovadoras que respondan a las necesidades de nuestros clientes.',
                'button_text' => 'Más información',
                'button_url' => '/nosotros',
                'media_type' => 'video',
                'desktop_media_id' => $media['home_hero_video']->id,
                'mobile_media_id' => $media['home_hero_video']->id,
                'alt_text' => 'Video principal de Tarea SRL',
                'sort_order' => SortOrder::fromPosition(1),
                'autoplay_override_seconds' => null,
                'is_active' => true,
            ]
        );
    }

    protected function seedCatalog(array $media): array
    {
        $family = ProductFamily::query()->updateOrCreate(
            ['slug' => 'herrajes-y-morseteria'],
            [
                'name' => 'Herrajes y morsetería',
                'description' => 'Familia principal de herrajes, morsetería y componentes complementarios.',
                'cover_media_id' => $media['product_mr0']->id,
                'accent_color' => '#173F73',
                'sort_order' => SortOrder::fromPosition(1),
                'is_active' => true,
                'show_on_products_page' => true,
                'show_on_home' => true,
            ]
        );

        $chain = ProductSubfamily::query()->updateOrCreate(
            ['product_family_id' => $family->id, 'slug' => 'elementos-de-cadena'],
            [
                'name' => 'Elementos de cadena',
                'short_description' => 'Elementos de cadena',
                'description' => 'Subfamilia destacada para home y catálogo.',
                'cover_media_id' => $media['subfamily_chain']->id,
                'accent_color' => '#8ED9E8',
                'sort_order' => SortOrder::fromPosition(1),
                'is_active' => true,
                'show_on_home' => true,
                'show_on_family_page' => true,
            ]
        );

        $pat = ProductSubfamily::query()->updateOrCreate(
            ['product_family_id' => $family->id, 'slug' => 'componentes-hilo-guardia-pat'],
            [
                'name' => 'Componentes de hilo de guardia / PAT',
                'short_description' => 'Componentes de hilo de guardia / PAT',
                'description' => 'Subfamilia destacada para home y catálogo.',
                'cover_media_id' => $media['subfamily_pat']->id,
                'accent_color' => '#B06AC5',
                'sort_order' => SortOrder::fromPosition(2),
                'is_active' => true,
                'show_on_home' => true,
                'show_on_family_page' => true,
            ]
        );

        $mounts = ProductSubfamily::query()->updateOrCreate(
            ['product_family_id' => $family->id, 'slug' => 'accesorios-montajes-aisladores'],
            [
                'name' => 'Accesorios para montajes de aisladores',
                'short_description' => 'Accesorios para montajes de aisladores',
                'description' => 'Subfamilia destacada para home y catálogo.',
                'cover_media_id' => $media['subfamily_mounts']->id,
                'accent_color' => '#EF6364',
                'sort_order' => SortOrder::fromPosition(3),
                'is_active' => true,
                'show_on_home' => true,
                'show_on_family_page' => true,
            ]
        );

        ProductSubfamily::query()->updateOrCreate(
            ['product_family_id' => $family->id, 'slug' => 'preensamblado'],
            ['name' => 'Preensamblado', 'accent_color' => '#69C4BE', 'sort_order' => SortOrder::fromPosition(4), 'is_active' => true, 'show_on_home' => false, 'show_on_family_page' => true]
        );
        ProductSubfamily::query()->updateOrCreate(
            ['product_family_id' => $family->id, 'slug' => 'rienda'],
            ['name' => 'Rienda', 'accent_color' => '#E67B39', 'sort_order' => SortOrder::fromPosition(5), 'is_active' => true, 'show_on_home' => false, 'show_on_family_page' => true]
        );
        ProductSubfamily::query()->updateOrCreate(
            ['product_family_id' => $family->id, 'slug' => 'red-compacta'],
            ['name' => 'Red compacta', 'accent_color' => '#A1CD62', 'sort_order' => SortOrder::fromPosition(6), 'is_active' => true, 'show_on_home' => false, 'show_on_family_page' => true]
        );
        ProductSubfamily::query()->updateOrCreate(
            ['product_family_id' => $family->id, 'slug' => 'montajes-de-aisladores'],
            ['name' => 'Montajes de aisladores', 'accent_color' => '#F36B5D', 'sort_order' => SortOrder::fromPosition(7), 'is_active' => true, 'show_on_home' => false, 'show_on_family_page' => true]
        );

        $products = [
            ['slug' => 'morsa-retencion-mr-0', 'name' => 'Morsa de retención MR-0', 'media' => 'product_mr0', 'sub' => $chain, 'featured' => true, 'material' => 'Cuerpo y apretador: fundición nodular según IRAM 700.', 'treatment' => 'Cincado en caliente según IRAM 20022.', 'observations' => 'Chaveta tipo R / Material latón.'],
            ['slug' => 'mensula-retencion-pkr20', 'name' => 'Ménsula de retención PKR20', 'media' => 'product_pkr20', 'sub' => ProductSubfamily::query()->where('slug', 'preensamblado')->first(), 'featured' => true],
            ['slug' => 'muerto-anclaje-ha', 'name' => 'Muerto de anclaje H°A°', 'media' => 'product_ha', 'sub' => ProductSubfamily::query()->where('slug', 'rienda')->first(), 'featured' => true],
            ['slug' => 'brazo-l-5kv-35kv', 'name' => 'Brazo L 5 KV / 35 KV', 'media' => 'product_brazo_l', 'sub' => ProductSubfamily::query()->where('slug', 'red-compacta')->first(), 'featured' => true],
            ['slug' => 'pernos-mn-41c-mn-41r', 'name' => 'Pernos MN 41C / MN 41R', 'media' => 'product_pernos', 'sub' => ProductSubfamily::query()->where('slug', 'montajes-de-aisladores')->first(), 'featured' => true],
            ['slug' => 'soporte-antibalanceo', 'name' => 'Soporte para antibalanceo', 'media' => 'product_antibalanceo', 'sub' => ProductSubfamily::query()->where('slug', 'red-compacta')->first(), 'featured' => true],
            ['slug' => 'morsa-retencion-rg-3-anillo', 'name' => 'Morsa de retención RG-3 con anillo', 'media' => 'product_rg3', 'sub' => $pat, 'featured' => true],
            ['slug' => 'morseto-peine', 'name' => 'Morseto peine', 'media' => 'product_morseto', 'sub' => $chain, 'featured' => true],
        ];

        foreach ($products as $index => $productData) {
            Product::query()->updateOrCreate(
                ['slug' => $productData['slug']],
                [
                    'product_family_id' => $family->id,
                    'product_subfamily_id' => $productData['sub']?->id,
                    'name' => $productData['name'],
                    'sku' => strtoupper(str_replace('-', '', $productData['slug'])),
                    'short_description' => $productData['name'],
                    'description' => $productData['name'].' para soluciones eléctricas de media y alta tensión.',
                    'material' => $productData['material'] ?? null,
                    'treatment' => $productData['treatment'] ?? null,
                    'observations' => $productData['observations'] ?? null,
                    'main_media_id' => $media[$productData['media']]->id,
                    'sort_order' => SortOrder::fromPosition($index + 1),
                    'is_active' => true,
                    'is_featured_home' => $productData['featured'],
                    'is_featured_family' => $productData['featured'],
                ]
            );
        }

        return [$family];
    }

    protected function seedPartners(array $media): void
    {
        $partners = [
            ['slug' => 'maurizio', 'name' => 'Maurizio', 'media' => 'partner_maurizio'],
            ['slug' => 'cst', 'name' => 'CST', 'media' => 'partner_cst'],
            ['slug' => 'entec', 'name' => 'ENTEC', 'media' => 'partner_entec'],
            ['slug' => 'sao-jose', 'name' => 'São José', 'media' => 'partner_sao_jose'],
        ];

        foreach ($partners as $index => $partner) {
            Partner::query()->updateOrCreate(
                ['slug' => $partner['slug']],
                [
                    'name' => $partner['name'],
                    'logo_media_id' => $media[$partner['media']]->id,
                    'partner_type' => 'representada',
                    'sort_order' => SortOrder::fromPosition($index + 1),
                    'is_active' => true,
                    'show_on_home' => true,
                    'show_on_page' => true,
                ]
            );
        }
    }

    protected function seedNews(array $media): void
    {
        $empresa = PostCategory::query()->updateOrCreate(
            ['slug' => 'empresa'],
            ['name' => 'Empresa', 'color' => '#1D5A92', 'sort_order' => SortOrder::fromPosition(1), 'is_active' => true]
        );

        $capacitaciones = PostCategory::query()->updateOrCreate(
            ['slug' => 'capacitaciones'],
            ['name' => 'Capacitaciones', 'color' => '#1D5A92', 'sort_order' => SortOrder::fromPosition(2), 'is_active' => true]
        );

        $posts = [
            ['slug' => 'nos-movemos-por-vos', 'title' => '¡Nos movemos por vos!', 'category' => $empresa, 'media' => 'post_1', 'excerpt' => 'Estamos dedicados a garantizar la eficiencia en la coordinación de operaciones.', 'content' => 'Contenido inicial de novedad institucional.', 'date' => now()->subDays(10)],
            ['slug' => 'reforzando-alianzas-con-el-equipo-de-eden', 'title' => 'Reforzando Alianzas con el equipo de Eden', 'category' => $empresa, 'media' => 'post_2', 'excerpt' => 'El día de ayer, le dimos la bienvenida al equipo de compras de EDEN S.A.', 'content' => 'Contenido inicial sobre alianzas con Eden.', 'date' => now()->subDays(7)],
            ['slug' => 'capacitacion-iso-45001-sgsst', 'title' => 'Capacitación ISO 45001 de SGSST', 'category' => $capacitaciones, 'media' => 'post_3', 'excerpt' => 'En Tarea SRL la seguridad es nuestra prioridad número uno.', 'content' => 'Contenido inicial de capacitación ISO 45001.', 'date' => now()->subDays(4)],
        ];

        foreach ($posts as $index => $post) {
            Post::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'post_category_id' => $post['category']->id,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'cover_media_id' => $media[$post['media']]->id,
                    'published_at' => $post['date'],
                    'sort_order' => SortOrder::fromPosition($index + 1),
                    'is_active' => true,
                    'show_on_home' => true,
                    'is_featured' => true,
                ]
            );
        }
    }

    protected function seedClients(array $media): void
    {
        $category = ClientCategory::query()->updateOrCreate(
            ['slug' => 'energia'],
            ['name' => 'Energía', 'sort_order' => SortOrder::fromPosition(1), 'is_active' => true]
        );

        $clients = [
            ['slug' => 'epe', 'name' => 'EPE', 'media' => 'client_epe'],
            ['slug' => 'energia-san-juan', 'name' => 'Energía San Juan', 'media' => 'client_energia_san_juan'],
            ['slug' => 'transener', 'name' => 'Transener', 'media' => 'client_transener'],
            ['slug' => 'edea', 'name' => 'Edea', 'media' => 'client_edea'],
            ['slug' => 'eden', 'name' => 'EDEN', 'media' => 'client_eden'],
            ['slug' => 'edenor', 'name' => 'Edenor', 'media' => 'client_edenor'],
        ];

        foreach ($clients as $index => $client) {
            Client::query()->updateOrCreate(
                ['slug' => $client['slug']],
                [
                    'client_category_id' => $category->id,
                    'name' => $client['name'],
                    'logo_media_id' => $media[$client['media']]->id,
                    'sort_order' => SortOrder::fromPosition($index + 1),
                    'is_active' => true,
                    'show_on_home' => true,
                ]
            );
        }
    }

    protected function seedFooter(array $media): void
    {
        FooterSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'logo_media_id' => $media['footer_logo']->id,
                'brand_name' => 'TAREA',
                'newsletter_title' => 'Suscribite al newsletter',
                'newsletter_placeholder' => 'Email',
                'contact_title' => 'Contacto',
                'contact_address' => 'Avenida Antártida Argentina 2940 Llavalol, Buenos Aires, Argentina.',
                'phone_primary' => '+54 11 4298-0196',
                'phone_secondary' => '+54 9 11 3441-1550',
                'email_primary' => 'ventas@tareasrl.com.ar',
                'whatsapp_url' => 'https://wa.me/5491134411550',
                'copyright_text' => '© Copyright 2026 Tarea S.R.L. Todos los derechos reservados',
            ]
        );

        $social = [
            ['platform' => 'instagram', 'url' => 'https://instagram.com/tareasrl'],
            ['platform' => 'facebook', 'url' => 'https://facebook.com/tareasrl'],
            ['platform' => 'youtube', 'url' => 'https://youtube.com/@tareasrl'],
            ['platform' => 'linkedin', 'url' => 'https://linkedin.com/company/tareasrl'],
        ];

        foreach ($social as $index => $item) {
            SocialLink::query()->updateOrCreate(
                ['platform' => $item['platform'], 'location' => 'footer'],
                [
                    'label' => ucfirst($item['platform']),
                    'url' => $item['url'],
                    'icon' => $item['platform'],
                    'sort_order' => SortOrder::fromPosition($index + 1),
                    'is_active' => true,
                ]
            );
        }
    }
}
