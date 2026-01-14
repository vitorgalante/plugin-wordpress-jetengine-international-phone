<?php
/**
 * Classe para gerenciar assets do plugin
 * 
 * @package JetEngine_Intl_Phone_Field
 */

// Se acessado diretamente, aborta
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JEIPF_Assets
 * 
 * Gerencia o carregamento de CSS e JavaScript
 */
class JEIPF_Assets {

    /**
     * Versão da biblioteca intl-tel-input
     */
    const INTL_TEL_INPUT_VERSION = '25.3.1';

    /**
     * Construtor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa hooks
     */
    private function init_hooks() {
        // Frontend
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        
        // Admin (editor de formulários)
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Enfileira assets no frontend
     */
    public function enqueue_frontend_assets() {
        // Registra a biblioteca intl-tel-input via CDN
        wp_register_style(
            'intl-tel-input',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@' . self::INTL_TEL_INPUT_VERSION . '/build/css/intlTelInput.css',
            array(),
            self::INTL_TEL_INPUT_VERSION
        );

        wp_register_script(
            'intl-tel-input',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@' . self::INTL_TEL_INPUT_VERSION . '/build/js/intlTelInput.min.js',
            array(),
            self::INTL_TEL_INPUT_VERSION,
            true
        );

        // Registra nossos estilos customizados
        wp_register_style(
            'jeipf-frontend',
            JEIPF_PLUGIN_URL . 'assets/css/frontend.css',
            array( 'intl-tel-input' ),
            JEIPF_VERSION
        );

        // Registra nosso script de inicialização
        wp_register_script(
            'jeipf-frontend',
            JEIPF_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'intl-tel-input', 'jquery' ),
            JEIPF_VERSION,
            true
        );

        // Localiza variáveis para o JavaScript
        wp_localize_script(
            'jeipf-frontend',
            'jeipfSettings',
            array(
                'utilsScript' => 'https://cdn.jsdelivr.net/npm/intl-tel-input@' . self::INTL_TEL_INPUT_VERSION . '/build/js/utils.js',
                'i18n' => array(
                    'invalidNumber'    => __( 'Número inválido', 'jetengine-intl-phone-field' ),
                    'invalidCountry'   => __( 'Código de país inválido', 'jetengine-intl-phone-field' ),
                    'tooShort'         => __( 'Número muito curto', 'jetengine-intl-phone-field' ),
                    'tooLong'          => __( 'Número muito longo', 'jetengine-intl-phone-field' ),
                    'validNumber'      => __( 'Número válido', 'jetengine-intl-phone-field' ),
                ),
            )
        );

        // Enfileira os assets se houver formulário JetEngine na página
        // Os assets serão efetivamente carregados quando o shortcode for executado
        // Vamos sempre enfileirar pois não temos como detectar antes
        if ( $this->should_load_assets() ) {
            $this->load_assets();
        }
    }

    /**
     * Verifica se deve carregar os assets
     * 
     * @return bool
     */
    private function should_load_assets() {
        global $post;

        // Se é admin, não carrega no frontend
        if ( is_admin() ) {
            return false;
        }

        // Carrega sempre para garantir que funcione em qualquer contexto
        // (shortcodes, widgets, templates)
        return true;
    }

    /**
     * Carrega os assets
     */
    public function load_assets() {
        wp_enqueue_style( 'jeipf-frontend' );
        wp_enqueue_script( 'jeipf-frontend' );
    }

    /**
     * Enfileira assets no admin
     * 
     * @param string $hook Hook da página atual
     */
    public function enqueue_admin_assets( $hook ) {
        // Apenas nas páginas de edição de formulários do JetEngine
        if ( ! $this->is_jetengine_forms_page( $hook ) ) {
            return;
        }

        wp_enqueue_style(
            'jeipf-admin',
            JEIPF_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            JEIPF_VERSION
        );

        wp_enqueue_script(
            'jeipf-admin',
            JEIPF_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            JEIPF_VERSION,
            true
        );
    }

    /**
     * Verifica se está na página de formulários do JetEngine
     * 
     * @param string $hook Hook da página
     * @return bool
     */
    private function is_jetengine_forms_page( $hook ) {
        $screen = get_current_screen();
        
        if ( ! $screen ) {
            return false;
        }

        // Páginas de formulários do JetEngine
        $forms_pages = array(
            'jet-engine_page_jet-engine-forms',
            'jet-engine-booking',
        );

        return in_array( $screen->id, $forms_pages, true ) || 
               strpos( $screen->id, 'jet-engine-booking' ) !== false;
    }
}
