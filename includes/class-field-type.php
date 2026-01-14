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
        // Registra o tipo de campo no admin
        add_filter( 'jet-engine/forms/booking/field-types', array( $this, 'register_field_type' ) );
        
        // Retorna o caminho do template (hook correto do JetEngine)
        add_filter( 'jet-engine/forms/booking/field-template/' . self::FIELD_TYPE, array( $this, 'get_field_template' ), 10, 3 );
    }

    public function register_field_type( $field_types ) {
        $field_types[ self::FIELD_TYPE ] = __( 'International Phone', 'jetengine-intl-phone-field' );
        return $field_types;
    }

    public function get_field_template( $template, $args, $builder ) {
        return JEIPF_PLUGIN_DIR . 'includes/templates/intl-phone-field.php';
    }
}
