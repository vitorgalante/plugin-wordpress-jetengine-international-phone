<?php
/**
 * Classe do campo de telefone internacional
 *
 * @package JFB_Intl_Tel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe JFB_Intl_Tel_Field
 * 
 * Implementa a lógica do campo de telefone internacional
 */
class JFB_Intl_Tel_Field {

    /**
     * Nome do campo
     */
    const FIELD_NAME = 'intl-tel-field';

    /**
     * Instância única
     */
    private static $instance = null;

    /**
     * Retorna instância única
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
        $this->init_hooks();
    }

    /**
     * Inicializa hooks
     */
    private function init_hooks() {
        // Registra campo no JetFormBuilder
        add_filter( 'jet-form-builder/fields/types', array( $this, 'register_field_type' ) );
        
        // Renderização customizada
        add_filter( 'jet-form-builder/render/' . self::FIELD_NAME, array( $this, 'render_field' ), 10, 2 );
        
        // Processa valor antes de salvar
        add_filter( 'jet-form-builder/request/field-value', array( $this, 'process_field_value' ), 10, 3 );
    }

    /**
     * Registra tipo de campo
     */
    public function register_field_type( $types ) {
        $types[ self::FIELD_NAME ] = array(
            'label'    => __( 'Telefone Internacional', 'jet-form-intl-tel' ),
            'icon'     => 'dashicons-phone',
            'category' => 'common',
        );
        return $types;
    }

    /**
     * Renderiza o campo
     */
    public function render_field( $output, $args ) {
        $field_id   = isset( $args['id'] ) ? $args['id'] : 'intl-tel-' . uniqid();
        $field_name = isset( $args['name'] ) ? $args['name'] : 'phone';
        $required   = isset( $args['required'] ) && $args['required'];
        $default    = isset( $args['default'] ) ? $args['default'] : '';
        $class      = isset( $args['class'] ) ? $args['class'] : '';
        $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';

        // Classes do campo
        $field_classes = array(
            'jet-form-builder__field',
            'intl-tel-field',
            $class,
        );

        // Atributos de dados para configuração individual
        $data_attrs = array();
        
        if ( isset( $args['initial_country'] ) && ! empty( $args['initial_country'] ) ) {
            $data_attrs['data-initial-country'] = esc_attr( $args['initial_country'] );
        }
        
        if ( isset( $args['preferred_countries'] ) && ! empty( $args['preferred_countries'] ) ) {
            $data_attrs['data-preferred-countries'] = esc_attr( $args['preferred_countries'] );
        }
        
        if ( isset( $args['only_countries'] ) && ! empty( $args['only_countries'] ) ) {
            $data_attrs['data-only-countries'] = esc_attr( $args['only_countries'] );
        }
        
        if ( isset( $args['exclude_countries'] ) && ! empty( $args['exclude_countries'] ) ) {
            $data_attrs['data-exclude-countries'] = esc_attr( $args['exclude_countries'] );
        }

        if ( isset( $args['save_format'] ) && ! empty( $args['save_format'] ) ) {
            $data_attrs['data-save-format'] = esc_attr( $args['save_format'] );
        }

        // Monta string de data attributes
        $data_string = '';
        foreach ( $data_attrs as $key => $value ) {
            $data_string .= sprintf( ' %s="%s"', $key, $value );
        }

        ob_start();
        ?>
        <div class="intl-tel-field-wrapper">
            <input
                type="tel"
                id="<?php echo esc_attr( $field_id ); ?>"
                name="<?php echo esc_attr( $field_name ); ?>"
                class="<?php echo esc_attr( implode( ' ', array_filter( $field_classes ) ) ); ?>"
                value="<?php echo esc_attr( $default ); ?>"
                <?php echo $required ? 'required' : ''; ?>
                <?php echo $placeholder ? 'placeholder="' . esc_attr( $placeholder ) . '"' : ''; ?>
                <?php echo $data_string; ?>
                autocomplete="tel"
            />
            <div class="intl-tel-validation-message"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Processa valor do campo antes de salvar
     */
    public function process_field_value( $value, $field_name, $request ) {
        // Verifica se é um campo de telefone internacional
        if ( strpos( $field_name, 'phone' ) === false && strpos( $field_name, 'tel' ) === false ) {
            return $value;
        }

        // Limpa o valor
        $value = sanitize_text_field( $value );

        // Se já está no formato E.164, retorna
        if ( preg_match( '/^\+[1-9]\d{6,14}$/', $value ) ) {
            return $value;
        }

        // Remove caracteres não numéricos exceto o +
        $cleaned = preg_replace( '/[^0-9+]/', '', $value );

        return $cleaned;
    }

    /**
     * Valida número de telefone
     */
    public static function validate_phone_number( $number ) {
        // Remove espaços e caracteres especiais
        $cleaned = preg_replace( '/[^0-9+]/', '', $number );

        // Verifica formato E.164
        if ( preg_match( '/^\+[1-9]\d{6,14}$/', $cleaned ) ) {
            return array(
                'valid'   => true,
                'number'  => $cleaned,
                'message' => __( 'Número válido', 'jet-form-intl-tel' ),
            );
        }

        // Tenta identificar o problema
        if ( strlen( $cleaned ) < 8 ) {
            return array(
                'valid'   => false,
                'number'  => $cleaned,
                'message' => __( 'Número muito curto', 'jet-form-intl-tel' ),
            );
        }

        if ( strlen( $cleaned ) > 15 ) {
            return array(
                'valid'   => false,
                'number'  => $cleaned,
                'message' => __( 'Número muito longo', 'jet-form-intl-tel' ),
            );
        }

        if ( strpos( $cleaned, '+' ) !== 0 ) {
            return array(
                'valid'   => false,
                'number'  => $cleaned,
                'message' => __( 'Número deve começar com código do país (+)', 'jet-form-intl-tel' ),
            );
        }

        return array(
            'valid'   => false,
            'number'  => $cleaned,
            'message' => __( 'Formato de número inválido', 'jet-form-intl-tel' ),
        );
    }

    /**
     * Formata número para exibição
     */
    public static function format_for_display( $number, $format = 'INTERNATIONAL' ) {
        // Remove tudo exceto números e +
        $cleaned = preg_replace( '/[^0-9+]/', '', $number );

        switch ( $format ) {
            case 'E164':
                return $cleaned;

            case 'NATIONAL':
                // Remove código do país para formato nacional
                // Isso é simplificado - para formato correto, use a lib no frontend
                return preg_replace( '/^\+\d{1,3}/', '', $cleaned );

            case 'RFC3966':
                return 'tel:' . str_replace( '+', '+', $cleaned );

            case 'INTERNATIONAL':
            default:
                // Adiciona espaços básicos (simplificado)
                if ( preg_match( '/^\+(\d{1,3})(\d+)$/', $cleaned, $matches ) ) {
                    return '+' . $matches[1] . ' ' . $matches[2];
                }
                return $cleaned;
        }
    }
}

// Inicializa
JFB_Intl_Tel_Field::instance();
