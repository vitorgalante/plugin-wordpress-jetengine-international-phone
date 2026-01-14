/**
 * JetEngine International Phone Field - Frontend JavaScript
 * 
 * @package JetEngine_Intl_Phone_Field
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Classe principal do campo de telefone internacional
     */
    class JEIPFPhoneField {
        /**
         * Construtor
         * 
         * @param {HTMLElement} wrapper - Elemento wrapper do campo
         */
        constructor(wrapper) {
            this.wrapper = wrapper;
            this.$wrapper = $(wrapper);
            this.config = this.parseConfig();
            this.input = wrapper.querySelector('.jeipf-phone-input');
            this.hiddenInput = wrapper.querySelector('.jeipf-hidden-value');
            this.validationMsg = wrapper.querySelector('.jeipf-validation-message');
            this.iti = null;
            
            this.init();
        }

        /**
         * Obtém configuração do atributo data
         * 
         * @returns {Object}
         */
        parseConfig() {
            try {
                return JSON.parse(this.$wrapper.attr('data-jeipf-config') || '{}');
            } catch (e) {
                console.error('JEIPF: Erro ao parsear configuração', e);
                return {};
            }
        }

        /**
         * Inicializa o campo
         */
        init() {
            if (!this.input || typeof intlTelInput === 'undefined') {
                console.error('JEIPF: Input não encontrado ou intl-tel-input não carregado');
                return;
            }

            // Configurações do intl-tel-input
            const options = {
                initialCountry: this.config.initialCountry || 'br',
                preferredCountries: this.config.preferredCountries || ['br', 'us', 'pt'],
                allowDropdown: this.config.allowDropdown !== false,
                separateDialCode: this.config.separateDialCode || false,
                formatOnDisplay: this.config.formatOnDisplay !== false,
                nationalMode: true,
                autoPlaceholder: 'aggressive',
                countrySearch: true,
                containerClass: 'jeipf-iti-container',
                dropdownContainer: document.body, // Evita problemas de z-index em modals
                loadUtils: () => this.loadUtils(),
            };

            // Adiciona onlyCountries se configurado
            if (this.config.onlyCountries && this.config.onlyCountries.length > 0) {
                options.onlyCountries = this.config.onlyCountries;
            }

            // Inicializa intl-tel-input
            this.iti = intlTelInput(this.input, options);

            // Configura eventos
            this.bindEvents();

            // Carrega valor inicial se existir
            this.loadInitialValue();
        }

        /**
         * Carrega o script de utilitários
         * 
         * @returns {Promise}
         */
        loadUtils() {
            if (typeof jeipfSettings !== 'undefined' && jeipfSettings.utilsScript) {
                return import(jeipfSettings.utilsScript);
            }
            return Promise.reject('Utils script URL not found');
        }

        /**
         * Configura eventos
         */
        bindEvents() {
            // Atualiza valor hidden quando o número muda
            this.input.addEventListener('input', () => this.updateHiddenValue());
            this.input.addEventListener('blur', () => this.updateHiddenValue());
            
            // Atualiza quando o país muda
            this.input.addEventListener('countrychange', () => {
                this.updateHiddenValue();
                this.validate();
            });

            // Validação ao sair do campo
            this.input.addEventListener('blur', () => {
                if (this.config.validatePhone !== false) {
                    this.validate();
                }
            });

            // Atualiza ao digitar
            this.input.addEventListener('keyup', () => {
                this.updateHiddenValue();
                
                // Validação em tempo real (debounced)
                if (this.config.validatePhone !== false) {
                    this.debounceValidate();
                }
            });

            // Integração com submit do formulário JetEngine
            this.$wrapper.closest('form').on('submit', () => {
                this.updateHiddenValue();
            });

            // Suporte para AJAX forms do JetEngine
            $(document).on('jet-engine/form/ajax/before-send', (e, data) => {
                this.updateHiddenValue();
            });
        }

        /**
         * Carrega valor inicial do campo hidden
         */
        loadInitialValue() {
            const initialValue = this.hiddenInput.value;
            
            if (initialValue) {
                // Se temos um valor inicial, seta no input
                this.iti.setNumber(initialValue);
            }
        }

        /**
         * Atualiza o valor do campo hidden com formato E.164
         */
        updateHiddenValue() {
            if (!this.iti) return;

            try {
                // Obtém o número no formato E.164 (ex: +5511999999999)
                const number = this.iti.getNumber();
                this.hiddenInput.value = number;
            } catch (e) {
                // Se der erro, mantém o valor atual
                console.warn('JEIPF: Erro ao obter número', e);
            }
        }

        /**
         * Validação com debounce
         */
        debounceValidate() {
            if (this.validateTimeout) {
                clearTimeout(this.validateTimeout);
            }
            
            this.validateTimeout = setTimeout(() => {
                this.validate();
            }, 500);
        }

        /**
         * Valida o número de telefone
         * 
         * @returns {boolean}
         */
        validate() {
            if (!this.iti) return true;

            const inputValue = this.input.value.trim();
            
            // Se vazio, remove validação visual
            if (!inputValue) {
                this.clearValidation();
                return true;
            }

            // Verifica se é válido
            const isValid = this.iti.isValidNumber();
            
            if (isValid) {
                this.showValid();
            } else {
                this.showInvalid();
            }

            return isValid;
        }

        /**
         * Mostra mensagem de número válido
         */
        showValid() {
            this.$wrapper
                .removeClass('jeipf-has-error')
                .addClass('jeipf-is-valid');
            
            if (this.validationMsg && typeof jeipfSettings !== 'undefined') {
                this.validationMsg.textContent = jeipfSettings.i18n.validNumber || '✓ Valid number';
                this.validationMsg.className = 'jeipf-validation-message jeipf-valid';
                this.validationMsg.style.display = 'block';
            }
        }

        /**
         * Mostra mensagem de número inválido
         */
        showInvalid() {
            this.$wrapper
                .addClass('jeipf-has-error')
                .removeClass('jeipf-is-valid');
            
            if (this.validationMsg && typeof jeipfSettings !== 'undefined') {
                const errorCode = this.iti.getValidationError();
                let errorMsg = jeipfSettings.i18n.invalidNumber || 'Invalid number';
                
                // Mensagens específicas por tipo de erro
                const errorMessages = {
                    1: jeipfSettings.i18n.invalidCountry || 'Invalid country code',
                    2: jeipfSettings.i18n.tooShort || 'Number too short',
                    3: jeipfSettings.i18n.tooLong || 'Number too long',
                    4: jeipfSettings.i18n.invalidNumber || 'Invalid number',
                    5: jeipfSettings.i18n.invalidLength || 'Invalid length',
                };
                
                if (errorMessages[errorCode]) {
                    errorMsg = errorMessages[errorCode];
                }
                
                this.validationMsg.textContent = errorMsg;
                this.validationMsg.className = 'jeipf-validation-message jeipf-invalid';
                this.validationMsg.style.display = 'block';
            }
        }

        /**
         * Limpa validação visual
         */
        clearValidation() {
            this.$wrapper
                .removeClass('jeipf-has-error')
                .removeClass('jeipf-is-valid');
            
            if (this.validationMsg) {
                this.validationMsg.style.display = 'none';
                this.validationMsg.textContent = '';
                this.validationMsg.className = 'jeipf-validation-message';
            }
        }

        /**
         * Destrói a instância
         */
        destroy() {
            if (this.iti) {
                this.iti.destroy();
            }
            
            if (this.validateTimeout) {
                clearTimeout(this.validateTimeout);
            }
        }
    }

    /**
     * Inicialização global
     */
    const JEIPF = {
        instances: [],

        /**
         * Inicializa todos os campos na página
         */
        init() {
            this.initFields();
            this.observeNewFields();
        },

        /**
         * Inicializa campos existentes
         */
        initFields() {
            const wrappers = document.querySelectorAll('.jeipf-field-wrapper:not(.jeipf-initialized)');
            
            wrappers.forEach(wrapper => {
                wrapper.classList.add('jeipf-initialized');
                const instance = new JEIPFPhoneField(wrapper);
                this.instances.push(instance);
            });
        },

        /**
         * Observa novos campos adicionados dinamicamente (AJAX, popups, etc.)
         */
        observeNewFields() {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            // Verifica se é um wrapper ou contém wrappers
                            if (node.classList && node.classList.contains('jeipf-field-wrapper')) {
                                if (!node.classList.contains('jeipf-initialized')) {
                                    node.classList.add('jeipf-initialized');
                                    this.instances.push(new JEIPFPhoneField(node));
                                }
                            } else {
                                const wrappers = node.querySelectorAll('.jeipf-field-wrapper:not(.jeipf-initialized)');
                                wrappers.forEach(wrapper => {
                                    wrapper.classList.add('jeipf-initialized');
                                    this.instances.push(new JEIPFPhoneField(wrapper));
                                });
                            }
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        },

        /**
         * Reinicializa um campo específico
         * 
         * @param {HTMLElement} wrapper
         */
        reinit(wrapper) {
            // Remove instância antiga
            const index = this.instances.findIndex(i => i.wrapper === wrapper);
            if (index > -1) {
                this.instances[index].destroy();
                this.instances.splice(index, 1);
            }
            
            // Cria nova instância
            wrapper.classList.remove('jeipf-initialized');
            wrapper.classList.add('jeipf-initialized');
            this.instances.push(new JEIPFPhoneField(wrapper));
        }
    };

    // Inicializa quando o DOM estiver pronto
    $(document).ready(() => {
        JEIPF.init();
    });

    // Também inicializa quando Elementor frontend carrega (popups, etc.)
    $(window).on('elementor/frontend/init', () => {
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction('frontend/element_ready/jet-engine-booking-form.default', () => {
                setTimeout(() => JEIPF.initFields(), 100);
            });
        }
    });

    // Expõe globalmente para uso externo
    window.JEIPF = JEIPF;

})(jQuery);
