<?php

namespace App\Support;

use Illuminate\Http\Request;

class SeoPageResolver
{
    protected const PAGES = [
        'home' => [
            'label' => 'Inicio',
            'recommended_title' => 'Página principal con marca, propuesta de valor y productos destacados.',
            'recommended_description' => 'Explicá en una frase qué hace la empresa y por qué elegirla. Pensalo como la vidriera principal de Google.',
            'recommended_keywords' => 'marca, rubro principal, soluciones, productos, industria',
        ],
        'about' => [
            'label' => 'Nosotros',
            'recommended_title' => 'Historia, misión, visión y trayectoria de la empresa.',
            'recommended_description' => 'Contá quiénes son, qué experiencia tienen y qué valores respaldan su trabajo.',
            'recommended_keywords' => 'empresa, trayectoria, misión, visión, valores, industria',
        ],
        'products' => [
            'label' => 'Productos',
            'recommended_title' => 'Catálogo de productos, familias y soluciones disponibles.',
            'recommended_description' => 'Resumí qué tipo de productos ofrece la empresa y qué necesidad resuelven.',
            'recommended_keywords' => 'productos, catálogo, componentes, soluciones, industria',
        ],
        'products_all' => [
            'label' => 'Todos los productos',
            'recommended_title' => 'Listado completo de productos disponibles.',
            'recommended_description' => 'Pensalo para búsquedas más amplias donde el usuario todavía no eligió una familia concreta.',
            'recommended_keywords' => 'productos, catálogo completo, soluciones, industria',
        ],
        'products_family' => [
            'label' => 'Familias y subfamilias de productos',
            'recommended_title' => 'Página de familia o subfamilia de productos.',
            'recommended_description' => 'Conviene orientar este SEO al tipo de producto o línea que más te interesa posicionar.',
            'recommended_keywords' => 'familias, subfamilias, productos, línea, catálogo',
        ],
        'products_detail' => [
            'label' => 'Detalle de producto',
            'recommended_title' => 'Ficha comercial del producto puntual.',
            'recommended_description' => 'Si no se define manualmente desde el contenido, esta configuración se usa como respaldo en el detalle del producto.',
            'recommended_keywords' => 'producto, ficha, detalle, especificaciones, industria',
        ],
        'products_sheet' => [
            'label' => 'Ficha técnica de producto',
            'recommended_title' => 'Ficha técnica descargable o visualizable.',
            'recommended_description' => 'Sirve como respaldo SEO para la vista de ficha técnica del producto.',
            'recommended_keywords' => 'ficha técnica, especificaciones, producto, industria',
        ],
        'catalog' => [
            'label' => 'Catálogo',
            'recommended_title' => 'Descarga o solicitud de catálogo comercial.',
            'recommended_description' => 'Indicá que desde esta página se puede pedir o descargar el catálogo con información comercial.',
            'recommended_keywords' => 'catálogo, descarga, solicitud, productos, comercial',
        ],
        'quality' => [
            'label' => 'Calidad',
            'recommended_title' => 'Políticas de calidad, certificaciones y procesos.',
            'recommended_description' => 'Explicá el enfoque de calidad de la empresa y los respaldos o certificaciones que posee.',
            'recommended_keywords' => 'calidad, certificación, procesos, mejora continua, industria',
        ],
        'clients' => [
            'label' => 'Clientes',
            'recommended_title' => 'Clientes, sectores y empresas que confían en la marca.',
            'recommended_description' => 'Mostrá respaldo comercial mencionando rubros, experiencia y empresas atendidas.',
            'recommended_keywords' => 'clientes, empresas, sectores, referencias, industria',
        ],
        'partners' => [
            'label' => 'Representadas',
            'recommended_title' => 'Marcas representadas y alianzas comerciales.',
            'recommended_description' => 'Describí las marcas o representadas con las que trabaja la empresa y su valor para el cliente.',
            'recommended_keywords' => 'representadas, marcas, alianzas, proveedores, industria',
        ],
        'news' => [
            'label' => 'Novedades',
            'recommended_title' => 'Noticias, lanzamientos y actualidad de la empresa.',
            'recommended_description' => 'Indicá que acá se publican novedades, anuncios, ferias, lanzamientos y noticias relevantes.',
            'recommended_keywords' => 'novedades, noticias, lanzamientos, empresa, industria',
        ],
        'news_detail' => [
            'label' => 'Detalle de novedad',
            'recommended_title' => 'Nota o noticia puntual.',
            'recommended_description' => 'Se usa como respaldo para artículos cuando no tengan descripción propia cargada.',
            'recommended_keywords' => 'noticia, novedad, artículo, empresa, industria',
        ],
        'quote' => [
            'label' => 'Presupuesto',
            'recommended_title' => 'Solicitud de presupuesto y contacto comercial.',
            'recommended_description' => 'Invitá a consultar y pedir cotización de manera directa y clara.',
            'recommended_keywords' => 'presupuesto, cotización, consulta, ventas, productos',
        ],
        'contact' => [
            'label' => 'Contacto',
            'recommended_title' => 'Datos de contacto, ubicación y formulario.',
            'recommended_description' => 'Explicá cómo comunicarse con la empresa, por qué medio y para qué tipo de consultas.',
            'recommended_keywords' => 'contacto, teléfono, email, ubicación, consultas',
        ],
        'search' => [
            'label' => 'Búsqueda',
            'recommended_title' => 'Buscador interno del sitio.',
            'recommended_description' => 'Página para buscar contenidos, productos o secciones dentro del sitio.',
            'recommended_keywords' => 'buscar, buscador, productos, contenidos, sitio',
        ],
    ];

    protected const ROUTE_PAGE_MAP = [
        'web.home' => 'home',
        'web.about' => 'about',
        'web.products.index' => 'products',
        'web.products.all' => 'products_all',
        'web.products.family' => 'products_family',
        'web.products.show' => 'products_detail',
        'web.products.technical-sheet' => 'products_sheet',
        'web.quality' => 'quality',
        'web.news.index' => 'news',
        'web.news.show' => 'news_detail',
        'web.catalog.show' => 'catalog',
        'web.catalog.store' => 'catalog',
        'web.partners.index' => 'partners',
        'web.clients.index' => 'clients',
        'web.quote.show' => 'quote',
        'web.quote.store' => 'quote',
        'web.contact.show' => 'contact',
        'web.contact.store' => 'contact',
        'web.search.index' => 'search',
        'web.search.suggest' => 'search',
    ];

    public static function pages(): array
    {
        return self::PAGES;
    }

    public static function resolvePageKey(?Request $request = null): ?string
    {
        $routeName = $request?->route()?->getName();

        if (! $routeName) {
            return null;
        }

        return self::ROUTE_PAGE_MAP[$routeName] ?? null;
    }
}
