/**
 * JetEngine International Phone Field - Admin JavaScript
 * 
 * @package JetEngine_Intl_Phone_Field
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Controla a visibilidade das configurações do campo intl_phone
     */
    const JEIPFAdmin = {
        
        /**
         * Inicializa
         */
        init() {
            this.bindEvents();
            this.toggleFieldSettings();
        },

        /**
         * Configura eventos
         */
        bindEvents() {
            // Quando o tipo de campo muda
            $(document).on('change', '.jet-form-editor__field-type select, select[name*="[type]"]', () => {
                this.toggleFieldSettings();
            });

            // Quando um novo campo é adicionado
            $(document).on('jet-engine/forms/field-added', () => {
                setTimeout(() => this.toggleFieldSettings(), 100);
            });

            // Inicialização após o carregamento do editor
            $(document).on('jet-engine/forms/editor-ready', () => {
                this.toggleFieldSettings();
            });
        },

        /**
         * Alterna visibilidade das configurações baseado no tipo selecionado
         */
        toggleFieldSettings() {
            // Encontra todos os campos do editor
            $('.jet-form-editor__field').each(function() {
                const $field = $(this);
                const fieldType = $field.find('select[name*="[type]"]').val();
                
                // Mostra/esconde configurações específicas do intl_phone
                const $intlSettings = $field.find('[data-conditions*="intl_phone"]');
                
                if (fieldType === 'intl_phone') {
                    $intlSettings.show();
                } else {
                    $intlSettings.hide();
                }
            });
        }
    };

    // Inicializa quando o documento estiver pronto
    $(document).ready(() => {
        JEIPFAdmin.init();
    });

})(jQuery);
