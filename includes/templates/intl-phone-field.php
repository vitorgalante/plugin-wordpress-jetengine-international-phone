<?php
/**
 * Template do campo de telefone internacional
 * 
 * @package JetEngine_Intl_Phone_Field
 * 
 * Variáveis disponíveis:
 * @var array $args Argumentos do campo
 * @var object $this Instância do renderer
 */

// Se acessado diretamente, aborta
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Configurações do campo
$field_name    = isset( $args['name'] ) ? $args['name'] : '';
$field_id      = isset( $args['id'] ) ? $args['id'] : 'intl-phone-' . uniqid();
$default_value = isset( $args['default'] ) ? $args['default'] : '';
$placeholder   = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
$required      = ! empty( $args['required'] ) ? 'required' : '';
$class         = isset( $args['class'] ) ? $args['class'] : '';

// Configurações específicas do intl-tel-input
$initial_country     = isset( $args['intl_initial_country'] ) ? strtolower( trim( $args['intl_initial_country'] ) ) : 'br';
$preferred_countries = isset( $args['intl_preferred_countries'] ) ? $args['intl_preferred_countries'] : 'br,us,pt';
$only_countries      = isset( $args['intl_only_countries'] ) ? $args['intl_only_countries'] : '';
$allow_dropdown      = isset( $args['intl_allow_dropdown'] ) ? $args['intl_allow_dropdown'] : 'yes';
$separate_dial_code  = isset( $args['intl_separate_dial_code'] ) ? $args['intl_separate_dial_code'] : 'no';
$format_on_display   = isset( $args['intl_format_on_display'] ) ? $args['intl_format_on_display'] : 'yes';
$validate_phone      = isset( $args['intl_validate_phone'] ) ? $args['intl_validate_phone'] : 'yes';

// Processa listas de países
$preferred_list = array_map( 'trim', array_filter( explode( ',', $preferred_countries ) ) );
$only_list      = array_map( 'trim', array_filter( explode( ',', $only_countries ) ) );

// Gera ID único para múltiplas instâncias
$unique_id = 'jeipf-' . wp_generate_uuid4();

// Prepara dados para JavaScript
$field_config = array(
    'initialCountry'     => $initial_country,
    'preferredCountries' => $preferred_list,
    'onlyCountries'      => $only_list,
    'allowDropdown'      => $allow_dropdown === 'yes',
    'separateDialCode'   => $separate_dial_code === 'yes',
    'formatOnDisplay'    => $format_on_display === 'yes',
    'validatePhone'      => $validate_phone === 'yes',
    'fieldName'          => $field_name,
);
?>

<div class="jeipf-field-wrapper" 
     id="<?php echo esc_attr( $unique_id ); ?>-wrapper"
     data-jeipf-config='<?php echo esc_attr( wp_json_encode( $field_config ) ); ?>'>
    
    <!-- Campo de entrada visível -->
    <input type="tel" 
           id="<?php echo esc_attr( $unique_id ); ?>"
           class="jet-form__field text-field jeipf-phone-input <?php echo esc_attr( $class ); ?>"
           placeholder="<?php echo esc_attr( $placeholder ); ?>"
           data-field-name="<?php echo esc_attr( $field_name ); ?>"
           autocomplete="tel"
           <?php echo esc_attr( $required ); ?>>
    
    <!-- Campo hidden que será enviado com o valor E.164 -->
    <input type="hidden" 
           name="<?php echo esc_attr( $field_name ); ?>"
           id="<?php echo esc_attr( $unique_id ); ?>-hidden"
           class="jeipf-hidden-value"
           value="<?php echo esc_attr( $default_value ); ?>"
           data-field-name="<?php echo esc_attr( $field_name ); ?>">
    
    <!-- Container para mensagem de validação -->
    <div class="jeipf-validation-message" 
         id="<?php echo esc_attr( $unique_id ); ?>-validation"
         style="display: none;"></div>
</div>
