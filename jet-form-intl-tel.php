<?php
/**
 * Plugin Name: JetFormBuilder International Phone Field
 * Plugin URI: https://github.com/vitoor/jet-form-intl-tel
 * Description: Adiciona um campo de telefone internacional com validação ao JetFormBuilder usando a biblioteca intl-tel-input.
 * Version: 1.0.1
 * Author: Vitoor
 * Author URI: https://github.com/vitoor
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: jet-form-intl-tel
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constantes do plugin
define( 'JFB_INTL_TEL_VERSION', '1.0.1' );
define( 'JFB_INTL_TEL_PATH', plugin_dir_path( __FILE__ ) );
define( 'JFB_INTL_TEL_URL', plugin_dir_url( __FILE__ ) );
define( 'JFB_INTL_TEL_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Classe principal do plugin
 */
final class JFB_Intl_Tel {

    /**
     * Instância única
     */
    private static $instance = null;

    /**
     * Versão da biblioteca intl-tel-input
     */
    const INTL_TEL_INPUT_VERSION = '23.0.12';

    /**
     * Retorna a instância única
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor
     */
    private function __construct() {
        $this->check_dependencies();
    }

    /**
     * Verifica dependências e inicializa
     */
    private function check_dependencies() {
        add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
    }

    /**
     * Inicializa o plugin
     */
    public function init() {
        // Verifica se JetFormBuilder está ativo (standalone ou como módulo do JetEngine)
        if ( ! $this->is_jetformbuilder_active() ) {
            add_action( 'admin_notices', array( $this, 'missing_jetformbuilder_notice' ) );
            return;
        }

        // Carrega arquivos necessários
        $this->load_includes();

        // Hooks
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );

        // Registra o bloco Gutenberg
        add_action( 'jet-form-builder/blocks/register', array( $this, 'register_block' ) );

        // Filtro para validação no servidor
        add_filter( 'jet-form-builder/validate-field', array( $this, 'validate_phone_field' ), 10, 3 );

        // Adiciona configurações
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Carrega arquivos de inclusão
     */
    private function load_includes() {
        require_once JFB_INTL_TEL_PATH . 'includes/class-intl-tel-field.php';
        require_once JFB_INTL_TEL_PATH . 'includes/class-intl-tel-block.php';
    }

