<?php
/**
 * Plugin Name: JetEngine International Phone Field
 * Plugin URI: https://github.com/vitoor/jetengine-intl-phone-field
 * Description: Adiciona um campo de telefone internacional ao JetEngine Forms (legacy) usando a biblioteca intl-tel-input.
 * Version: 1.0.2
 * Author: Vitoor
 * Author URI: https://vitoor.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: jetengine-intl-phone-field
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.4
 */

// Se acessado diretamente, aborta
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constantes do plugin
define( 'JEIPF_VERSION', '1.0.2' );
define( 'JEIPF_PLUGIN_FILE', __FILE__ );
define( 'JEIPF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'JEIPF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Classe principal do plugin
 */
final class JetEngine_Intl_Phone_Field {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
    }

    public function init() {
        // Verifica apenas se JetEngine está ativo
        if ( ! class_exists( 'Jet_Engine' ) ) {
            add_action( 'admin_notices', array( $this, 'jetengine_missing_notice' ) );
            return;
        }

        // Carrega arquivos necessários
        require_once JEIPF_PLUGIN_DIR . 'includes/class-field-type.php';
        require_once JEIPF_PLUGIN_DIR . 'includes/class-assets.php';

        // Inicializa
        new JEIPF_Field_Type();
        new JEIPF_Assets();
    }

    public function jetengine_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><strong>JetEngine International Phone Field</strong> requer o plugin JetEngine ativo.</p>
        </div>
        <?php
    }
}

function jetengine_intl_phone_field() {
    return JetEngine_Intl_Phone_Field::instance();
}

jetengine_intl_phone_field();
