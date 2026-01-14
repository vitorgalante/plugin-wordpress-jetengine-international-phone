/**
 * JetFormBuilder International Phone Field - Gutenberg Block
 *
 * @package JFB_Intl_Tel
 */

(function(wp) {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { createElement: el, Fragment } = wp.element;
    const { 
        TextControl, 
        ToggleControl, 
        SelectControl, 
        PanelBody,
        PanelRow 
    } = wp.components;
    const { InspectorControls } = wp.blockEditor;
    const { __ } = wp.i18n;

    // Ícone do bloco
    const phoneIcon = el('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        viewBox: '0 0 24 24',
        width: 24,
        height: 24
    }, el('path', {
        d: 'M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z',
        fill: 'currentColor'
    }));

    // Bandeiras para preview
    const flags = {
        br: '🇧🇷',
        us: '🇺🇸',
        pt: '🇵🇹',
        es: '🇪🇸',
        ar: '🇦🇷',
        mx: '🇲🇽',
        gb: '🇬🇧',
        fr: '🇫🇷',
        de: '🇩🇪',
        it: '🇮🇹'
    };

    // Códigos de país
    const dialCodes = {
        br: '+55',
        us: '+1',
        pt: '+351',
        es: '+34',
        ar: '+54',
        mx: '+52',
        gb: '+44',
        fr: '+33',
        de: '+49',
        it: '+39'
    };

    // Registra o bloco
    registerBlockType('jet-forms/intl-tel-field', {
        title: __('Telefone Internacional', 'jet-form-intl-tel'),
        description: __('Campo de telefone com seleção de país e validação internacional.', 'jet-form-intl-tel'),
        icon: phoneIcon,
        category: 'jet-form-builder-fields',
        keywords: [
            __('telefone', 'jet-form-intl-tel'),
            __('phone', 'jet-form-intl-tel'),
            __('internacional', 'jet-form-intl-tel'),
            __('celular', 'jet-form-intl-tel')
        ],
        supports: {
            html: false,
            className: true
        },
        attributes: {
            name: {
                type: 'string',
                default: 'phone'
            },
            label: {
                type: 'string',
                default: __('Telefone', 'jet-form-intl-tel')
            },
            desc: {
                type: 'string',
                default: ''
            },
            required: {
                type: 'boolean',
                default: false
            },
            default: {
                type: 'string',
                default: ''
            },
            placeholder: {
                type: 'string',
                default: ''
            },
            class: {
                type: 'string',
                default: ''
            },
            initial_country: {
                type: 'string',
                default: 'br'
            },
            preferred_countries: {
                type: 'string',
                default: 'br,us,pt'
            },
            only_countries: {
                type: 'string',
                default: ''
            },
            exclude_countries: {
                type: 'string',
                default: ''
            },
            save_format: {
                type: 'string',
                default: 'E164'
            },
            separate_dial_code: {
                type: 'boolean',
                default: true
            },
            allow_dropdown: {
                type: 'boolean',
                default: true
            },
            show_validation: {
                type: 'boolean',
                default: true
            }
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const {
                name,
                label,
                desc,
                required,
                placeholder,
                initial_country,
                preferred_countries,
                only_countries,
                exclude_countries,
                save_format,
                separate_dial_code,
                allow_dropdown,
                show_validation
            } = attributes;

            // Obtém bandeira e código do país inicial
            const country = initial_country.toLowerCase();
            const flag = flags[country] || '🌍';
            const dialCode = dialCodes[country] || '+??';

            return el(Fragment, {},
                // Painel de configurações
                el(InspectorControls, {},
                    // Configurações básicas
                    el(PanelBody, {
                        title: __('Configurações Básicas', 'jet-form-intl-tel'),
                        initialOpen: true
                    },
                        el(TextControl, {
                            label: __('Nome do Campo', 'jet-form-intl-tel'),
                            value: name,
                            onChange: function(value) {
                                setAttributes({ name: value });
                            },
                            help: __('Identificador único do campo (sem espaços)', 'jet-form-intl-tel')
                        }),
                        el(TextControl, {
                            label: __('Label', 'jet-form-intl-tel'),
                            value: label,
                            onChange: function(value) {
                                setAttributes({ label: value });
                            }
                        }),
                        el(TextControl, {
                            label: __('Descrição', 'jet-form-intl-tel'),
                            value: desc,
                            onChange: function(value) {
                                setAttributes({ desc: value });
                            },
                            help: __('Texto de ajuda abaixo do campo', 'jet-form-intl-tel')
                        }),
                        el(TextControl, {
                            label: __('Placeholder', 'jet-form-intl-tel'),
                            value: placeholder,
                            onChange: function(value) {
                                setAttributes({ placeholder: value });
                            },
                            help: __('Deixe vazio para usar placeholder automático', 'jet-form-intl-tel')
                        }),
                        el(ToggleControl, {
                            label: __('Obrigatório', 'jet-form-intl-tel'),
                            checked: required,
                            onChange: function(value) {
                                setAttributes({ required: value });
                            }
                        })
                    ),

                    // Configurações de país
                    el(PanelBody, {
                        title: __('Configurações de País', 'jet-form-intl-tel'),
                        initialOpen: false
                    },
                        el(TextControl, {
                            label: __('País Inicial', 'jet-form-intl-tel'),
                            value: initial_country,
                            onChange: function(value) {
                                setAttributes({ initial_country: value.toLowerCase() });
                            },
                            help: __('Código ISO do país (ex: br, us, pt)', 'jet-form-intl-tel')
                        }),
                        el(TextControl, {
                            label: __('Países Preferidos', 'jet-form-intl-tel'),
                            value: preferred_countries,
                            onChange: function(value) {
                                setAttributes({ preferred_countries: value.toLowerCase() });
                            },
                            help: __('Lista de países no topo (separados por vírgula)', 'jet-form-intl-tel')
                        }),
                        el(TextControl, {
                            label: __('Apenas Países', 'jet-form-intl-tel'),
                            value: only_countries,
                            onChange: function(value) {
                                setAttributes({ only_countries: value.toLowerCase() });
                            },
                            help: __('Mostrar apenas estes países (deixe vazio para todos)', 'jet-form-intl-tel')
                        }),
                        el(TextControl, {
                            label: __('Excluir Países', 'jet-form-intl-tel'),
                            value: exclude_countries,
                            onChange: function(value) {
                                setAttributes({ exclude_countries: value.toLowerCase() });
                            },
                            help: __('Países a excluir da lista', 'jet-form-intl-tel')
                        })
                    ),

                    // Configurações de formato
                    el(PanelBody, {
                        title: __('Formato e Validação', 'jet-form-intl-tel'),
                        initialOpen: false
                    },
                        el(SelectControl, {
                            label: __('Formato de Salvamento', 'jet-form-intl-tel'),
                            value: save_format,
                            options: [
                                { value: 'E164', label: 'E.164 (+5511999999999)' },
                                { value: 'INTERNATIONAL', label: __('Internacional (+55 11 99999-9999)', 'jet-form-intl-tel') },
                                { value: 'NATIONAL', label: __('Nacional ((11) 99999-9999)', 'jet-form-intl-tel') },
                                { value: 'RFC3966', label: 'RFC3966 (tel:+55-11-99999-9999)' }
                            ],
                            onChange: function(value) {
                                setAttributes({ save_format: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Separar Código do País', 'jet-form-intl-tel'),
                            checked: separate_dial_code,
                            onChange: function(value) {
                                setAttributes({ separate_dial_code: value });
                            },
                            help: __('Exibe código (+55) separado do campo', 'jet-form-intl-tel')
                        }),
                        el(ToggleControl, {
                            label: __('Permitir Seleção de País', 'jet-form-intl-tel'),
                            checked: allow_dropdown,
                            onChange: function(value) {
                                setAttributes({ allow_dropdown: value });
                            }
                        }),
                        el(ToggleControl, {
                            label: __('Mostrar Validação', 'jet-form-intl-tel'),
                            checked: show_validation,
                            onChange: function(value) {
                                setAttributes({ show_validation: value });
                            },
                            help: __('Exibe mensagens de validação em tempo real', 'jet-form-intl-tel')
                        })
                    )
                ),

                // Preview do campo no editor
                el('div', { className: 'jfb-intl-tel-block-preview' },
                    // Label
                    label && el('label', { 
                        className: 'jet-form-builder__label' 
                    }, 
                        label,
                        required && el('span', { 
                            className: 'jet-form-builder__required-mark',
                            style: { color: '#dc3545', marginLeft: '4px' }
                        }, '*')
                    ),

                    // Campo preview
                    el('div', { 
                        className: 'jfb-intl-tel-preview',
                        style: {
                            display: 'flex',
                            alignItems: 'center',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            padding: '8px 12px',
                            marginTop: '8px',
                            backgroundColor: '#f9f9f9'
                        }
                    },
                        // Bandeira e código
                        el('span', { 
                            style: { 
                                fontSize: '20px',
                                marginRight: '8px'
                            }
                        }, flag),
                        
                        separate_dial_code && el('span', {
                            style: {
                                color: '#666',
                                fontWeight: '500',
                                marginRight: '8px',
                                paddingRight: '8px',
                                borderRight: '1px solid #ddd'
                            }
                        }, dialCode),

                        // Placeholder
                        el('span', {
                            style: {
                                color: '#999',
                                flex: 1
                            }
                        }, placeholder || __('Digite o número...', 'jet-form-intl-tel')),

                        // Dropdown indicator
                        allow_dropdown && el('span', {
                            style: {
                                color: '#666',
                                fontSize: '10px'
                            }
                        }, '▼')
                    ),

                    // Descrição
                    desc && el('div', {
                        className: 'jet-form-builder__desc',
                        style: {
                            fontSize: '12px',
                            color: '#666',
                            marginTop: '5px'
                        }
                    }, desc),

                    // Info do formato
                    el('div', {
                        style: {
                            fontSize: '11px',
                            color: '#888',
                            marginTop: '8px',
                            padding: '6px 10px',
                            backgroundColor: '#e8f4fc',
                            borderRadius: '3px'
                        }
                    }, 
                        el('strong', {}, __('Formato: ', 'jet-form-intl-tel')),
                        save_format
                    )
                )
            );
        },

        save: function() {
            // Renderização é feita pelo PHP
            return null;
        }
    });

})(window.wp);
