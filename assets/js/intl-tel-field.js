/**
 * JetFormBuilder International Phone Field - Frontend Script
 *
 * @package JFB_Intl_Tel
 */

(function($) {
    'use strict';

    // Configuração global
    const config = window.jfbIntlTelConfig || {
        initialCountry: 'br',
        preferredCountries: ['br', 'us', 'pt'],
        separateDialCode: true,
        nationalMode: false,
        autoPlaceholder: 'aggressive',
        formatOnDisplay: true,
        allowDropdown: true,
        saveFormat: 'E164',
        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js',
        i18n: {
            invalidNumber: 'Número de telefone inválido',
            invalidCountry: 'Código de país inválido',
            tooShort: 'Número muito curto',
            tooLong: 'Número muito longo',
            validNumber: 'Número válido'
        }
    };

    // Armazena instâncias
    const instances = new Map();

    // Erros de validação
    const errorMap = {
        0: config.i18n.invalidNumber,
        1: config.i18n.invalidCountry,
        2: config.i18n.tooShort,
        3: config.i18n.tooLong,
        4: config.i18n.invalidNumber
    };

    /**
     * Inicializa o campo de telefone
     */
    function initField(input) {
        if (instances.has(input)) {
            return instances.get(input);
        }

        // Obtém configurações do campo (data attributes sobrescrevem config global)
        const fieldConfig = getFieldConfig(input);

        // Inicializa intl-tel-input
        const iti = window.intlTelInput(input, {
            initialCountry: fieldConfig.initialCountry,
            preferredCountries: fieldConfig.preferredCountries,
            separateDialCode: fieldConfig.separateDialCode,
            nationalMode: fieldConfig.nationalMode,
            autoPlaceholder: fieldConfig.autoPlaceholder,
            formatOnDisplay: fieldConfig.formatOnDisplay,
            allowDropdown: fieldConfig.allowDropdown,
            utilsScript: config.utilsScript,
            customContainer: 'intl-tel-input-container',
            dropdownContainer: document.body,
            showSelectedDialCode: fieldConfig.separateDialCode
        });

        // Armazena instância
        instances.set(input, {
            iti: iti,
            config: fieldConfig
        });

        // Configura eventos
        setupEvents(input, iti, fieldConfig);

        return { iti, config: fieldConfig };
    }

    /**
     * Obtém configuração do campo
     */
    function getFieldConfig(input) {
        const dataset = input.dataset;
        
        return {
            initialCountry: dataset.initialCountry || config.initialCountry,
            preferredCountries: dataset.preferredCountries 
                ? dataset.preferredCountries.split(',').map(c => c.trim())
                : config.preferredCountries,
            onlyCountries: dataset.onlyCountries 
                ? dataset.onlyCountries.split(',').map(c => c.trim())
                : null,
            excludeCountries: dataset.excludeCountries
                ? dataset.excludeCountries.split(',').map(c => c.trim())
                : null,
            separateDialCode: dataset.separateDialCode !== undefined 
                ? dataset.separateDialCode === 'true'
                : config.separateDialCode,
            nationalMode: dataset.nationalMode !== undefined
                ? dataset.nationalMode === 'true'
                : config.nationalMode,
            autoPlaceholder: dataset.autoPlaceholder || config.autoPlaceholder,
            formatOnDisplay: dataset.formatOnDisplay !== undefined
                ? dataset.formatOnDisplay === 'true'
                : config.formatOnDisplay,
            allowDropdown: dataset.allowDropdown !== undefined
                ? dataset.allowDropdown === 'true'
                : config.allowDropdown,
            saveFormat: dataset.saveFormat || config.saveFormat,
            showValidation: dataset.showValidation !== undefined
                ? dataset.showValidation === 'true'
                : true
        };
    }

    /**
     * Configura eventos do campo
     */
    function setupEvents(input, iti, fieldConfig) {
        const wrapper = input.closest('.intl-tel-field-wrapper');
        const validationMsg = wrapper ? wrapper.querySelector('.intl-tel-validation-message') : null;

        // Validação em tempo real
        input.addEventListener('input', function() {
            validateField(input, iti, validationMsg, fieldConfig);
        });

        input.addEventListener('blur', function() {
            validateField(input, iti, validationMsg, fieldConfig);
            
            // Formata o número no blur
            if (iti.isValidNumber()) {
                formatNumber(input, iti, fieldConfig.saveFormat);
            }
        });

        // Atualiza quando país muda
        input.addEventListener('countrychange', function() {
            validateField(input, iti, validationMsg, fieldConfig);
        });

        // Intercepta submit do formulário
        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!validateAndFormat(input, iti, validationMsg, fieldConfig)) {
                    e.preventDefault();
                    e.stopPropagation();
                    input.focus();
                    return false;
                }
            });
        }
    }

    /**
     * Valida o campo
     */
    function validateField(input, iti, validationMsg, fieldConfig) {
        const value = input.value.trim();
        
        // Remove classes anteriores
        input.classList.remove('error', 'valid', 'is-invalid', 'is-valid');
        
        if (validationMsg) {
            validationMsg.textContent = '';
            validationMsg.classList.remove('error', 'success');
        }

        // Campo vazio - não valida (deixa required lidar)
        if (!value) {
            return true;
        }

        // Valida com intl-tel-input
        if (iti.isValidNumber()) {
            input.classList.add('valid', 'is-valid');
            
            if (validationMsg && fieldConfig.showValidation) {
                validationMsg.textContent = config.i18n.validNumber;
                validationMsg.classList.add('success');
            }
            
            return true;
        } else {
            const errorCode = iti.getValidationError();
            const errorMessage = errorMap[errorCode] || config.i18n.invalidNumber;
            
            input.classList.add('error', 'is-invalid');
            
            if (validationMsg && fieldConfig.showValidation) {
                validationMsg.textContent = errorMessage;
                validationMsg.classList.add('error');
            }
            
            return false;
        }
    }

    /**
     * Valida e formata antes do submit
     */
    function validateAndFormat(input, iti, validationMsg, fieldConfig) {
        const value = input.value.trim();
        
        // Campo vazio com required
        if (!value && input.hasAttribute('required')) {
            return false;
        }
        
        // Campo vazio sem required - ok
        if (!value) {
            return true;
        }

        // Valida
        if (!iti.isValidNumber()) {
            validateField(input, iti, validationMsg, fieldConfig);
            return false;
        }

        // Formata para salvamento
        formatNumber(input, iti, fieldConfig.saveFormat);
        
        return true;
    }

    /**
     * Formata o número para salvamento
     */
    function formatNumber(input, iti, format) {
        if (!iti.isValidNumber()) {
            return;
        }

        let formattedNumber;
        
        switch (format) {
            case 'E164':
                formattedNumber = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                break;
            case 'INTERNATIONAL':
                formattedNumber = iti.getNumber(intlTelInputUtils.numberFormat.INTERNATIONAL);
                break;
            case 'NATIONAL':
                formattedNumber = iti.getNumber(intlTelInputUtils.numberFormat.NATIONAL);
                break;
            case 'RFC3966':
                formattedNumber = iti.getNumber(intlTelInputUtils.numberFormat.RFC3966);
                break;
            default:
                formattedNumber = iti.getNumber(intlTelInputUtils.numberFormat.E164);
        }

        // Atualiza valor do input
        input.value = formattedNumber;
    }

    /**
     * Inicializa todos os campos
     */
    function initAllFields() {
        // Seleciona todos os campos de telefone
        const selectors = [
            'input.intl-tel-field',
            'input[data-intl-tel]',
            '.jet-form-builder__field[type="tel"]',
            'input[type="tel"].jet-form-builder__field'
        ];

        const fields = document.querySelectorAll(selectors.join(', '));
        
        fields.forEach(function(input) {
            initField(input);
        });
    }

    /**
     * Observer para campos dinâmicos
     */
    function setupMutationObserver() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType !== Node.ELEMENT_NODE) return;
                    
                    // Verifica se é um campo de telefone
                    if (node.matches && node.matches('input.intl-tel-field, input[data-intl-tel]')) {
                        initField(node);
                    }
                    
                    // Verifica campos dentro do elemento adicionado
                    const innerFields = node.querySelectorAll 
                        ? node.querySelectorAll('input.intl-tel-field, input[data-intl-tel]')
                        : [];
                        
                    innerFields.forEach(function(input) {
                        initField(input);
                    });
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * API pública
     */
    window.JFBIntlTel = {
        init: initField,
        initAll: initAllFields,
        getInstance: function(input) {
            return instances.get(input);
        },
        validate: function(input) {
            const instance = instances.get(input);
            if (instance) {
                return instance.iti.isValidNumber();
            }
            return false;
        },
        getNumber: function(input, format) {
            const instance = instances.get(input);
            if (instance && instance.iti.isValidNumber()) {
                format = format || instance.config.saveFormat;
                return instance.iti.getNumber(intlTelInputUtils.numberFormat[format] || intlTelInputUtils.numberFormat.E164);
            }
            return null;
        },
        setCountry: function(input, countryCode) {
            const instance = instances.get(input);
            if (instance) {
                instance.iti.setCountry(countryCode);
            }
        },
        setNumber: function(input, number) {
            const instance = instances.get(input);
            if (instance) {
                instance.iti.setNumber(number);
            }
        },
        destroy: function(input) {
            const instance = instances.get(input);
            if (instance) {
                instance.iti.destroy();
                instances.delete(input);
            }
        }
    };

    /**
     * Inicialização
     */
    function init() {
        // Aguarda utils.js carregar
        if (typeof intlTelInputUtils === 'undefined') {
            // Tenta novamente em 100ms
            setTimeout(init, 100);
            return;
        }

        initAllFields();
        setupMutationObserver();
    }

    // DOM Ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // Pequeno delay para garantir que utils.js carregou
        setTimeout(init, 100);
    }

    // jQuery ready (fallback)
    $(function() {
        setTimeout(init, 200);
    });

})(jQuery);