    /**
     * Aviso de dependência ausente
     */
    public function missing_jetformbuilder_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e( 'JetFormBuilder International Phone Field', 'jet-form-intl-tel' ); ?></strong>
                <?php esc_html_e( 'requer o JetFormBuilder para funcionar. Por favor, instale e ative o JetFormBuilder (standalone ou como módulo do JetEngine).', 'jet-form-intl-tel' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Verifica se JetFormBuilder está disponível
     * Suporta tanto o plugin standalone quanto o módulo do JetEngine
     */
    private function is_jetformbuilder_active() {
        // Verifica plugin standalone
        if ( class_exists( 'Jet_Form_Builder\Plugin' ) ) {
            return true;
        }

        // Verifica módulo do JetEngine
        if ( class_exists( 'Jet_Engine' ) && function_exists( 'jet_form_builder' ) ) {
            return true;
        }

        // Verifica se o post type do JetFormBuilder existe
        if ( post_type_exists( 'jet-form-builder' ) ) {
            return true;
        }

        // Verifica pela classe do módulo do JetEngine
        if ( class_exists( 'Jet_Engine_Module_Forms' ) ) {
            return true;
        }

        // Verifica se existe a função global jet_fb_render_field
        if ( function_exists( 'jet_fb_render_field' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Enfileira assets do frontend
     */
    public function enqueue_frontend_assets() {
        // Biblioteca intl-tel-input CSS
        wp_enqueue_style(
            'intl-tel-input',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@' . self::INTL_TEL_INPUT_VERSION . '/build/css/intlTelInput.css',
            array(),
            self::INTL_TEL_INPUT_VERSION
        );

        // CSS personalizado
        wp_enqueue_style(
            'jfb-intl-tel-field',
            JFB_INTL_TEL_URL . 'assets/css/intl-tel-field.css',
            array( 'intl-tel-input' ),
            JFB_INTL_TEL_VERSION
        );

        // Biblioteca intl-tel-input JS
        wp_enqueue_script(
            'intl-tel-input',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@' . self::INTL_TEL_INPUT_VERSION . '/build/js/intlTelInput.min.js',
            array(),
            self::INTL_TEL_INPUT_VERSION,
            true
        );

        // Script de inicialização
        wp_enqueue_script(
            'jfb-intl-tel-field',
            JFB_INTL_TEL_URL . 'assets/js/intl-tel-field.js',
            array( 'intl-tel-input', 'jquery' ),
            JFB_INTL_TEL_VERSION,
            true
        );

        // Passa configurações para o JS
        $settings = get_option( 'jfb_intl_tel_settings', array() );
        wp_localize_script( 'jfb-intl-tel-field', 'jfbIntlTelConfig', array(
            'initialCountry'     => isset( $settings['initial_country'] ) ? $settings['initial_country'] : 'br',
            'preferredCountries' => isset( $settings['preferred_countries'] ) ? explode( ',', $settings['preferred_countries'] ) : array( 'br', 'us', 'pt' ),
            'separateDialCode'   => isset( $settings['separate_dial_code'] ) ? (bool) $settings['separate_dial_code'] : true,
            'nationalMode'       => isset( $settings['national_mode'] ) ? (bool) $settings['national_mode'] : false,
            'autoPlaceholder'    => isset( $settings['auto_placeholder'] ) ? $settings['auto_placeholder'] : 'aggressive',
            'formatOnDisplay'    => isset( $settings['format_on_display'] ) ? (bool) $settings['format_on_display'] : true,
            'allowDropdown'      => isset( $settings['allow_dropdown'] ) ? (bool) $settings['allow_dropdown'] : true,
            'saveFormat'         => isset( $settings['save_format'] ) ? $settings['save_format'] : 'E164',
            'utilsScript'        => 'https://cdn.jsdelivr.net/npm/intl-tel-input@' . self::INTL_TEL_INPUT_VERSION . '/build/js/utils.js',
            'i18n'               => array(
                'invalidNumber'    => __( 'Número de telefone inválido', 'jet-form-intl-tel' ),
                'invalidCountry'   => __( 'Código de país inválido', 'jet-form-intl-tel' ),
                'tooShort'         => __( 'Número muito curto', 'jet-form-intl-tel' ),
                'tooLong'          => __( 'Número muito longo', 'jet-form-intl-tel' ),
                'validNumber'      => __( 'Número válido', 'jet-form-intl-tel' ),
            ),
        ) );
    }

    /**
     * Enfileira assets do admin
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_jfb-intl-tel-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'jfb-intl-tel-admin',
            JFB_INTL_TEL_URL . 'assets/css/admin.css',
            array(),
            JFB_INTL_TEL_VERSION
        );
    }

    /**
     * Enfileira assets do editor Gutenberg
     */
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'jfb-intl-tel-block',
            JFB_INTL_TEL_URL . 'assets/js/editor/intl-tel-block.js',
            array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
            JFB_INTL_TEL_VERSION,
            true
        );

        wp_enqueue_style(
            'jfb-intl-tel-editor',
            JFB_INTL_TEL_URL . 'assets/css/admin.css',
            array(),
            JFB_INTL_TEL_VERSION
        );
    }

    /**
     * Registra o bloco customizado
     */
    public function register_block( $manager ) {
        $manager->register_block_type( new JFB_Intl_Tel_Block() );
    }

    /**
     * Valida o campo de telefone no servidor
     */
    public function validate_phone_field( $is_valid, $value, $field ) {
        if ( 'intl-tel-field' !== $field['blockName'] ) {
            return $is_valid;
        }

        if ( empty( $value ) ) {
            return $is_valid; // Campo vazio - deixa validação de required lidar
        }

        // Validação básica do formato E.164
        if ( ! preg_match( '/^\+[1-9]\d{6,14}$/', $value ) ) {
            return new WP_Error(
                'invalid_phone',
                __( 'Por favor, insira um número de telefone válido.', 'jet-form-intl-tel' )
            );
        }

        return $is_valid;
    }

    /**
     * Adiciona página de configurações
     */
    public function add_settings_page() {
        add_options_page(
            __( 'JetFormBuilder Telefone Internacional', 'jet-form-intl-tel' ),
            __( 'JFB Telefone Intl', 'jet-form-intl-tel' ),
            'manage_options',
            'jfb-intl-tel-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Registra configurações
     */
    public function register_settings() {
        register_setting( 'jfb_intl_tel_settings', 'jfb_intl_tel_settings', array(
            'sanitize_callback' => array( $this, 'sanitize_settings' ),
        ) );

        add_settings_section(
            'jfb_intl_tel_general',
            __( 'Configurações Gerais', 'jet-form-intl-tel' ),
            '__return_false',
            'jfb-intl-tel-settings'
        );

        // País inicial
        add_settings_field(
            'initial_country',
            __( 'País Inicial', 'jet-form-intl-tel' ),
            array( $this, 'render_text_field' ),
            'jfb-intl-tel-settings',
            'jfb_intl_tel_general',
            array(
                'id'          => 'initial_country',
                'default'     => 'br',
                'description' => __( 'Código do país inicial (ex: br, us, pt)', 'jet-form-intl-tel' ),
            )
        );

        // Países preferidos
        add_settings_field(
            'preferred_countries',
            __( 'Países Preferidos', 'jet-form-intl-tel' ),
            array( $this, 'render_text_field' ),
            'jfb-intl-tel-settings',
            'jfb_intl_tel_general',
            array(
                'id'          => 'preferred_countries',
                'default'     => 'br,us,pt',
                'description' => __( 'Lista de países no topo do dropdown (separados por vírgula)', 'jet-form-intl-tel' ),
            )
        );

        // Formato de salvamento
        add_settings_field(
            'save_format',
            __( 'Formato de Salvamento', 'jet-form-intl-tel' ),
            array( $this, 'render_select_field' ),
            'jfb-intl-tel-settings',
            'jfb_intl_tel_general',
            array(
                'id'      => 'save_format',
                'default' => 'E164',
                'options' => array(
                    'E164'          => 'E.164 (+5511999999999)',
                    'INTERNATIONAL' => 'Internacional (+55 11 99999-9999)',
                    'NATIONAL'      => 'Nacional ((11) 99999-9999)',
                    'RFC3966'       => 'RFC3966 (tel:+55-11-99999-9999)',
                ),
            )
        );

        // Separar código do país
        add_settings_field(
            'separate_dial_code',
            __( 'Separar Código do País', 'jet-form-intl-tel' ),
            array( $this, 'render_checkbox_field' ),
            'jfb-intl-tel-settings',
            'jfb_intl_tel_general',
            array(
                'id'          => 'separate_dial_code',
                'default'     => true,
                'description' => __( 'Exibe o código do país (+55) separado do input', 'jet-form-intl-tel' ),
            )
        );

        // Permitir dropdown
        add_settings_field(
            'allow_dropdown',
            __( 'Permitir Seleção de País', 'jet-form-intl-tel' ),
            array( $this, 'render_checkbox_field' ),
            'jfb-intl-tel-settings',
            'jfb_intl_tel_general',
            array(
                'id'          => 'allow_dropdown',
                'default'     => true,
                'description' => __( 'Permite que o usuário selecione o país', 'jet-form-intl-tel' ),
            )
        );
    }

    /**
     * Sanitiza configurações
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        $sanitized['initial_country'] = isset( $input['initial_country'] )
            ? sanitize_text_field( strtolower( $input['initial_country'] ) )
            : 'br';

        $sanitized['preferred_countries'] = isset( $input['preferred_countries'] )
            ? sanitize_text_field( strtolower( $input['preferred_countries'] ) )
            : 'br,us,pt';

        $sanitized['save_format'] = isset( $input['save_format'] )
            ? sanitize_text_field( $input['save_format'] )
            : 'E164';

        $sanitized['separate_dial_code'] = isset( $input['separate_dial_code'] ) ? 1 : 0;
        $sanitized['allow_dropdown'] = isset( $input['allow_dropdown'] ) ? 1 : 0;
        $sanitized['national_mode'] = isset( $input['national_mode'] ) ? 1 : 0;
        $sanitized['format_on_display'] = isset( $input['format_on_display'] ) ? 1 : 0;
        $sanitized['auto_placeholder'] = isset( $input['auto_placeholder'] )
            ? sanitize_text_field( $input['auto_placeholder'] )
            : 'aggressive';

        return $sanitized;
    }

    /**
     * Renderiza campo de texto
     */
    public function render_text_field( $args ) {
        $settings = get_option( 'jfb_intl_tel_settings', array() );
        $value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : $args['default'];
        ?>
        <input
            type="text"
            id="<?php echo esc_attr( $args['id'] ); ?>"
            name="jfb_intl_tel_settings[<?php echo esc_attr( $args['id'] ); ?>]"
            value="<?php echo esc_attr( $value ); ?>"
            class="regular-text"
        />
        <?php if ( isset( $args['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Renderiza campo select
     */
    public function render_select_field( $args ) {
        $settings = get_option( 'jfb_intl_tel_settings', array() );
        $value = isset( $settings[ $args['id'] ] ) ? $settings[ $args['id'] ] : $args['default'];
        ?>
        <select
            id="<?php echo esc_attr( $args['id'] ); ?>"
            name="jfb_intl_tel_settings[<?php echo esc_attr( $args['id'] ); ?>]"
        >
            <?php foreach ( $args['options'] as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Renderiza campo checkbox
     */
    public function render_checkbox_field( $args ) {
        $settings = get_option( 'jfb_intl_tel_settings', array() );
        $value = isset( $settings[ $args['id'] ] ) ? (bool) $settings[ $args['id'] ] : $args['default'];
        ?>
        <label>
            <input
                type="checkbox"
                id="<?php echo esc_attr( $args['id'] ); ?>"
                name="jfb_intl_tel_settings[<?php echo esc_attr( $args['id'] ); ?>]"
                value="1"
                <?php checked( $value ); ?>
            />
            <?php if ( isset( $args['description'] ) ) : ?>
                <?php echo esc_html( $args['description'] ); ?>
            <?php endif; ?>
        </label>
        <?php
    }

    /**
     * Renderiza página de configurações
     */
    public function render_settings_page() {
        ?>
        <div class="wrap jfb-intl-tel-settings">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'jfb_intl_tel_settings' );
                do_settings_sections( 'jfb-intl-tel-settings' );
                submit_button( __( 'Salvar Configurações', 'jet-form-intl-tel' ) );
                ?>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Como Usar', 'jet-form-intl-tel' ); ?></h2>
            <ol>
                <li><?php esc_html_e( 'No editor do JetFormBuilder, adicione o bloco "Telefone Internacional"', 'jet-form-intl-tel' ); ?></li>
                <li><?php esc_html_e( 'Configure as opções do campo no painel lateral', 'jet-form-intl-tel' ); ?></li>
                <li><?php esc_html_e( 'O número será salvo no formato configurado acima', 'jet-form-intl-tel' ); ?></li>
            </ol>

            <h2><?php esc_html_e( 'Códigos de País Comuns', 'jet-form-intl-tel' ); ?></h2>
            <p>
                <code>br</code> - Brasil |
                <code>us</code> - Estados Unidos |
                <code>pt</code> - Portugal |
                <code>es</code> - Espanha |
                <code>ar</code> - Argentina |
                <code>mx</code> - México |
                <code>co</code> - Colômbia
            </p>
        </div>
        <?php
    }
}

/**
 * Inicializa o plugin
 */
function jfb_intl_tel() {
    return JFB_Intl_Tel::instance();
}

// Inicia o plugin
jfb_intl_tel();
