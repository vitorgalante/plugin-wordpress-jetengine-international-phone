<?php
/**
 * Classe para registrar o tipo de campo de telefone internacional
 * 
 * @package JetEngine_Intl_Phone_Field
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JEIPF_Field_Type {

    const FIELD_TYPE = 'intl_phone';

    public function __construct() {
        // Registra o tipo de campo no editor
        add_filter( 'jet-engine/forms/booking/field-types', array( $this, 'register_field_type' ) );
        
        // Adiciona o caminho do template
        add_filter( 'jet-engine/forms/booking/field-template-path', array( $this, 'field_template_path' ), 10, 2 );
        
        // Hook alternativo para renderização
        add_action( 'jet-engine/forms/booking/field-template/intl_phone', array( $this, 'render_field_template' ), 10, 3 );
        
        // Hook principal para renderização
        add_filter( 'jet-engine/forms/booking/pre-render-field', array( $this, 'pre_render_field' ), 10, 2 );
    }

    public function register_field_type( $field_types ) {
        $field_types[ self::FIELD_TYPE ] = __( 'International Phone', 'jetengine-intl-phone-field' );
        return $field_types;
    }

    public function field_template_path( $path, $field_type ) {
        if ( self::FIELD_TYPE === $field_type ) {
            return JEIPF_PLUGIN_DIR . 'includes/templates/intl-phone-field.php';
        }
        return $path;
    }

    public function render_field_template( $args, $instance, $form ) {
        include JEIPF_PLUGIN_DIR . 'includes/templates/intl-phone-field.php';
    }

    public function pre_render_field( $result, $args ) {
        if ( ! isset( $args['type'] ) || self::FIELD_TYPE !== $args['type'] ) {
            return $result;
        }
        
        ob_start();
        include JEIPF_PLUGIN_DIR . 'includes/templates/intl-phone-field.php';
        return ob_get_clean();
    }
}
