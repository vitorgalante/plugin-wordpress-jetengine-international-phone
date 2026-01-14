<?php
/**
 * Template do campo de telefone internacional
 * 
 * Variáveis disponíveis do contexto JetEngine:
 * $args - configurações do campo
 * $this - instância do Jet_Engine_Booking_Forms_Builder
 * 
 * @package JetEngine_Intl_Phone_Field
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$field_id = 'jeipf-' . uniqid();

// Dados do campo vindos do JetEngine
$field_name    = isset( $args['name'] ) ? $args['name'] : '';
$default_value = isset( $args['default'] ) ? $args['default'] : '';
$placeholder   = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
$required      = ! empty( $args['required'] ) ? 'required' : '';

// Configurações do intl-tel-input
$field_config = array(
    'initialCountry'     => 'br',
    'preferredCountries' => array( 'br', 'us', 'pt' ),
    'onlyCountries'      => array(),
    'allowDropdown'      => true,
    'separateDialCode'   => false,
    'formatOnDisplay'    => true,
    'validatePhone'      => true,
);
?>

<div class="jeipf-field-wrapper" 
     id="<?php echo esc_attr( $field_id ); ?>-wrapper"
     data-jeipf-config='<?php echo esc_attr( wp_json_encode( $field_config ) ); ?>'>
    
    <input type="tel" 
           id="<?php echo esc_attr( $field_id ); ?>"
           class="jet-form__field text-field jeipf-phone-input"
           placeholder="<?php echo esc_attr( $placeholder ); ?>"
           data-field-name="<?php echo esc_attr( $field_name ); ?>"
           autocomplete="tel"
           <?php echo esc_attr( $required ); ?>>
    
    <input type="hidden" 
           name="<?php echo esc_attr( $field_name ); ?>"
           id="<?php echo esc_attr( $field_id ); ?>-hidden"
           class="jeipf-hidden-value"
           value="<?php echo esc_attr( $default_value ); ?>">
    
    <div class="jeipf-validation-message" 
         id="<?php echo esc_attr( $field_id ); ?>-validation"
         style="display: none;"></div>
</div>
