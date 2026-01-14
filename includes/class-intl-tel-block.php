<?php
/**
 * Classe do bloco Gutenberg para telefone internacional
 *
 * @package JFB_Intl_Tel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Jet_Form_Builder\Blocks\Types\Base;

/**
 * Classe JFB_Intl_Tel_Block
 * 
 * Implementa o bloco Gutenberg do campo de telefone internacional
 */
class JFB_Intl_Tel_Block extends Base {

    /**
     * Retorna o nome do bloco
     */
    public function get_name() {
        return 'intl-tel-field';
    }

    /**
     * Retorna o título do bloco
     */
    public function get_title() {
        return __( 'Telefone Internacional', 'jet-form-intl-tel' );
    }

    /**
     * Retorna o ícone do bloco
     */
    public function get_icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
        </svg>';
    }

    /**
     * Retorna a categoria do bloco
     */
    public function get_category() {
        return 'jet-form-builder-fields';
    }

    /**
     * Retorna os atributos do bloco
     */
    public function get_attributes() {
        return array(
            'name' => array(
                'type'    => 'string',
                'default' => 'phone',
            ),
            'label' => array(
                'type'    => 'string',
                'default' => __( 'Telefone', 'jet-form-intl-tel' ),
            ),
            'desc' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'required' => array(
                'type'    => 'boolean',
                'default' => false,
            ),
            'default' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'placeholder' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'class' => array(
                'type'    => 'string',
                'default' => '',
            ),
            // Configurações específicas do intl-tel-input
            'initial_country' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'preferred_countries' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'only_countries' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'exclude_countries' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'save_format' => array(
                'type'    => 'string',
                'default' => 'E164',
            ),
            'separate_dial_code' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'allow_dropdown' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'show_validation' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
        );
    }

    /**
     * Renderiza o bloco no editor
     */
    public function render_callback( $attributes, $content = null ) {
        $this->set_attributes( $attributes );

        $settings = get_option( 'jfb_intl_tel_settings', array() );

        // Mescla configurações globais com atributos do bloco
        $initial_country = ! empty( $attributes['initial_country'] ) 
            ? $attributes['initial_country'] 
            : ( isset( $settings['initial_country'] ) ? $settings['initial_country'] : 'br' );

        $preferred_countries = ! empty( $attributes['preferred_countries'] )
            ? $attributes['preferred_countries']
            : ( isset( $settings['preferred_countries'] ) ? $settings['preferred_countries'] : 'br,us,pt' );

        $save_format = ! empty( $attributes['save_format'] )
            ? $attributes['save_format']
            : ( isset( $settings['save_format'] ) ? $settings['save_format'] : 'E164' );

        // Prepara argumentos para renderização
        $render_args = array(
            'id'                  => $this->get_field_id(),
            'name'                => $this->get_field_name(),
            'required'            => $this->get_required_val(),
            'default'             => $this->get_field_value( 'default' ),
            'placeholder'         => $this->get_field_value( 'placeholder' ),
            'class'               => $this->get_field_value( 'class' ),
            'initial_country'     => $initial_country,
            'preferred_countries' => $preferred_countries,
            'only_countries'      => $this->get_field_value( 'only_countries' ),
            'exclude_countries'   => $this->get_field_value( 'exclude_countries' ),
            'save_format'         => $save_format,
            'separate_dial_code'  => $this->get_field_value( 'separate_dial_code' ),
            'allow_dropdown'      => $this->get_field_value( 'allow_dropdown' ),
            'show_validation'     => $this->get_field_value( 'show_validation' ),
        );

        // Inicia buffer de saída
        ob_start();

        // Wrapper do campo
        $this->start_field();

        // Label
        $this->render_label();

        // Campo de telefone
        echo JFB_Intl_Tel_Field::instance()->render_field( '', $render_args );

        // Descrição
        $this->render_description();

        // Fecha wrapper
        $this->end_field();

        return ob_get_clean();
    }

    /**
     * Retorna ID único do campo
     */
    protected function get_field_id() {
        return 'intl-tel-' . $this->get_field_name() . '-' . uniqid();
    }

    /**
     * Retorna nome do campo
     */
    protected function get_field_name() {
        return $this->get_field_value( 'name' ) ?: 'phone';
    }

    /**
     * Retorna se é obrigatório
     */
    protected function get_required_val() {
        return (bool) $this->get_field_value( 'required' );
    }

    /**
     * Inicia wrapper do campo
     */
    protected function start_field() {
        $classes = array(
            'jet-form-builder__field-wrap',
            'jet-form-builder-intl-tel-field',
        );

        if ( $this->get_required_val() ) {
            $classes[] = 'jet-form-builder__required';
        }

        printf(
            '<div class="%s" data-field-name="%s">',
            esc_attr( implode( ' ', $classes ) ),
            esc_attr( $this->get_field_name() )
        );
    }

    /**
     * Finaliza wrapper do campo
     */
    protected function end_field() {
        echo '</div>';
    }

    /**
     * Renderiza label
     */
    protected function render_label() {
        $label = $this->get_field_value( 'label' );

        if ( empty( $label ) ) {
            return;
        }

        printf(
            '<label class="jet-form-builder__label" for="%s">%s%s</label>',
            esc_attr( $this->get_field_id() ),
            esc_html( $label ),
            $this->get_required_val() ? ' <span class="jet-form-builder__required-mark">*</span>' : ''
        );
    }

    /**
     * Renderiza descrição
     */
    protected function render_description() {
        $desc = $this->get_field_value( 'desc' );

        if ( empty( $desc ) ) {
            return;
        }

        printf(
            '<div class="jet-form-builder__desc">%s</div>',
            wp_kses_post( $desc )
        );
    }

    /**
     * Define atributos
     */
    protected function set_attributes( $attributes ) {
        $this->attributes = wp_parse_args( $attributes, $this->get_attributes() );
    }

    /**
     * Retorna valor de um atributo
     */
    protected function get_field_value( $key ) {
        $defaults = $this->get_attributes();
        $default  = isset( $defaults[ $key ]['default'] ) ? $defaults[ $key ]['default'] : '';

        return isset( $this->attributes[ $key ] ) ? $this->attributes[ $key ] : $default;
    }
}
